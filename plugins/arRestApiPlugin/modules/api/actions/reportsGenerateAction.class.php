<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class ApiReportsGenerateAction extends QubitApiAction
{
  protected function get($request)
  {
    $this->query = new \Elastica\Query;
    $this->queryBool = new \Elastica\Query\Bool;
    $this->queryBool->addMust(new \Elastica\Query\MatchAll);

    // ES only gets 10 results if size is not set
    // Max int32 value may cause OutOfMemoryError
    $this->query->setSize(99999);

    $this->results = array();

    if (!isset($request->type))
    {
      throw new QubitApi404Exception('Type not set');
    }

    switch ($request->type)
    {
      case 'granular_ingest':
      case 'high_level_ingest':
      case 'fixity':
      case 'fixity_error':
      case 'general_download':
      case 'amount_downloaded':
      case 'component_level':
      case 'video_characteristics':
        $type = QubitFlatfileImport::camelize($request->type);

        $this->$type();

        break;

      default:
        throw new QubitApi404Exception('Type not available');

        break;
    }

    return $this->results;
  }

  protected function granularIngest()
  {
    // Date range
    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);

    $this->query->setSort(array('createdAt' => 'desc'));
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($this->query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $aip = array();

      $this->addItemToArray($aip, 'name', $doc['filename']);
      $this->addItemToArray($aip, 'uuid', $doc['uuid']);
      $this->addItemToArray($aip, 'ingestion_date', $doc['createdAt']);

      if (isset($doc['type']))
      {
        $this->addItemToArray($aip, 'classification', get_search_i18n($doc['type'], 'name'));
      }

      if (isset($doc['partOf']))
      {
        $this->addItemToArray($aip['part_of'], 'id', $doc['partOf']['id']);
        $this->addItemToArray($aip['part_of'], 'title', get_search_i18n($doc['partOf'], 'title'));
        $this->addItemToArray($aip['part_of'], 'department', $doc['partOf']['department']['name']);
      }

      $this->addItemToArray($aip, 'ingestion_user', $doc['ingestionUser']);
      $this->addItemToArray($aip, 'attached_to', $doc['attachedTo']);


      // Add results grouped by user and department
      if (isset($aip['ingestion_user']))
      {
        $this->results['by_user'][$aip['ingestion_user']]['results'][] = $aip;
      }

      if (isset($aip['part_of']['department']))
      {
        $this->results['by_department'][$aip['part_of']['department']]['results'][] = $aip;
      }
    }

    // Get counts by department
    foreach ($this->results['by_department'] as $department => $ingests)
    {
      $artworks = $users = array();

      foreach ($ingests['results'] as $ingest)
      {
        if (isset($ingest['part_of']['title']) && !in_array($ingest['part_of']['title'], $artworks))
        {
          $artworks[] = $ingest['part_of']['title'];
        }

        if (isset($ingest['ingestion_user']) && !in_array($ingest['ingestion_user'], $users))
        {
          $users[] = $ingest['ingestion_user'];
        }
      }

      $counts = array(
        'aips' => count($ingests['results']),
        'artworks' => count($artworks),
        'users' => count($users));

      $this->results['by_department'][$department]['totals'] = $counts;
    }

    // Get counts by user
    foreach ($this->results['by_user'] as $user => $ingests)
    {
      $artworks = $departments = array();

      foreach ($ingests['results'] as $ingest)
      {
        if (isset($ingest['part_of']['title']) && !in_array($ingest['part_of']['title'], $artworks))
        {
          $artworks[] = $ingest['part_of']['title'];
        }

        if (isset($ingest['part_of']['department']) && !in_array($ingest['part_of']['department'], $departments))
        {
          $departments[] = $ingest['part_of']['department'];
        }
      }

      $counts = array(
        'aips' => count($ingests['results']),
        'artworks' => count($artworks),
        'departments' => count($departments));

      $this->results['by_user'][$user]['totals'] = $counts;
    }
  }

  protected function highLevelIngest()
  {
    // New artworks
    $this->queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_artwork_record_id'))));
    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    $this->results['new_artworks'] = $resultSet->getTotalHits();

    // Store new artworks to calculate the amount of artworks with material added
    $newWorks = array();
    foreach ($resultSet as $hit)
    {
      $newWorks[] = $hit->getId();
    }

    // New tech records
    $this->query = new \Elastica\Query;
    $this->queryBool = new \Elastica\Query\Bool;
    $this->queryBool->addMust(new \Elastica\Query\MatchAll);

    $this->queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_supporting_technology_record_id'))));
    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    $this->results['new_tech_records'] = $resultSet->getTotalHits();

    // AIPs ingested
    $this->query = new \Elastica\Query;
    $this->queryBool = new \Elastica\Query\Bool;
    $this->queryBool->addMust(new \Elastica\Query\MatchAll);
    $this->query->setSize(99999);

    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($this->query);

    $this->results['aips_ingested'] = $resultSet->getTotalHits();

    $works = array();

    // Files ingested and total filesize ingested
    $this->results['files_ingested'] = 0;
    $this->results['total_filesize_ingested'] = 0;

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      if (isset($doc['digitalObjectCount']))
      {
        $this->results['files_ingested'] += $doc['digitalObjectCount'];
      }

      if (isset($doc['sizeOnDisk']))
      {
        $this->results['total_filesize_ingested'] += $doc['sizeOnDisk'];
      }

      // Check if it's part of an Artwork to calculate the amount of artworks with material added
      if (isset($doc['partOf']['levelOfDescriptionId']) && $doc['partOf']['levelOfDescriptionId'] == sfConfig::get('app_drmc_lod_artwork_record_id'))
      {
        $works[] = $doc['partOf']['id'];
      }
    }

    // Remove new artworks from all the artworks (can't use array_diff to keep duplicates)
    foreach ($newWorks as $work)
    {
      $pos = array_search($work, $works);
      unset($works[$pos]);
    }

    // Now remove duplicates
    $works = array_unique($works, SORT_STRING);

    // Artworks with new materials added
    $this->results['artworks_with_materials_added'] = count($works);

    // Aggregate
    $this->results['aggregate'] = $this->results['artworks_with_materials_added'] + $this->results['new_artworks'];
  }

  protected function fixity()
  {
    // Date range
    $this->filterEsRangeFacet('from', 'to', 'timeStarted', $this->queryBool);

    $this->query->setSort(array('timeStarted' => 'desc'));
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($this->query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      // Store session_uuid for currently running check (not included in the results)
      if (!isset($doc['timeCompleted']) && isset($doc['sessionUuid']))
      {
        $currentlyChecking = $doc['sessionUuid'];

        continue;
      }

      $fixity = array();

      if (isset($doc['success']))
      {
        $fixity['success'] = (bool)$doc['success'];
      }

      $this->addItemToArray($fixity, 'time_started', $doc['timeStarted']);
      $this->addItemToArray($fixity, 'time_completed', $doc['timeCompleted']);

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $this->addItemToArray($fixity, 'duration', $duration);
      }

      $this->addItemToArray($fixity['aip'], 'uuid', $doc['aip']['uuid']);
      $this->addItemToArray($fixity['aip'], 'name', $doc['aip']['name']);
      $this->addItemToArray($fixity['aip'], 'part_of', $doc['aip']['partOf']);
      $this->addItemToArray($fixity['aip'], 'attached_to', $doc['aip']['attachedTo']);


      // Add results grouped by session_uuid
      if (isset($doc['sessionUuid']))
      {
        $this->results[$doc['sessionUuid']]['results'][] = $fixity;
      }
    }

    // Remove current check
    if (isset($currentlyChecking) && isset($this->results[$currentlyChecking]))
    {
      unset($this->results[$currentlyChecking]);
    }

    // Remove individual checks
    $resultsCopy = $this->results;
    foreach ($resultsCopy as $sessionUuid => $results)
    {
      if (count($results['results']) == 1)
      {
        unset($this->results[$sessionUuid]);
      }
    }

    // Get totals by collection check
    foreach ($this->results as $collection => $fixities)
    {
      $artworks = array();
      $verifiedCount = 0;

      foreach ($fixities['results'] as $fixity)
      {
        if (isset($fixity['aip']['part_of']) && !in_array($fixity['aip']['part_of'], $artworks))
        {
          $artworks[] = $fixity['aip']['part_of'];
        }

        if (isset($fixity['success']) && $fixity['success'])
        {
          $verifiedCount++;
        }
      }

      $start = $fixities['results'][count($fixities['results']) - 1]['time_started'];
      $end = $fixities['results'][0]['time_completed'];
      $duration = 0;

      if (isset($start) && isset($end))
      {
        $duration = strtotime($end) - strtotime($start);
      }

      $totals = array(
        'aips' => count($fixities['results']),
        'artworks' => count($artworks),
        'time_started' => $start,
        'time_completed' => $end,
        'duration' => $duration,
        'verified' => $verifiedCount);

      $this->results[$collection]['totals'] = $totals;
    }
  }

  protected function fixityError()
  {
    // Date range
    $this->filterEsRangeFacet('from', 'to', 'timeStarted', $this->queryBool);

    // Only errors
    $this->queryBool->addMust(new \Elastica\Query\Term(array('success' => false)));

    $this->query->setSort(array('timeCompleted' => 'desc'));
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($this->query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $fixity = array();

      $this->addItemToArray($fixity, 'fail_time', $doc['timeCompleted']);
      $this->addItemToArray($fixity['aip'], 'uuid', $doc['aip']['uuid']);
      $this->addItemToArray($fixity['aip'], 'name', $doc['aip']['name']);
      $this->addItemToArray($fixity['aip'], 'part_of', $doc['aip']['partOf']);
      $this->addItemToArray($fixity['aip'], 'attached_to', $doc['aip']['attachedTo']);

      // Get last fixity recovery data
      // TODO: Add fixity recovery data to fixity_reports in ES
      $sql = <<<sql

SELECT
  rec.time_started,
  rec.time_completed,
  rec.success,
  user.username
FROM
  fixity_recovery rec
JOIN user
  ON rec.user_id = user.id
WHERE
  rec.fixity_report_id = ?
ORDER BY
  rec.time_completed DESC
LIMIT 1;

sql;

      $result = QubitPdo::fetchOne($sql, array($hit->getId()));

      if (false !== $result)
      {
        if (isset($result->success))
        {
          $fixity['recovery']['success'] = (bool)$result->success;
        }

        $this->addItemToArray($fixity['recovery'], 'user', $result->username);
        $this->addItemToArray($fixity['recovery'], 'time_started', $result->time_started);
        $this->addItemToArray($fixity['recovery'], 'time_completed', $result->time_completed);

        if (isset($result->time_started) && isset($result->time_completed))
        {
          $duration = strtotime($result->time_completed) - strtotime($result->time_started);
          $this->addItemToArray($fixity['recovery'], 'duration', $duration);
        }
      }

      $this->results['results'][] = $fixity;
    }

    // Get totals row
    $artworks = $users = array();
    $verifiedCount = 0;

    foreach ($this->results['results'] as $fixity)
    {
      if (isset($fixity['aip']['part_of']) && !in_array($fixity['aip']['part_of'], $artworks))
      {
        $artworks[] = $fixity['aip']['part_of'];
      }

      if (isset($fixity['recovery']['user']) && !in_array($fixity['recovery']['user'], $users))
      {
        $users[] = $fixity['recovery']['user'];
      }

      if (isset($fixity['recovery']['success']) && $fixity['recovery']['success'])
      {
        $verifiedCount++;
      }
    }

    $totals = array(
      'aips' => count($this->results['results']),
      'artworks' => count($artworks),
      'users' => count($users),
      'verified' => $verifiedCount);

    $this->results['totals'] = $totals;
  }

  protected function generalDownload()
  {
    $sql  = 'SELECT
                access.access_date,
                access.object_id,
                access.access_type,
                access.reason,
                user.username';
    $sql .= ' FROM '.QubitAccessLog::TABLE_NAME.' access';
    $sql .= ' JOIN '.QubitUser::TABLE_NAME.' user
                ON access.user_id = user.id';
    $sql .= ' WHERE access.access_type in (?, ?)';

    if (isset($this->request->from) && ctype_digit($this->request->from))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->from / 1000));

      $sql .= ' AND access.access_date >= "'.$date->format('Y-m-d H:i:s').'"';
    }

    if (isset($this->request->to) && ctype_digit($this->request->to))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->to / 1000));

      $sql .= ' AND access.access_date <= "'.$date->format('Y-m-d H:i:s').'"';
    }

    $sql .= ' ORDER BY access.access_date';

    $results = QubitPdo::fetchAll($sql, array(QubitTerm::ACCESS_LOG_AIP_FILE_DOWNLOAD_ENTRY, QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY));

    foreach ($results as $result)
    {
      $accessLog = array();

      $this->addItemToArray($accessLog, 'user', $result->username);
      $this->addItemToArray($accessLog, 'date', arElasticSearchPluginUtil::convertDate($result->access_date));
      $this->addItemToArray($accessLog, 'reason', $result->reason);

      // Get AIP/file data from ES
      if ($result->access_type == QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY)
      {
        $this->addItemToArray($accessLog, 'type', 'AIP');

        try
        {
          $esResult = QubitSearch::getInstance()->index->getType('QubitAip')->getDocument($result->object_id);
          $doc = $esResult->getData();

          $this->addItemToArray($accessLog, 'name', $doc['filename']);
          $this->addItemToArray($accessLog, 'uuid', $doc['uuid']);
          $this->addItemToArray($accessLog, 'part_of', get_search_i18n($doc['partOf'], 'title'));
          $this->addItemToArray($accessLog, 'department', $doc['partOf']['department']['name']);
          $this->addItemToArray($accessLog, 'attached_to', $doc['attachedTo']);
          $this->addItemToArray($accessLog, 'size', (int)$doc['sizeOnDisk']);
        }
        catch (\Elastica\Exception\NotFoundException $e)
        {
          // AIP not found
        }
      }
      else
      {
        $this->addItemToArray($accessLog, 'type', 'File');

        try
        {
          $esResult = QubitSearch::getInstance()->index->getType('QubitInformationObject')->getDocument($result->object_id);
          $doc = $esResult->getData();

          $this->addItemToArray($accessLog, 'name', $doc['metsData']['filename']);
          $this->addItemToArray($accessLog, 'uuid', $doc['aipUuid']);
          $this->addItemToArray($accessLog, 'part_of', $doc['aipPartOf']);
          $this->addItemToArray($accessLog, 'department', $doc['aipPartOfDepartmentName']);
          $this->addItemToArray($accessLog, 'attached_to', $doc['aipAttachedTo']);
          $this->addItemToArray($accessLog, 'size', (int)$doc['metsData']['size']);
        }
        catch (\Elastica\Exception\NotFoundException $e)
        {
          // File not found
        }
      }

      // Add results grouped by user and department
      if (isset($accessLog['user']))
      {
        $this->results['by_user'][$accessLog['user']]['results'][] = $accessLog;
      }

      if (isset($accessLog['department']))
      {
        $this->results['by_department'][$accessLog['department']]['results'][] = $accessLog;
      }
    }

    // Get counts by department
    foreach ($this->results['by_department'] as $department => $accessLogs)
    {
      $artworks = $users = array();
      $size = 0;

      foreach ($accessLogs['results'] as $accessLog)
      {
        if (isset($accessLog['part_of']) && !in_array($accessLog['part_of'], $artworks))
        {
          $artworks[] = $accessLog['part_of'];
        }

        if (isset($accessLog['user']) && !in_array($accessLog['user'], $users))
        {
          $users[] = $accessLog['user'];
        }

        if (isset($accessLog['size']))
        {
          $size += $accessLog['size'];
        }
      }

      $counts = array(
        'size' => $size,
        'artworks' => count($artworks),
        'users' => count($users));

      $this->results['by_department'][$department]['totals'] = $counts;
    }

    // Get counts by user
    foreach ($this->results['by_user'] as $user => $accessLogs)
    {
      $artworks = $departments = array();
      $size = 0;

      foreach ($accessLogs['results'] as $accessLog)
      {
        if (isset($accessLog['part_of']) && !in_array($accessLog['part_of'], $artworks))
        {
          $artworks[] = $accessLog['part_of'];
        }

        if (isset($accessLog['department']) && !in_array($accessLog['department'], $departments))
        {
          $departments[] = $accessLog['department'];
        }

        if (isset($accessLog['size']))
        {
          $size += $accessLog['size'];
        }
      }

      $counts = array(
        'size' => $size,
        'artworks' => count($artworks),
        'departments' => count($departments));

      $this->results['by_user'][$user]['totals'] = $counts;
    }
  }

  protected function amountDownloaded()
  {
    $sql  = 'SELECT
                access.object_id,
                access.access_type,
                user.username';
    $sql .= ' FROM '.QubitAccessLog::TABLE_NAME.' access';
    $sql .= ' JOIN '.QubitUser::TABLE_NAME.' user
                ON access.user_id = user.id';
    $sql .= ' WHERE access.access_type in (?, ?)';

    if (isset($this->request->from) && ctype_digit($this->request->from))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->from / 1000));

      $sql .= ' AND access.access_date >= "'.$date->format('Y-m-d H:i:s').'"';
    }

    if (isset($this->request->to) && ctype_digit($this->request->to))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->to / 1000));

      $sql .= ' AND access.access_date <= "'.$date->format('Y-m-d H:i:s').'"';
    }

    $sql .= ' ORDER BY access.access_date';

    $results = QubitPdo::fetchAll($sql, array(QubitTerm::ACCESS_LOG_AIP_FILE_DOWNLOAD_ENTRY, QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY));

    foreach ($results as $result)
    {
      $accessLog = array();

      $this->addItemToArray($accessLog, 'user', $result->username);

      // Get AIP/file data from ES
      if ($result->access_type == QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY)
      {
        $this->addItemToArray($accessLog, 'type', 'AIP');

        try
        {
          $esResult = QubitSearch::getInstance()->index->getType('QubitAip')->getDocument($result->object_id);
          $doc = $esResult->getData();

          $this->addItemToArray($accessLog, 'part_of', get_search_i18n($doc['partOf'], 'title'));
          $this->addItemToArray($accessLog, 'department', $doc['partOf']['department']['name']);
          $this->addItemToArray($accessLog, 'size', (int)$doc['sizeOnDisk']);
        }
        catch (\Elastica\Exception\NotFoundException $e)
        {
          // AIP not found
        }
      }
      else
      {
        $this->addItemToArray($accessLog, 'type', 'File');

        try
        {
          $esResult = QubitSearch::getInstance()->index->getType('QubitInformationObject')->getDocument($result->object_id);
          $doc = $esResult->getData();

          $this->addItemToArray($accessLog, 'part_of', $doc['aipPartOf']);
          $this->addItemToArray($accessLog, 'department', $doc['aipPartOfDepartmentName']);
          $this->addItemToArray($accessLog, 'size', (int)$doc['metsData']['size']);
        }
        catch (\Elastica\Exception\NotFoundException $e)
        {
          // File not found
        }
      }

      // Add results grouped by user and department
      if (isset($accessLog['user']))
      {
        $grouped['by_user'][$accessLog['user']][] = $accessLog;
      }

      if (isset($accessLog['department']))
      {
        $grouped['by_department'][$accessLog['department']][] = $accessLog;
      }
    }

    // Get counts grouped by department
    $totalArtworks = $totalUsers = $totalAips = $totalFiles = $totalSize = 0;
    foreach ($grouped['by_department'] as $department => $logs)
    {
      $artworks = $users = array();
      $countAips = $countFiles = $countSize = 0;

      foreach ($logs as $log)
      {
        if (isset($log['type']) && $log['type'] == 'AIP')
        {
          $countAips ++;
        }

        if (isset($log['type']) && $log['type'] == 'File')
        {
          $countFiles ++;
        }

        if (isset($log['part_of']) && !in_array($log['part_of'], $artworks))
        {
          $artworks[] = $log['part_of'];
        }

        if (isset($log['user']) && !in_array($log['user'], $users))
        {
          $users[] = $log['user'];
        }

        if (isset($log['size']))
        {
          $countSize += $log['size'];
        }
      }

      $counts = array(
        'aips' => $countAips,
        'files' => $countFiles,
        'artworks' => count($artworks),
        'size' => $countSize,
        'users' => count($users));

      $this->results['by_department'][$department] = $counts;

      // Totals by all departments
      $totalArtworks += count($artworks);
      $totalUsers += count($users);
      $totalAips += $countAips;
      $totalFiles += $countFiles;
      $totalSize += $countSize;
    }

    $totalCounts = array(
      'aips' => $totalAips,
      'files' => $totalFiles,
      'artworks' => $totalArtworks,
      'size' => $totalSize,
      'users' => $totalUsers);

    $this->results['department_totals'] = $totalCounts;

    // Get counts grouped by user
    $totalArtworks = $totalDepartments = $totalAips = $totalFiles = $totalSize = 0;
    foreach ($grouped['by_user'] as $user => $logs)
    {
      $artworks = $departments = array();
      $countAips = $countFiles = $countSize = 0;

      foreach ($logs as $log)
      {
        if (isset($log['type']) && $log['type'] == 'AIP')
        {
          $countAips ++;
        }

        if (isset($log['type']) && $log['type'] == 'File')
        {
          $countFiles ++;
        }

        if (isset($log['part_of']) && !in_array($log['part_of'], $artworks))
        {
          $artworks[] = $log['part_of'];
        }

        if (isset($log['department']) && !in_array($log['department'], $departments))
        {
          $departments[] = $log['department'];
        }

        if (isset($log['size']))
        {
          $countSize += $log['size'];
        }
      }

      $counts = array(
        'aips' => $countAips,
        'files' => $countFiles,
        'artworks' => count($artworks),
        'size' => $countSize,
        'departments' => count($departments));

      $this->results['by_user'][$user] = $counts;

      // Totals by all users
      $totalArtworks += count($artworks);
      $totalDepartments += count($departments);
      $totalAips += $countAips;
      $totalFiles += $countFiles;
      $totalSize += $countSize;
    }

    $totalCounts = array(
      'aips' => $totalAips,
      'files' => $totalFiles,
      'artworks' => $totalArtworks,
      'size' => $totalSize,
      'departments' => $totalDepartments);

    $this->results['user_totals'] = $totalCounts;
  }

  protected function componentLevel()
  {
    $componentLevels = array(
      sfConfig::get('app_drmc_lod_archival_master_id'),
      sfConfig::get('app_drmc_lod_artist_supplied_master_id'),
      sfConfig::get('app_drmc_lod_artist_verified_proof_id'),
      sfConfig::get('app_drmc_lod_exhibition_format_id'),
      sfConfig::get('app_drmc_lod_miscellaneous_id'),
      sfConfig::get('app_drmc_lod_component_id')
    );

    $this->queryBool->addMust(new \Elastica\Query\Terms('levelOfDescriptionId', $componentLevels));
    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $component = array();

      $this->addItemToArray($component, 'artwork', get_search_i18n($doc['tmsComponent']['artwork'], 'title'));
      $this->addItemToArray($component, 'artist', $doc['tmsComponent']['artwork']['artist']);
      $this->addItemToArray($component, 'department', $doc['tmsComponent']['artwork']['departmentName']);
      $this->addItemToArray($component, 'component', get_search_i18n($doc, 'title'));

      if (isset($doc['levelOfDescriptionId']))
      {
        $this->addItemToArray($component, 'status', $this->getTermName($doc['levelOfDescriptionId']));
      }

      // Defaults for each component
      $countAips = $countFiles = $countSize = 0;
      $fixitySuccess = $lastFixityDate = null;

      // Calculate counts and obtain fixity data
      if (isset($doc['aips']))
      {
        // If there are AIPS make true fixity success by default
        $fixitySuccess = true;

        foreach ($doc['aips'] as $aip)
        {
          $countAips++;
          $countFiles += $aip['digitalObjectCount'];
          $countSize += $aip['sizeOnDisk'];

          // Get last fixity check for the AIP
          // TODO? Add fixity data to components in ES and update component when fixity checks are added
          $fixityQuery = new \Elastica\Query;
          $fixityQueryBool = new \Elastica\Query\Bool;
          $fixityQueryBool->addMust(new \Elastica\Query\Term(array('aip.uuid' => $aip['uuid'])));

          $fixityQuery->setQuery($fixityQueryBool);
          $fixityQuery->setSort(array('timeCompleted' => 'desc'));
          $fixityQuery->setLimit(1);

          $fixityResultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($fixityQuery);
          $fixityResults = $fixityResultSet->getResults();

          if (count($fixityResults) == 1)
          {
            $fixityDoc = $fixityResults[0]->getData();

            // If the last check failed set global fixity status as false
            if (isset($fixityDoc['success']) && !(bool)$fixityDoc['success'])
            {
              $fixitySuccess = false;
            }

            // Update last check date
            if (isset($fixityDoc['timeCompleted']) && (!isset($lastFixityDate) || $fixityDoc['timeCompleted'] > $lastFixityDate))
            {
              $lastFixityDate = $fixityDoc['timeCompleted'];
            }
          }
        }
      }

      $component['aips_count'] = $countAips;
      $component['files_count'] = $countFiles;
      $component['size_count'] = $countSize;
      $component['fixity_success'] = $fixitySuccess;
      $component['last_fixity_date'] = $lastFixityDate;

      // Add results grouped by department and artwork
      if (isset($component['department']) && isset($component['artwork']))
      {
        $this->results[$component['department']]['results'][$component['artwork']]['results'][] = $component;
      }
    }

    // Get counts grouped for artworks and departments
    foreach ($this->results as $department => $works)
    {
      $totalArtworks = $totalAips = $totalFiles = $totalSize = $totalVerified = 0;
      $totalArtists = array();

      foreach ($works['results'] as $work => $components)
      {
        $status = array();
        $artist = '';
        $countAips = $countFiles = $countSize = $countVerified = 0;

        foreach ($components['results'] as $component)
        {
          if (isset($component['status']) && !in_array($component['status'], $status))
          {
            $status[] = $component['status'];
          }

          if (isset($component['fixity_success']) && $component['fixity_success'])
          {
            $countVerified++;
          }

          if (isset($component['aips_count']))
          {
            $countAips += $component['aips_count'];
          }

          if (isset($component['files_count']))
          {
            $countFiles += $component['files_count'];
          }

          if (isset($component['size_count']))
          {
            $countSize += $component['size_count'];
          }

          if (isset($component['artist']))
          {
            $artist = $component['artist'];
          }
        }

        $counts = array(
          'artwork' => $work,
          'artist' => $artist,
          'components' => count($components['results']),
          'status' => count($status),
          'aips' => $countAips,
          'files' => $countFiles,
          'size' => $countSize,
          'verified' => $countVerified);

        $this->results[$department]['results'][$work]['totals'] = $counts;

        // Totals by department
        $totalArtworks ++;
        $totalAips += $countAips;
        $totalFiles += $countFiles;
        $totalSize += $countSize;
        $totalVerified += $countVerified;

        if (isset($artist) && !in_array($artist, $totalArtists))
        {
          $totalArtists[] = $artist;
        }
      }

      $totalCounts = array(
        'aips' => $totalAips,
        'files' => $totalFiles,
        'artworks' => $totalArtworks,
        'size' => $totalSize,
        'artists' => count($totalArtists),
        'verified' => $totalVerified);

      $this->results[$department]['totals'] = $totalCounts;
    }
  }

  protected function videoCharacteristics()
  {
    $this->queryBool->addMust(new \Elastica\Query\Term(array('digitalObject.mediaTypeId' => QubitTerm::VIDEO_ID)));
    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $video = array();

      // TODO? Get data from all the tracks
      $this->addItemToArray($video, 'filename', $doc['metsData']['filename']);
      $this->addItemToArray($video, 'part_of', $doc['aipPartOf']);
      $this->addItemToArray($video, 'size', $doc['metsData']['size']);
      $this->addItemToArray($video, 'format', $doc['metsData']['mediainfo']['generalTracks'][0]['format']);

      if (isset($doc['metsData']['mediainfo']['videoTracks']))
      {
        $video['video_streams_count'] = count($doc['metsData']['mediainfo']['videoTracks']);
      }

      if (isset($doc['metsData']['mediainfo']['audioTracks']))
      {
        $video['audio_streams_count'] = count($doc['metsData']['mediainfo']['audioTracks']);
      }

      // TODO? Get data from all the tracks
      // PUID ?
      $this->addItemToArray($video, 'codec_id', $doc['metsData']['mediainfo']['videoTracks'][0]['codecId']);
      $this->addItemToArray($video, 'codec', $doc['metsData']['mediainfo']['videoTracks'][0]['codec']);
      $this->addItemToArray($video, 'duration', $doc['metsData']['mediainfo']['videoTracks'][0]['duration']);
      $this->addItemToArray($video, 'width', $doc['metsData']['mediainfo']['videoTracks'][0]['width']);
      // Original width ?
      $this->addItemToArray($video, 'height', $doc['metsData']['mediainfo']['videoTracks'][0]['height']);
      // Original height ?
      $this->addItemToArray($video, 'display_aspect_ratio', $doc['metsData']['mediainfo']['videoTracks'][0]['displayAspectRatio']);
      $this->addItemToArray($video, 'frame_rate', $doc['metsData']['mediainfo']['videoTracks'][0]['frameRate']);
      // Standard ?
      $this->addItemToArray($video, 'color_space', $doc['metsData']['mediainfo']['videoTracks'][0]['colorSpace']);
      $this->addItemToArray($video, 'chroma_subsampling', $doc['metsData']['mediainfo']['videoTracks'][0]['chromaSubsampling']);
      $this->addItemToArray($video, 'bit_depth', $doc['metsData']['mediainfo']['videoTracks'][0]['bitDepth']);

      // Fron first audio track
      $this->addItemToArray($video, 'sample_rate', $doc['metsData']['mediainfo']['audioTracks'][0]['samplingRate']);
      $this->addItemToArray($video, 'compression_mode', $doc['metsData']['mediainfo']['audioTracks'][0]['compressionMode']);

      // Get last fixity check for the file AIP
      // TODO? Add fixity data to files in ES and update file when fixity checks are added
      $fixitySuccess = $lastFixityDate = null;
      if (isset($doc['aipUuid']))
      {
        $fixityQuery = new \Elastica\Query;
        $fixityQueryBool = new \Elastica\Query\Bool;
        $fixityQueryBool->addMust(new \Elastica\Query\Term(array('aip.uuid' => $doc['aipUuid'])));

        $fixityQuery->setQuery($fixityQueryBool);
        $fixityQuery->setSort(array('timeCompleted' => 'desc'));
        $fixityQuery->setLimit(1);

        $fixityResultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($fixityQuery);
        $fixityResults = $fixityResultSet->getResults();

        if (count($fixityResults) == 1)
        {
          $fixityDoc = $fixityResults[0]->getData();

          // If the last check failed set global fixity status as false
          if (isset($fixityDoc['success']))
          {
            $fixitySuccess = (bool)$fixityDoc['success'];
          }

          // Update last check date
          if (isset($fixityDoc['timeCompleted']))
          {
            $lastFixityDate = $fixityDoc['timeCompleted'];
          }
        }
      }

      $video['fixity_success'] = $fixitySuccess;
      $video['fixity_date'] = $lastFixityDate;

      $this->results[] = $video;
    }
  }

  protected function getTermName($id)
  {
    if (null !== $item = QubitTerm::getById($id))
    {
      return $item->getName(array('cultureFallback' => true));
    }
  }
}
