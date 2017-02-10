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
 * Asynchronous job to export repository metadata from clipboard.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arRepositoryCsvExportJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $search;
  protected $params = array();

  public function runJob($parameters)
  {
    $this->params = $parameters;

    // Create query increasing limit from default
    $this->search = new arElasticSearchPluginQuery(1000000000);
    $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $this->params['params']['slugs']));

    $this->downloadFileExtension = 'csv';
    $path = $this->getDownloadRelativeFilePath();

    // Export CSV to temp directory
    $this->info($this->i18n->__('Starting export to %1', array('%1' => $path)));

    if (-1 === $itemsExported = $this->exportResults($path))
    {
      return false;
    }

    $this->info($this->i18n->__('Exported %1 repositories.', array('%1' => $itemsExported)));

    // Mark job as complete and set download path
    $this->info($this->i18n->__('Export and archiving complete.'));
    $this->job->setStatusCompleted();
    $this->job->downloadPath = $path;
    $this->job->save();

    return true;
  }

  /**
   * Export search results as CSV
   *
   * @param string  Path of file to write CSV data to
   *
   * @return int  Number of descriptions exported, -1 if and error occurred and to end the job.
   */
  protected function exportResults($path)
  {
    $itemsExported = 0;

    $resultSet = QubitSearch::getInstance()->index->getType('QubitRepository')->search($this->search->getQuery(false, false));

    $writer = new csvRepositoryExport($path, null, 10000);
    $writer->loadResourceSpecificConfiguration('QubitRepository');

    foreach ($resultSet as $hit)
    {
      if (null === $resource = QubitRepository::getById($hit->getId()))
      {
        $this->error($this->i18n->__('Cannot fetch repository, id: '.$hit->getId()));
        return -1;
      }

      $writer->exportResource($resource);

      // Log progress every 1000 rows
      if ($itemsExported && ($itemsExported % 1000 == 0))
      {
        $this->info($this->i18n->__('%1 items exported.', array('%1' => $itemsExported)));
      }

      $itemsExported++;
    }

    return $itemsExported;
  }
}
