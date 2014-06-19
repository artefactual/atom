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

class ApiReportsGenerateCsvAction extends QubitApiAction
{
  protected function get($request)
  {
    $this->query = new \Elastica\Query;
    $this->queryBool = new \Elastica\Query\Bool;
    $this->queryBool->addMust(new \Elastica\Query\MatchAll);

    // ES only gets 10 results if size is not set
    // Max int32 value may cause OutOfMemoryError
    $this->query->setSize(99999);

    if (!isset($request->type))
    {
      throw new QubitApi404Exception('Type not set');
    }

    // Use php://temp stream, max 2M
    $this->csv = fopen('php://temp/maxmemory:'. (2*1024*1024), 'r+');

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

    // Rewind the position of the pointer
    rewind($this->csv);

    // Disable layout
    $this->setLayout(false);

    // Set the file name
    $this->getResponse()->setHttpHeader('Content-Disposition', "attachment; filename=report.csv");

    // Send $csv content as the response body
    $this->getResponse()->setContent(stream_get_contents($this->csv));

    return 'CSV';
  }

  protected function granularIngest()
  {
    // CSV header
    fputcsv($this->csv, array(
      'AIP name',
      'AIP Classification',
      'UUID',
      'Part of',
      'Attached to',
      'Department',
      'Ingest date',
      'Ingested by'));

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

      // Add row per AIP, add empty strings if field not set
      $aipCsv = array();

      $aipCsv[] = isset($aip['name']) ? $aip['name'] : '';
      $aipCsv[] = isset($aip['classification']) ? $aip['classification'] : '';
      $aipCsv[] = isset($aip['uuid']) ? $aip['uuid'] : '';
      $aipCsv[] = isset($aip['part_of']['title']) ? $aip['part_of']['title'] : '';
      $aipCsv[] = isset($aip['attached_to']) ? $aip['attached_to'] : '';
      $aipCsv[] = isset($aip['part_of']['department']) ? $aip['part_of']['department'] : '';
      $aipCsv[] = isset($aip['ingestion_date']) ? $aip['ingestion_date'] : '';
      $aipCsv[] = isset($aip['ingestion_user']) ? $aip['ingestion_user'] : '';

      fputcsv($this->csv, $aipCsv);
    }
  }

  protected function highLevelIngest()
  {
    // New artworks
    $this->queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_artwork_record_id'))));
    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    $results['new_artworks'] = $resultSet->getTotalHits();

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

    $results['new_tech_records'] = $resultSet->getTotalHits();

    // AIPs ingested
    $this->query = new \Elastica\Query;
    $this->queryBool = new \Elastica\Query\Bool;
    $this->queryBool->addMust(new \Elastica\Query\MatchAll);
    $this->query->setSize(99999);

    $this->filterEsRangeFacet('from', 'to', 'createdAt', $this->queryBool);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($this->query);

    $results['aips_ingested'] = $resultSet->getTotalHits();

    $works = array();

    // Files ingested and total filesize ingested
    $results['files_ingested'] = 0;
    $results['total_filesize_ingested'] = 0;

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      if (isset($doc['digitalObjectCount']))
      {
        $results['files_ingested'] += $doc['digitalObjectCount'];
      }

      if (isset($doc['sizeOnDisk']))
      {
        $results['total_filesize_ingested'] += $doc['sizeOnDisk'];
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
    $results['artworks_with_materials_added'] = count($works);

    // Aggregate
    $results['aggregate'] = $results['artworks_with_materials_added'] + $results['new_artworks'];

    // CSV header
    fputcsv($this->csv, array(
      'New Artwork records',
      'Artworks with materials added',
      'Aggregate',
      'New Supporting tech records',
      'AIPs ingested',
      'Files ingested',
      'Total filesize ingested'));

    $aipCsv = array();

    $resultsCsv[] = isset($results['new_artworks']) ? $results['new_artworks'] : '';
    $resultsCsv[] = isset($results['artworks_with_materials_added']) ? $results['artworks_with_materials_added'] : '';
    $resultsCsv[] = isset($results['aggregate']) ? $results['aggregate'] : '';
    $resultsCsv[] = isset($results['new_tech_records']) ? $results['new_tech_records'] : '';
    $resultsCsv[] = isset($results['aips_ingested']) ? $results['aips_ingested'] : '';
    $resultsCsv[] = isset($results['files_ingested']) ? $results['files_ingested'] : '';
    $resultsCsv[] = isset($results['total_filesize_ingested']) ? $results['total_filesize_ingested'] : '';

    fputcsv($this->csv, $resultsCsv);
  }

  protected function fixity()
  {
    // CSV header
    fputcsv($this->csv, array(
      'AIP name',
      'UUID',
      'Part of',
      'Attached to',
      'Start time',
      'End time',
      'Duration',
      'Outcome'));

    $this->filterEsRangeFacet('from', 'to', 'timeStarted', $this->queryBool);

    $this->query->setSort(array('timeStarted' => 'desc'));
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($this->query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

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

      // Add row per AIP, add empty strings if field not set
      $fixityCsv = array();

      $fixityCsv[] = isset($fixity['aip']['name']) ? $fixity['aip']['name'] : '';
      $fixityCsv[] = isset($fixity['aip']['uuid']) ? $fixity['aip']['uuid'] : '';
      $fixityCsv[] = isset($fixity['aip']['part_of']) ? $fixity['aip']['part_of'] : '';
      $fixityCsv[] = isset($fixity['aip']['attached_to']) ? $fixity['aip']['attached_to'] : '';
      $fixityCsv[] = isset($fixity['time_started']) ? $fixity['time_started'] : '';
      $fixityCsv[] = isset($fixity['time_completed']) ? $fixity['time_completed'] : '';
      $fixityCsv[] = isset($fixity['duration']) ? $this->convertSeconds($fixity['duration']) : '';

      if (!isset($fixity['success']))
      {
        $fixityCsv[] = '';
      }
      else if ($fixity['success'])
      {
        $fixityCsv[] = 'Verified';
      }
      else
      {
        $fixityCsv[] = 'Failed';
      }

      fputcsv($this->csv, $fixityCsv);
    }
  }

  protected function fixityError()
  {
    // CSV header
    fputcsv($this->csv, array(
      'AIP name',
      'UUID',
      'Part of',
      'Attached to',
      'Fail time',
      'Replaced start',
      'Replaced end',
      'Duration',
      'By',
      'Outcome'));

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

      $fixityCsv = array();

      $fixityCsv[] = isset($fixity['aip']['name']) ? $fixity['aip']['name'] : '';
      $fixityCsv[] = isset($fixity['aip']['uuid']) ? $fixity['aip']['uuid'] : '';
      $fixityCsv[] = isset($fixity['aip']['part_of']) ? $fixity['aip']['part_of'] : '';
      $fixityCsv[] = isset($fixity['aip']['attached_to']) ? $fixity['aip']['attached_to'] : '';
      $fixityCsv[] = isset($fixity['fail_time']) ? $fixity['fail_time'] : '';
      $fixityCsv[] = isset($fixity['recovery']['time_started']) ? $fixity['recovery']['time_started'] : '';
      $fixityCsv[] = isset($fixity['recovery']['time_completed']) ? $fixity['recovery']['time_completed'] : '';
      $fixityCsv[] = isset($fixity['recovery']['duration']) ? $this->convertSeconds($fixity['recovery']['duration']) : '';
      $fixityCsv[] = isset($fixity['recovery']['user']) ? $fixity['recovery']['user'] : '';

      if (!isset($fixity['recovery']['success']))
      {
        $fixityCsv[] = '';
      }
      else if ($fixity['recovery']['success'])
      {
        $fixityCsv[] = 'Verified';
      }
      else
      {
        $fixityCsv[] = 'Failed';
      }

      fputcsv($this->csv, $fixityCsv);
    }
  }

  protected function generalDownload()
  {
    // CSV header
    fputcsv($this->csv, array(
      'User',
      'Download type',
      'Download datetime',
      'Name',
      'UUID',
      'Total Filesize',
      'Parent Artwork',
      'Attached to',
      'Department',
      'Download reason'));

    $sql  = 'SELECT
                access.date,
                access.object_id,
                access.type_id,
                access.reason,
                user.username';
    $sql .= ' FROM '.QubitAccessLog::TABLE_NAME.' access';
    $sql .= ' JOIN '.QubitUser::TABLE_NAME.' user
                ON access.user_id = user.id';
    $sql .= ' WHERE access.type_id in (?, ?)';

    if (isset($this->request->from) && ctype_digit($this->request->from))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->from / 1000));

      $sql .= ' AND access.date >= "'.$date->format('Y-m-d H:i:s').'"';
    }

    if (isset($this->request->to) && ctype_digit($this->request->to))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->to / 1000));

      $sql .= ' AND access.date <= "'.$date->format('Y-m-d H:i:s').'"';
    }

    $sql .= ' ORDER BY access.date';

    $results = QubitPdo::fetchAll($sql, array(QubitTerm::ACCESS_LOG_AIP_FILE_DOWNLOAD_ENTRY, QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY));

    foreach ($results as $result)
    {
      $accessLog = array();

      $this->addItemToArray($accessLog, 'user', $result->username);
      $this->addItemToArray($accessLog, 'date', arElasticSearchPluginUtil::convertDate($result->date));
      $this->addItemToArray($accessLog, 'reason', $result->reason);

      // Get AIP/file data from ES
      if ($result->type_id == QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY)
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

      $accessLogCsv = array();

      $accessLogCsv[] = isset($accessLog['user']) ? $accessLog['user'] : '';
      $accessLogCsv[] = isset($accessLog['type']) ? $accessLog['type'] : '';
      $accessLogCsv[] = isset($accessLog['date']) ? $accessLog['date'] : '';
      $accessLogCsv[] = isset($accessLog['name']) ? $accessLog['name'] : '';
      $accessLogCsv[] = isset($accessLog['uuid']) ? $accessLog['uuid'] : '';
      $accessLogCsv[] = isset($accessLog['size']) ? $accessLog['size'] : '';
      $accessLogCsv[] = isset($accessLog['part_of']) ? $accessLog['part_of'] : '';
      $accessLogCsv[] = isset($accessLog['attached_to']) ? $accessLog['attached_to'] : '';
      $accessLogCsv[] = isset($accessLog['department']) ? $accessLog['department'] : '';
      $accessLogCsv[] = isset($accessLog['reason']) ? $accessLog['reason'] : '';

      fputcsv($this->csv, $accessLogCsv);
    }
  }

  protected function amountDownloaded()
  {
    // CSV header
    fputcsv($this->csv, array(
      'Users',
      'AIPs downloaded',
      'Files downloaded',
      'Total filesize',
      'Parent artworks',
      'Departments'));

    $sql  = 'SELECT
                access.object_id,
                access.type_id,
                user.username';
    $sql .= ' FROM '.QubitAccessLog::TABLE_NAME.' access';
    $sql .= ' JOIN '.QubitUser::TABLE_NAME.' user
                ON access.user_id = user.id';
    $sql .= ' WHERE access.type_id in (?, ?)';

    if (isset($this->request->from) && ctype_digit($this->request->from))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->from / 1000));

      $sql .= ' AND access.date >= "'.$date->format('Y-m-d H:i:s').'"';
    }

    if (isset($this->request->to) && ctype_digit($this->request->to))
    {
      $date = new DateTime();
      $date->setTimestamp((int)($this->request->to / 1000));

      $sql .= ' AND access.date <= "'.$date->format('Y-m-d H:i:s').'"';
    }

    $sql .= ' ORDER BY access.date';

    $results = QubitPdo::fetchAll($sql, array(QubitTerm::ACCESS_LOG_AIP_FILE_DOWNLOAD_ENTRY, QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY));

    foreach ($results as $result)
    {
      $accessLog = array();

      $this->addItemToArray($accessLog, 'user', $result->username);

      // Get AIP/file data from ES
      if ($result->type_id == QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY)
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

      $logs[] = $accessLog;
    }

    // Get totals
    $artworks = $users = $departments = array();
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

      if (isset($log['department']) && !in_array($log['department'], $departments))
      {
        $departments[] = $log['department'];
      }

      if (isset($log['size']))
      {
        $countSize += $log['size'];
      }
    }

    $counts = array();

    $counts[] = count($users);
    $counts[] = $countAips;
    $counts[] = $countFiles;
    $counts[] = $countSize;
    $counts[] = count($artworks);
    $counts[] = count($departments);

    fputcsv($this->csv, $counts);
  }

  protected function componentLevel()
  {
    // CSV header
    fputcsv($this->csv, array(
      'Artwork',
      'Artist',
      'Department',
      'Component',
      'Status',
      'Total Filesize',
      'No. associated AIPs',
      'No. associated files',
      'Fixity status',
      'Fixity date'));

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
    $this->query->setSort(array('createdAt' => 'desc'));

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

            // If there last check failed set global fixity status as false
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

      $componentCsv = array();

      $componentCsv[] = isset($component['artwork']) ? $component['artwork'] : '';
      $componentCsv[] = isset($component['artist']) ? $component['artist'] : '';
      $componentCsv[] = isset($component['department']) ? $component['department'] : '';
      $componentCsv[] = isset($component['component']) ? $component['component'] : '';
      $componentCsv[] = isset($component['status']) ? $component['status'] : '';
      $componentCsv[] = isset($component['size_count']) ? $component['size_count'] : '';
      $componentCsv[] = isset($component['aips_count']) ? $component['aips_count'] : '';
      $componentCsv[] = isset($component['files_count']) ? $component['files_count'] : '';

      if (!isset($component['fixity_success']))
      {
        $componentCsv[] = '';
      }
      else if ($component['fixity_success'])
      {
        $componentCsv[] = 'Verified';
      }
      else
      {
        $componentCsv[] = 'Failed';
      }

      $componentCsv[] = isset($component['last_fixity_date']) ? $component['last_fixity_date'] : '';

      fputcsv($this->csv, $componentCsv);
    }
  }

  protected function videoCharacteristics()
  {
    // CSV header
    fputcsv($this->csv, array(
      'Filename',
      'Part of',
      'Total file size',
      'Format',
      'Video streams',
      'Audio streams',
      'Codec ID',
      'Codec',
      'Duration',
      'Width',
      'Height',
      'Display aspect ratio',
      'Frame rate',
      'Color space',
      'Chroma subsampling',
      'Bit depth',
      'Sample rate',
      'Compression mode',
      'Fixity status',
      'Fixity date'));

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

      $videoCsv = array();

      $videoCsv[] = isset($video['filename']) ? $video['filename'] : '';
      $videoCsv[] = isset($video['part_of']) ? $video['part_of'] : '';
      $videoCsv[] = isset($video['size']) ? $video['size'] : '';
      $videoCsv[] = isset($video['format']) ? $video['format'] : '';
      $videoCsv[] = isset($video['video_streams_count']) ? $video['video_streams_count'] : '';
      $videoCsv[] = isset($video['audio_streams_count']) ? $video['audio_streams_count'] : '';
      $videoCsv[] = isset($video['codec_id']) ? $video['codec_id'] : '';
      $videoCsv[] = isset($video['codec']) ? $video['codec'] : '';
      $videoCsv[] = isset($video['duration']) ? $this->convertSeconds($video['duration']) : '';
      $videoCsv[] = isset($video['width']) ? $video['width'] : '';
      $videoCsv[] = isset($video['height']) ? $video['height'] : '';
      $videoCsv[] = isset($video['display_aspect_ratio']) ? $video['display_aspect_ratio'] : '';
      $videoCsv[] = isset($video['frame_rate']) ? $video['frame_rate'] : '';
      $videoCsv[] = isset($video['color_space']) ? $video['color_space'] : '';
      $videoCsv[] = isset($video['chroma_subsampling']) ? $video['chroma_subsampling'] : '';
      $videoCsv[] = isset($video['bit_depth']) ? $video['bit_depth'] : '';
      $videoCsv[] = isset($video['sample_rate']) ? $video['sample_rate'] : '';
      $videoCsv[] = isset($video['compression_mode']) ? $video['compression_mode'] : '';

      if (!isset($video['fixity_success']))
      {
        $videoCsv[] = '';
      }
      else if ($video['fixity_success'])
      {
        $videoCsv[] = 'Verified';
      }
      else
      {
        $videoCsv[] = 'Failed';
      }

      $videoCsv[] = isset($video['fixity_date']) ? $video['fixity_date'] : '';

      fputcsv($this->csv, $videoCsv);
    }
  }

  protected function getTermName($id)
  {
    if (null !== $item = QubitTerm::getById($id))
    {
      return $item->getName(array('cultureFallback' => true));
    }
  }

  protected function convertSeconds($time)
  {
    // Calculate
    $hours = floor($time / 3600);
    $time -= $hours * 3600;
    $minutes = floor($time / 60);
    $time -= $minutes * 60;
    $seconds = $time;

    // Return
    if ($hours > 0)
    {
      return $hours.'h '.$minutes.'m '.$seconds.'s';
    }
    else if ($minutes > 0)
    {
      return $minutes.'m '.$seconds.'s';
    }
    else
    {
      return $seconds.'s';
    }
  }
}
