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
class arActorExportJob extends arExportJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $downloadFileExtension = 'zip';

    /**
     * Create and return an ES search for clipboard actor records.
     *
     * @param array $parameters job parameters
     *
     * @return \Elastica\Search ES search object
     */
    public static function findExportRecords($parameters)
    {
        $query = new arElasticSearchPluginQuery(
            arElasticSearchPluginUtil::SCROLL_SIZE
        );

        $query->queryBool->addMust(
            new \Elastica\Query\Terms('slug', $parameters['params']['slugs'])
        );

        return QubitSearch::getInstance()
            ->index
            ->getIndex('QubitActor')
            ->createSearch($query->getQuery(false, false));
    }

    /**
     * Export actor metadata and related digital objects if appropriate.
     *
     * @param string $path to tempoarary export directory
     */
    protected function doExport($path)
    {
        $search = self::findExportRecords($this->params);

        $this->itemsExported = 0;

        // Scroll through results then iterate through resulting IDs
        foreach (arElasticSearchPluginUtil::getScrolledSearchResultIdentifiers($search) as $id) {
            if (null === $resource = QubitActor::getById($id)) {
                $this->error($this->i18n->__(
                    'Cannot fetch actor, id: %1',
                    ['%1' => $id]
                ));

                return;
            }

            $this->csvActionExport($path, $resource);
            $this->logExportProgress();
        }
    }

    protected function csvActionExport($path, $resource)
    {
        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'prod', false);
        $this->context = sfContext::createInstance($configuration);

        // Prepare CSV exporter
        $writer = new csvActorExport($path);
        $writer->setOptions(['relations' => true]);

        // Export actors and, optionally, related data
        $cultures = array_keys(DefaultTranslationLinksComponent::getOtherCulturesAvailable($resource->actorI18ns, 'authorizedFormOfName', $resource->getAuthorizedFormOfName(['sourceCulture' => true])));

        // Write row to file and initialize row
        foreach ($cultures as $culture) {
            $actor = QubitActor::getById($resource->id);
            $this->context->getUser()->setCulture($culture);

            $writer->exportResource($actor);
        }

        ++$this->itemsExported;
    }
}
