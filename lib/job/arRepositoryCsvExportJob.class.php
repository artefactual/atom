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
 */
class arRepositoryCsvExportJob extends arExportJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $downloadFileExtension = 'zip';
    protected $zipFileDownload;
    protected $search;
    protected $params = [];

    public function runJob($parameters)
    {
        $this->params = $parameters;

        // Create query increasing limit from default
        $this->search = new arElasticSearchPluginQuery(arElasticSearchPluginUtil::SCROLL_SIZE);
        $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $this->params['params']['slugs']));

        $this->zipFileDownload = new arZipFileDownload(
            $this->job->id,
            $this->downloadFileExtension,
            $this->logger,
        );
        $tempPath = $this->zipFileDownload->createJobTempDir();

        // Export CSV to temp directory
        $this->info($this->i18n->__('Starting export to %1', ['%1' => $tempPath]));

        if (-1 === $itemsExported = $this->exportResults($tempPath)) {
            return false;
        }

        $this->info($this->i18n->__('Exported %1 repositories.', ['%1' => $itemsExported]));

        // Compress CSV export files as a ZIP archive
        $this->info($this->i18n->__('Creating ZIP file %1', ['%1' => $this->zipFileDownload->getDownloadFilePath()]));
        $errors = $this->zipFileDownload->createZipForDownload($tempPath, $this->user->isAdministrator());

        if (!empty($errors)) {
            $this->error($this->i18n->__('Failed to create ZIP file.').' : '.implode(' : ', $errors));

            return false;
        }

        // Mark job as complete and set download path
        $this->info($this->i18n->__('Export and archiving complete.'));
        $this->job->setStatusCompleted();
        $this->job->downloadPath = $this->zipFileDownload->getDownloadRelativeFilePath();
        $this->job->save();

        return true;
    }

    /**
     * Export search results as CSV.
     *
     * @param string  Path of file to write CSV data to
     * @param mixed $path
     *
     * @return int number of descriptions exported, -1 if and error occurred and to end the job
     */
    protected function exportResults($path)
    {
        $itemsExported = 0;

        $search = QubitSearch::getInstance()->index->getIndex('QubitRepository')->createSearch($this->search->getQuery(false, false));

        $writer = new csvRepositoryExport($path, null, 10000);
        $writer->loadResourceSpecificConfiguration('QubitRepository');

        // Scroll through results then iterate through resulting IDs
        foreach (arElasticSearchPluginUtil::getScrolledSearchResultIdentifiers($search) as $id) {
            if (null === $resource = QubitRepository::getById($id)) {
                $this->error($this->i18n->__('Cannot fetch repository, id: %1', ['%1' => $id]));

                return -1;
            }

            $itemsExported = $this->csvActionExport($resource, $writer);
        }

        return $itemsExported;
    }

    protected function csvActionExport($resource, $writer)
    {
        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'prod', false);
        $this->context = sfContext::createInstance($configuration);

        // Export repositories and, optionally, related data
        $itemsExported = 0;

        $cultures = array_keys(DefaultTranslationLinksComponent::getOtherCulturesAvailable($resource->actorI18ns, 'authorizedFormOfName', $resource->getAuthorizedFormOfName(['sourceCulture' => true])));

        // Write row to file and initialize row
        foreach ($cultures as $culture) {
            $this->context->getUser()->setCulture($culture);

            $writer->exportResource($resource);

            // Log progress every 1000 rows
            if ($itemsExported && (0 == $itemsExported % 1000)) {
                $this->info($this->i18n->__('%1 items exported.', ['%1' => $itemsExported]));
            }
        }

        ++$itemsExported;

        return $itemsExported;
    }
}
