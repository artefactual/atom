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
 * Asynchronous job to export clipboard actor data and digital objects.
 */
class arAccessionExportJob extends arExportJob
{
    protected $context;

    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $downloadFileExtension = 'zip';

    /**
     * Create and return an ES search for clipboard accession records.
     *
     * @param array $parameters job parameters
     *
     * @return \Elastica\Search ES search object
     */
    public static function findExportRecords($parameters)
    {
        // Create new ES query
        $query = new arElasticSearchPluginQuery(
            arElasticSearchPluginUtil::SCROLL_SIZE
        );

        // If slugs contains '*', export all records; otherwise filter by specific slugs
        if (in_array('*', $parameters['params']['slugs'])) {
            $query->queryBool->addMust(new \Elastica\Query\MatchAll());
        } else {
            $query->queryBool->addMust(
                new \Elastica\Query\Terms('slug', $parameters['params']['slugs'])
            );
        }

        return QubitSearch::getInstance()
            ->index
            ->getIndex('QubitAccession')
            ->createSearch($query->getQuery(false, false));
    }

    /**
     * Export accession metadata.
     *
     * @param string $path to temporary export directory
     */
    protected function doExport($path)
    {
        $search = self::findExportRecords($this->params);

        if (0 == $search->count()) {
            return;
        }

        $this->itemsExported = 0;

        $this->info($this->i18n->__(
            'Exporting %1 clipboard item(s).',
            ['%1' => $search->count()],
        ));

        // Prepare CSV exporter
        $writer = new csvAccessionExport($path);

        // Scroll through results then iterate through resulting IDs
        foreach (arElasticSearchPluginUtil::getScrolledSearchResultIdentifiers($search) as $id) {
            if (null === $resource = QubitAccession::getById($id)) {
                $this->error($this->i18n->__(
                    'Cannot fetch accession, id: %1',
                    ['%1' => $id]
                ));

                continue;
            }

            $this->csvActionExport($resource, $writer);
            $this->logExportProgress();
        }
    }

    /**
     * Export resource and all related I18Ns as CSV.
     *
     * @param QubitAccession     $resource
     * @param csvAccessionExport $writer
     */
    protected function csvActionExport($resource, $writer)
    {
        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'prod', false);
        $this->context = sfContext::createInstance($configuration);

        $cultures = array_keys(DefaultTranslationLinksComponent::getOtherCulturesAvailable(
            $resource->accessionI18ns,
            'title',
            $resource->getTitle(['sourceCulture' => true])),
        );

        foreach ($cultures as $culture) {
            $this->context->getUser()->setCulture($culture);
            $writer->exportResource($resource);
        }

        ++$this->itemsExported;
    }
}
