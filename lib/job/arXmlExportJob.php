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

class arXmlExportJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $downloadFileExtension = 'zip';
  protected $search;            // arElasticSearchPluginQuery instance
  protected $errors = null;

  public function runJob($parameters)
  {
    // Create query increasing limit from default
    $this->search = new arElasticSearchPluginQuery(1000000000);

    if ($parameters['params']['fromClipboard'])
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $parameters['params']['slugs']));
    }
    else
    {
      $this->search->addFacetFilters(InformationObjectBrowseAction::$FACETS, $parameters['params']);
      $this->search->addAdvancedSearchFilters(InformationObjectBrowseAction::$NAMES, $parameters['params'], $this->archivalStandard);
    }

    $this->search->query->setSort(array('lft' => 'asc'));

    // Create temp directory in which CSV export files will be written
    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'search_export_'. $this->job->id;
    mkdir($tempPath);

    // Export CSV to temp directory
    $this->info($this->i18n->__('Starting export to %1.', array('%1' => $tempPath)));

    $this->exportResults($tempPath, $parameters);

    // Output CLI task messages.
    if (null != $this->errors)
    {
      foreach ($this->errors as $error)
      {
        $this->info($error);
      }
    }

    // Compress CSV export files as a ZIP archive
    $this->info($this->i18n->__('Creating ZIP file %1.', array('%1' => $this->getDownloadFilePath())));
    $success = $this->createZipForDownload($tempPath);

    if ($success !== true)
    {
      $this->error($this->i18n->__('Failed to create ZIP file.'));

      return false;
    }

    // Mark job as complete and set download path
    $this->info($this->i18n->__('Export and archiving complete.'));
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
   * @return null
   */
  protected function exportResults($path, $params)
  {
    $exitCode = 0;
    $resultIds = array();
    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->getQuery(false, false));

    foreach ($resultSet as $hit)
    {
      $resultIds[] = $hit->getId();
    }

    // If ElasticSearch document is stale (corresponding MySQL data deleted), ignore
    if (empty($resultIds))
    {
      $this->error($this->i18n->__('Records not found in search index.'));
      return;
    }

    foreach ($resultIds as $result)
    {
      $taskClassName = 'export:bulk';

      $criteria = sprintf('--criteria="i.id = (%s)"', $result);
      $currentLevelOnly = (true == $params['current-level-only']) ? '--current-level-only' : '';
      $public = (true == $params['public']) ? '--public' : '';

      $command = sprintf('php %s %s %s %s %s %s',
        escapeshellarg(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony'),
        escapeshellarg($taskClassName),
        $criteria,
        $currentLevelOnly,
        $public,
        escapeshellarg($path));

      // Log the command string in the job output window.
      $output[] = $command;

      // stderr to stdout
      $command .= ' 2>&1';

      // Run
      exec($command, $output, $exitCode);

      // Throw exception if exit code is greater than zero
      if (0 < $exitCode)
      {
        $output = implode($output, "<br />");

        throw new sfException($output);
      }
      else
      {
        // Warnings
        $this->errors = $output;
      }
    }
  }
}
