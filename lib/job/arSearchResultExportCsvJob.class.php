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
  protected $extraRequiredParameters = array('params');  // Search params
  protected $downloadFileExtension = 'zip';

  protected $search;            // arElasticSearchPluginQuery instance

  protected $archivalStandard;  // Which CSV export configuration to use: either "rad" or "isad"

  public function runJob($parameters)
  {
    // If not using RAD, default to ISAD CSV export format
    $this->archivalStandard = 'isad';
    if (QubitSetting::getByNameAndScope('informationobject', 'default_template') == 'rad')
    {
      $this->archivalStandard = 'rad';
    }

    // Create query increasing limit from default
    $this->search = new arElasticSearchPluginQuery(1000000000);
    $this->search->addFacetFilters(InformationObjectBrowseAction::$FACETS, $parameters['params']);
    $this->search->addAdvancedSearchFilters(InformationObjectBrowseAction::$NAMES, $parameters['params'], $this->archivalStandard);
    $this->search->query->setSort(array('lft' => 'asc'));

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

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->getQuery(false, false));

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
