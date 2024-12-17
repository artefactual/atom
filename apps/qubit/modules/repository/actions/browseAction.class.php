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
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class RepositoryBrowseAction extends DefaultBrowseAction
{
    public const INDEX_TYPE = 'QubitRepository';

    // Arrays not allowed in class constants
    public static $AGGS = [
        'languages' => [
            'type' => 'term',
            'field' => 'i18n.languages',
            'size' => 10,
        ],
        'types' => [
            'type' => 'term',
            'field' => 'types',
            'size' => 10,
        ],
        'regions' => [
            'type' => 'term',
            'field' => 'contactInformations.i18n.%s.region.untouched',
            'size' => 10,
        ],
        'geographicSubregions' => [
            'type' => 'term',
            'field' => 'geographicSubregions',
            'size' => 10,
        ],
        'locality' => [
            'type' => 'term',
            'field' => 'contactInformations.i18n.%s.city.untouched',
            'size' => 10,
        ],
        'thematicAreas' => [
            'type' => 'term',
            'field' => 'thematicAreas',
            'size' => 10,
        ],
    ];

    public function execute($request)
    {
        // Must call this first as parent::execute() calls addFacets().
        $this->setI18nFieldCultures();

        parent::execute($request);

        $this->cardView = 'card';
        $this->tableView = 'table';
        $allowedViews = [$this->cardView, $this->tableView];

        if (sfConfig::get('app_enable_institutional_scoping')) {
            if (isset($request->repos) && ctype_digit($request->repos) && null !== $this->repos = QubitRepository::getById($request->repos)) {
                $this->search->queryBool->addMust(new \Elastica\Query\Term(['repository.id' => $request->repos]));

                // Store realm in user session
                $this->context->user->setAttribute('search-realm', $request->repos);
            } else {
                // Remove search-realm
                $this->context->user->removeAttribute('search-realm');
            }
        }

        if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery)) {
            $this->search->queryBool->addMust(new \Elastica\Query\MatchAll());
        } else {
            $this->search->queryBool->addMust(
                arElasticSearchPluginUtil::generateQueryString(
                    $request->subquery,
                    arElasticSearchPluginUtil::getAllFields('repository')
                )
            );
        }

        $i18n = sprintf('i18n.%s.', $this->selectedCulture);

        switch ($request->sort) {
            case 'nameUp':
                $this->search->query->setSort([$i18n.'authorizedFormOfName.alphasort' => 'asc']);

                break;

            case 'nameDown':
                $this->search->query->setSort([$i18n.'authorizedFormOfName.alphasort' => 'desc']);

                break;

            case 'regionUp':
                $this->search->query->setSort([$i18n.'region.untouched' => 'asc']);

                break;

            case 'regionDown':
                $this->search->query->setSort([$i18n.'region.untouched' => 'desc']);

                break;

            case 'localityUp':
                $this->search->query->setSort([$i18n.'city.untouched' => 'asc']);

                break;

            case 'localityDown':
                $this->search->query->setSort([$i18n.'city.untouched' => 'desc']);

                break;

            case 'identifier':
                $this->search->query->addSort(['identifier.untouched' => $request->sortDir]);

                // no break
            case 'alphabetic':
                $this->search->query->addSort([$i18n.'authorizedFormOfName.alphasort' => $request->sortDir]);

                break;

            case 'lastUpdated':
            default:
                $this->search->query->setSort(['updatedAt' => $request->sortDir]);
        }

        $this->search->query->setQuery($this->search->queryBool);

        $resultSet = QubitSearch::getInstance()->index->getIndex('QubitRepository')->search($this->search->query);

        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($this->limit);
        $this->pager->init();

        $this->populateAggs($resultSet);

        if (isset($request->view) && in_array($request->view, $allowedViews)) {
            $this->view = $request->view;
        } else {
            $this->view = sfConfig::get('app_default_repository_browse_view', 'card');
        }

        $this->getAdvancedFilterTerms();
    }

    protected function populateAgg($name, $buckets)
    {
        switch ($name) {
            case 'types':
            case 'geographicSubregions':
            case 'thematicAreas':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

                foreach (QubitTerm::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->getName(['cultureFallback' => true]);
                }

                break;

            case 'regions':
            case 'locality':
                foreach ($buckets as $key => $bucket) {
                    $buckets[$key]['display'] = $bucket['key'];
                }

                break;

            default:
                return parent::populateAgg($name, $buckets);
        }

        return $buckets;
    }

    private function getAdvancedFilterTerms()
    {
        $limit = 500;

        $this->thematicAreas = QubitTerm::getEsTermsByTaxonomyId(QubitTaxonomy::THEMATIC_AREA_ID, $limit);
        $this->repositoryTypes = QubitTerm::getEsTermsByTaxonomyId(QubitTaxonomy::REPOSITORY_TYPE_ID, $limit);

        $query = new \Elastica\Query(new \Elastica\Query\MatchAll());
        $query->setSize($limit);

        $this->repositories = QubitSearch::getInstance()->index->getIndex('QubitRepository')->search($query);
    }

    /**
     * Set FACET i18n fields to the current culture. In the future, we'll want to implement culture fallback
     * for these fields as well (see #11121).
     */
    private function setI18nFieldCultures()
    {
        foreach (self::$AGGS as $key => &$value) {
            if (is_array($value['field']) && false !== array_search('i18n.%s', $value['field'])) {
                $value['field'] = sprintf($value['field'], $this->context->user->getCulture());
            }
        }
    }
}
