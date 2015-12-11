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

/**
 * A worker to, given the HTTP GET parameters sent to advanced search,
 * replicate the search and export the resulting decriptions to CSV.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arSearchResultExportCsvJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('params');  // search params
  protected $downloadFileExtension = 'zip';

  protected $searchParams;      // key/value array of search terms
  protected $search;            // arElasticSearchPluginQuery instance

  protected $archivalStandard;  // which CSV export configuration to use: either "rad" or "isad"

  public function runJob($parameters)
  {
    $this->searchParams = $parameters['params'];
    $this->search = new arElasticSearchPluginQuery(InformationObjectBrowseAction::$FACETS);

    // Add facet filters to the query
    $this->search->addFilters($this->searchParams);

    // If not using RAD, default to ISAD CSV export format
    $this->archivalStandard = 'isad';
    if (QubitSetting::getByNameAndScope('informationobject', 'default_template') == 'rad')
    {
      $this->archivalStandard = 'rad';
    }

    // Build query from adv. search form
    $this->addCriteriaBasedOnSearchParameters();

    // Sort by ID so parents can import properly from resulting export file
    $this->search->query->setSort(array('lft' => 'asc'));

    // Increase limit from default
    $this->search->query->setLimit(1000000000);

    // Create temp directory in which CSV export files will be written
    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'search_export_'. $this->job->id;
    mkdir($tempPath);

    // Export CSV to temp directory
    $this->info('Starting export to '. $tempPath .'.');
    $itemsExported = $this->exportResults($tempPath);
    $this->info('Exported '. $itemsExported .' descriptions.');

    // Compress CSV export files as a ZIP archive
    $this->info('Creating ZIP file '. $this->getDownloadFilePath() .'.');
    $success = $this->createZipForDownload($tempPath);

    if ($success !== true)
    {
      $this->error('Failed to create ZIP file.');
      return false;
    }

    // Mark job as complete and set download path
    $this->info('Export and archiving complete.');
    $this->job->setStatusCompleted();
    $this->job->downloadPath = $this->getDownloadRelativeFilePath();
    $this->job->save();

    return true;
  }

  /**
   * Add criteria to query based on parameters.
   *
   * @return void
   */
  protected function addCriteriaBasedOnSearchParameters()
  {
    // Build query with the boolean criteria
    if (null !== $criteria = $this->parseQuery())
    {
      $this->search->queryBool->addMust($criteria);
    }

    // Process date range, defaults to inclusive
    $rangeType = $this->searchParams['rangeType'];
    if (!isset($rangeType))
    {
      $rangeType = 'inclusive';
    }

    if (null !== $criteria = InformationObjectBrowseAction::getDateRangeQuery($this->searchParams['startDate'], $this->searchParams['endDate'], $rangeType))
    {
      $this->search->queryBool->addMust($criteria);
    }

    // Process advanced search form fields
    // Some of them have the same name as a facet, this creates query
    // duplication but allows as to keep facets and adv. search form syncronized
    foreach (InformationObjectBrowseAction::$NAMES as $name)
    {
      if (!empty($this->searchParams[$name])
        && (null !== $criteria = InformationObjectBrowseAction::fieldCriteria($name, $this->searchParams[$name])))
      {
        $this->search->queryBool->addMust($criteria);
      }
    }

    // Default to show only top level descriptions
    if (!isset($this->searchParams['topLod']) || filter_var($this->searchParams['topLod'], FILTER_VALIDATE_BOOLEAN))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    }

    // Get all information objects if the query is empty
    if (1 > count($this->search->queryBool->getParams()))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\MatchAll());
    }

    $this->search->query->setQuery($this->search->queryBool);
  }

  /**
   * Translate array of search parameters to query criteria.
   *
   * Modified version of parseQuery method in the SearchAdvancedAction class
   *
   * Each set of parameters is numbered, starting at zero, and includes three
   * properties: query text (prefixed by "sq"), operation (prefixed by "so": "and" or
   * "or"), and fields (prefixed by "sf") to return (defaulting to "_all").
   *
   * For example:
   *
   *   $this->searchParams = array(
   *     'so0' => 'and',
   *     'sq0' => 'cats',
   *     'sf0' => ''
   *   );
   *
   * @return object  \Elastica\Query\Bool instance
   */
  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();

    // Include search-box query in boolean criteria
    if (1 !== preg_match('/^[\s\t\r\n]*$/', $this->searchParams['query']))
    {
      $queryField = InformationObjectBrowseAction::queryField('_all', $this->searchParams['query'], $this->archivalStandard);
      InformationObjectBrowseAction::addToQueryBool($queryBool, 'and', $queryField);
    }

    $count = 0;

    while (null !== $query = $this->searchParams['sq' . $count])
    {
      if (!empty($query))
      {
        $field = $this->searchParams['sf'.$count];
        if (empty($field))
        {
          $field = '_all';
        }

        $operator = $this->searchParams['so'.$count];
        if (empty($operator))
        {
          $operator = 'and';
        }

        $queryField = InformationObjectBrowseAction::queryField($field, $query, $this->archivalStandard);
        InformationObjectBrowseAction::addToQueryBool($queryBool, $operator, $queryField);
      }

      $count++;
    }

    if (0 == count($queryBool->getParams()))
    {
      return;
    }

    return $queryBool;
  }

  /**
   * Export search results as CSV
   *
   * @param string  Path of file to write CSV data to
   *
   * @return int  Number of descriptions exported
   */
  protected function exportResults($path)
  {
    $itemsExported = 0;

    // Exporter will create a new file each 10,000 rows
    $writer = new csvInformationObjectExport($path, $this->archivalStandard, 10000);

    // Force loading of information object configuration, then modify writer
    // configuration
    $writer->loadResourceSpecificConfiguration('QubitInformationObject');
    array_unshift($writer->columnNames, 'referenceCode');
    array_unshift($writer->standardColumns, 'referenceCode');

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

    foreach ($resultSet as $hit)
    {
      $resource = QubitInformationObject::getById($hit->getId());

      // If ElasticSearch document is stale (corresponding MySQL data deleted), ignore
      if ($resource !== null)
      {
        $writer->exportResource($resource);

        // Log progress every 1000 rows
        if ($itemsExported && ($itemsExported % 1000 == 0))
        {
          $this->info($itemsExported .' items exported.');
        }

        $itemsExported++;
      }
    }

    return $itemsExported;
  }

  protected function createZipForDownload($path)
  {
    if (!is_writable($this->getJobsDownloadDirectory()))
    {
      return false;
    }

    $zip = new ZipArchive();

    $success = $zip->open($this->getDownloadFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

    if ($success == true)
    {
      foreach(scandir($path) as $file)
      {
        if (!is_dir($file))
        {
          $zip->addFile($path . DIRECTORY_SEPARATOR . $file, $file);
        }
      }

      $zip->close();
    }

    return $success;
  }
}
