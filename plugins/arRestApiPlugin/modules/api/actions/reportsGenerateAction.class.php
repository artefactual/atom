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

    // TODO: Add counts for each table (last row)
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

    // TODO: Add counts for each table (last row)
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

      $this->results[] = $fixity;
    }
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

    // TODO: Add counts for each table (last row)
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

      $grouped['totals'][] = $accessLog;
    }

    // Get counts grouped by department
    foreach ($grouped['by_department'] as $department => $logs)
    {
      $artworks = array();
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

        if (isset($log['size']))
        {
          $countSize += $log['size'];
        }
      }

      $counts = array(
        'aips' => $countAips,
        'files' => $countFiles,
        'artworks' => count($artworks),
        'size' => $countSize);

      $this->results['by_department'][$department][] = $counts;
    }

    // Get counts grouped by user
    foreach ($grouped['by_user'] as $user => $logs)
    {
      $artworks = array();
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

        if (isset($log['size']))
        {
          $countSize += $log['size'];
        }
      }

      $counts = array(
        'aips' => $countAips,
        'files' => $countFiles,
        'artworks' => count($artworks),
        'size' => $countSize);

      $this->results['by_user'][$user][] = $counts;
    }

    // Get totals
    $artworks = array();
    $countAips = $countFiles = $countSize = 0;

    foreach ($grouped['totals'] as $log)
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

      if (isset($log['size']))
      {
        $countSize += $log['size'];
      }
    }

    $counts = array(
      'aips' => $countAips,
      'files' => $countFiles,
      'artworks' => count($artworks),
      'size' => $countSize);

    $this->results['totals'] = $counts;
  }
}
