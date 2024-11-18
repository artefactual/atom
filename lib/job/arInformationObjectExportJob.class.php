<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A worker to, given the HTTP GET parameters sent to advanced search,
 * replicate the search and export the resulting descriptions to CSV.
 */
class arInformationObjectExportJob extends arExportJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $extraRequiredParameters = ['params'];  // Search params
    protected $downloadFileExtension = 'zip';
    protected $search;            // arElasticSearchPluginQuery instance
    protected $params = [];

    /**
     * Find records for export based on export parameters.
     *
     * @param array $parameters Export parameters
     *
     * @return arElasticSearchPluginQuery AtoM Elasticsearch query
     */
    public static function findExportRecords($parameters)
    {
        // Create ES query
        $query = new arElasticSearchPluginQuery(
            arElasticSearchPluginUtil::SCROLL_SIZE
        );

        if ($parameters['params']['fromClipboard']) {
            self::addClipboardCriteria($query, $parameters);
        } else {
            $query->addAggFilters(
                InformationObjectBrowseAction::$AGGS,
                $parameters['params']
            );

            $query->addAdvancedSearchFilters(
                InformationObjectBrowseAction::$NAMES,
                $parameters['params'],
                self::getCurrentArchivalStandard()
            );
        }

        $query->query->setSort(['lft' => 'asc']);

        return QubitSearch::getInstance()
            ->index
            ->getIndex('QubitInformationObject')
            ->createSearch($query->getQuery(false, false));
    }

    /**
     * Get the current archival standard.
     *
     * @return arElasticSearchPluginQuery AtoM Elasticsearch query
     */
    public static function getCurrentArchivalStandard()
    {
        if ('rad' == QubitSetting::getByNameAndScope('informationobject', 'default_template')) {
            return 'rad';
        }

        // If not using RAD, default to ISAD CSV export format
        return 'isad';
    }

    /**
     * Add clipboard search criteria to ES query.
     *
     * @param mixed $search
     * @param mixed $parameters
     */
    protected static function addClipboardCriteria(&$search, $parameters)
    {
        $search->queryBool->addMust(
            new \Elastica\Query\Terms('slug', $parameters['params']['slugs'])
        );

        // If "public" option is set, filter out draft records
        if (isset($parameters['public']) && $parameters['public']) {
            $search->queryBool->addMust(new \Elastica\Query\Term(
                ['publicationStatusId' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID]
            ));
        }
    }

    /**
     * Export clipboard item metadata and digital objects.
     *
     * @param string $path temporary export job working directory
     */
    protected function doExport($path)
    {
        $search = self::findExportRecords($this->params);

        if (0 == $search->count()) {
            return;
        }

        $this->info($this->i18n->__(
            'Exporting %1 clipboard item(s).',
            ['%1' => $search->count()]
        ));

        // Scroll through results then iterate through resulting IDs
        foreach (arElasticSearchPluginUtil::getScrolledSearchResultIdentifiers($search) as $id) {
            $resource = QubitInformationObject::getById($id);

            // Skip if ElasticSearch document is stale (no corresponding MySQL data)
            if (null == $resource) {
                continue;
            }

            $this->exportResource($resource, $path);
        }
    }

    /**
     * Test if passed level of description id is allowed for export.
     *
     * @param int $levelId level of description id to test
     *
     * @return bool true if all levels are allowed, or level is in selected levels
     */
    protected function isAllowedLevelId($levelId)
    {
        // If params['levels'] is empty all levels are allowed, otherwise check
        // that passed $levelId is in the list of selected levels
        return empty($this->params['levels'])
            || array_key_exists($levelId, $this->params['levels']);
    }
}
