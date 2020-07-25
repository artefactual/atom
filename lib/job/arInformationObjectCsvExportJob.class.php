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
 * replicate the search and export the resulting descriptions to CSV.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arInformationObjectCsvExportJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('params');  // Search params
  protected $downloadFileExtension = 'zip';
  protected $search;            // arElasticSearchPluginQuery instance
  protected $archivalStandard;  // Which CSV export configuration to use: either "rad" or "isad"
  protected $params = array();

  public function runJob($parameters)
  {
    $this->params = $parameters;

    $tempPath = $this->createJobTempDir();

    // Export CSV to temp directory
    $this->info($this->i18n->__('Starting export to %1.', array('%1' => $tempPath)));
    $itemsExported = $this->exportOrCheckForResults($parameters, $tempPath, $this);
    $this->info($this->i18n->__('Exported %1 descriptions.', array('%1' => $itemsExported)));

    if ($itemsExported)
    {
      // Compress CSV export files as a ZIP archive
      $this->info($this->i18n->__('Creating ZIP file %1.', array('%1' => $this->getDownloadFilePath())));
      $errors = $this->createZipForDownload($tempPath);

      if (!empty($errors))
      {
        $this->error($this->i18n->__('Failed to create ZIP file.') . ' : ' . implode(' : ', $errors));
        return false;
      }

      $this->job->downloadPath = $this->getDownloadRelativeFilePath();
      $this->info($this->i18n->__('Export and archiving complete.'));
    }
    else
    {
      $this->info($this->i18n->__('No relevant archival descriptions were found to export.'));
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  /**
   * Get the current archival standard
   *
   * @return arElasticSearchPluginQuery  AtoM Elasticsearch query
   */
  static public function getCurrentArchivalStandard()
  {
    // If not using RAD, default to ISAD CSV export format
    $archivalStandard = 'isad';
    if (QubitSetting::getByNameAndScope('informationobject', 'default_template') == 'rad')
    {
      $archivalStandard = 'rad';
    }

    return $archivalStandard;
  }

  /**
   * Create AtoM Elasticsearch query from export parameters
   *
   * @param array $parameters  Export parameters
   *
   * @return arElasticSearchPluginQuery  AtoM Elasticsearch query
   */
  static public function searchFromParameters($parameters)
  {
    // Create query
    $search = new arElasticSearchPluginQuery(arElasticSearchPluginUtil::SCROLL_SIZE);

    if ($parameters['params']['fromClipboard'])
    {
      $search->queryBool->addMust(new \Elastica\Query\Terms('slug', $parameters['params']['slugs']));
    }
    else
    {
      $search->addAggFilters(InformationObjectBrowseAction::$AGGS, $parameters['params']);
      $search->addAdvancedSearchFilters(
        InformationObjectBrowseAction::$NAMES,
        $parameters['params'],
        self::getCurrentArchivalStandard()
      );
    }

    $search->query->setSort(array('lft' => 'asc'));

    return $search;
  }

  /**
   * Return search result count and, optionally, export search results
   *
   * @param array $parameters  Export parameters
   * @param string $path  Path of file to write CSV data to
   * @param arInformationObjectCsvExportJob $job  Job object for logging progress
   *
   * @return int  Number of descriptions exported
   */
  static public function exportOrCheckForResults($parameters, $path = false, $job = false)
  {
    $itemsExported = 0;
    $public = isset($parameters['public']) && $parameters['public'];
    $levels = isset($parameters['levels']) ? $parameters['levels'] : array();
    $numLevels = count($levels);

    // If no path supplied, just a count of what would be exported is desired so don't actually set up an export
    if ($path)
    {
      // Exporter will create a new file each 10,000 rows
      $writer = new csvInformationObjectExport($path, self::getCurrentArchivalStandard(), 10000);

      // Store export options for use in csvInformationObjectExport
      $writer->setOptions($parameters);

      // Force loading of information object configuration, then modify writer configuration
      $writer->loadResourceSpecificConfiguration('QubitInformationObject');
      array_unshift($writer->columnNames, 'referenceCode');
      array_unshift($writer->standardColumns, 'referenceCode');
    }

    $search = self::searchFromParameters($parameters);
    $search = QubitSearch::getInstance()->index->getType('QubitInformationObject')->createSearch($search->getQuery(false, false));

    // Scroll through results then iterate through resulting IDs
    foreach (arElasticSearchPluginUtil::getScrolledSearchResultIdentifiers($search) as $id)
    {
      $resource = QubitInformationObject::getById($id);

      // If ElasticSearch document is stale (corresponding MySQL data deleted), ignore
      if ($resource !== null)
      {
        // Don't export draft descriptions with public option.
        // Don't export records if level of description is not in list of selected LODs.
        if (($public && $resource->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_DRAFT_ID) ||
          (0 < $numLevels && !array_key_exists($resource->levelOfDescriptionId, $levels)))
        {
          continue;
        }

        if ($path)
        {
          $writer->exportResource($resource);

          // Export descendants if configured
          if (!$parameters['current-level-only'])
          {
            foreach ($resource->getDescendantsForExport($parameters) as $descendant)
            {
              $writer->exportResource($descendant);
            }
          }
        }

        // Log progress every 1000 rows
        if ($job && $itemsExported && ($itemsExported % 1000 == 0))
        {
          $job->info($job->i18n->__('%1 items exported.', array('%1' => $itemsExported)));
        }

        $itemsExported++;
      }
    }

    return $itemsExported;
  }
}
