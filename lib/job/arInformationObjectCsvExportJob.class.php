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

    // If not using RAD, default to ISAD CSV export format
    $this->archivalStandard = 'isad';
    if (QubitSetting::getByNameAndScope('informationobject', 'default_template') == 'rad')
    {
      $this->archivalStandard = 'rad';
    }

    // Create query increasing limit from default
    $this->search = new arElasticSearchPluginQuery(1000000000);

    if ($this->params['params']['fromClipboard'])
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $this->params['params']['slugs']));
    }
    else
    {
      $this->search->addAggFilters(InformationObjectBrowseAction::$AGGS, $this->params['params']);
      $this->search->addAdvancedSearchFilters(InformationObjectBrowseAction::$NAMES, $this->params['params'], $this->archivalStandard);
    }

    $this->search->query->setSort(array('lft' => 'asc'));

    // Create temp directory in which CSV export files will be written
    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'search_export_'. $this->job->id;
    mkdir($tempPath);

    // Export CSV to temp directory
    $this->info($this->i18n->__('Starting export to %1.', array('%1' => $tempPath)));
    $itemsExported = $this->exportResults($tempPath);
    $this->info($this->i18n->__('Exported %1 descriptions.', array('%1' => $itemsExported)));

    if ($itemsExported)
    {
      // Compress CSV export files as a ZIP archive
      $this->info($this->i18n->__('Creating ZIP file %1.', array('%1' => $this->getDownloadFilePath())));
      $success = $this->createZipForDownload($tempPath);
      $this->job->downloadPath = $this->getDownloadRelativeFilePath();

      if ($success !== true)
      {
        $this->error($this->i18n->__('Failed to create ZIP file.'));

        return false;
      }

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
   * Export search results as CSV
   *
   * @param string  Path of file to write CSV data to
   *
   * @return int  Number of descriptions exported
   */
  protected function exportResults($path)
  {
    $itemsExported = 0;
    $public = isset($this->params['public']) && $this->params['public'];
    $levels = isset($this->params['levels']) ? $this->params['levels'] : array();
    $numLevels = count($levels);

    // Exporter will create a new file each 10,000 rows
    $writer = new csvInformationObjectExport($path, $this->archivalStandard, 10000);

    // store export options for use in csvInformationObjectExport
    $writer->setOptions($this->params);

    // Force loading of information object configuration, then modify writer
    // configuration
    $writer->loadResourceSpecificConfiguration('QubitInformationObject');
    array_unshift($writer->columnNames, 'referenceCode');
    array_unshift($writer->standardColumns, 'referenceCode');

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->getQuery(false, false));

    foreach ($resultSet as $hit)
    {
      $resource = QubitInformationObject::getById($hit->getId());

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

        $writer->exportResource($resource);

        // export descendants if configured
        if (!$this->params['current-level-only'])
        {
          foreach ($resource->getDescendantsForExport($this->params) as $descendant)
          {
            $writer->exportResource($descendant);
          }
        }

        // Log progress every 1000 rows
        if ($itemsExported && ($itemsExported % 1000 == 0))
        {
          $this->info($this->i18n->__('%1 items exported.', array('%1' => $itemsExported)));
        }

        $itemsExported++;
      }
    }

    return $itemsExported;
  }
}
