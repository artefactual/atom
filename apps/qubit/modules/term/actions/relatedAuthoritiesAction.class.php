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

class TermRelatedAuthoritiesAction extends TermIndexAction
{
    public const INDEX_TYPE = 'QubitActor';

    // Arrays not allowed in class constants
    public static $FILTERTAGS = [];
    public static $AGGS = [
        'languages' => [
            'type' => 'term',
            'field' => 'i18n.languages',
            'size' => 10,
        ],
        'occupations' => [
            'type' => 'term',
            'field' => 'occupations.id',
            'size' => 10,
        ],
        'places' => [
            'type' => 'term',
            'field' => 'places.id',
            'size' => 10,
        ],
        'subjects' => [
            'type' => 'term',
            'field' => 'subjects.id',
            'size' => 10,
        ],
        'direct' => [
            'type' => 'filter',
            'field' => '',
            'populate' => false,
        ],
    ];

    public function execute($request)
    {
        $this->setAndCheckResource();

        $directField = TermNavigateRelatedComponent::$TAXONOMY_ES_DIRECT_FIELDS[$this->resource->taxonomyId];
        $this::$AGGS['direct']['field'] = [$directField => $this->resource->id];

        DefaultBrowseAction::execute($request);

        // Disallow access to locked taxonomies
        if (in_array($this->resource->taxonomyId, QubitTaxonomy::$lockedTaxonomies)) {
            $this->getResponse()->setStatusCode(403);

            return sfView::NONE;
        }

        $this->setCulture($request);

        // Prepare filter tags, form, and hidden fields/values
        $this->populateFilterTags($request);

        // Take note of number of related information objects
        $resultSet = TermNavigateRelatedComponent::getEsDocsRelatedToTerm('QubitInformationObject', $this->resource);
        $this->relatedIoCount = $resultSet->count();

        // Perform search and paging
        $resultSet = $this->doSearch($request);
        $this->relatedActorCount = $resultSet->count();

        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($this->limit);
        $this->pager->init();

        $this->populateAggs($resultSet);

        // Load list terms
        $this->loadListTerms($request);
    }

    protected function populateAgg($name, $buckets)
    {
        switch ($name) {
            case 'occupations':
            case 'places':
            case 'subjects':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

                foreach (QubitTerm::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->getName(['cultureFallback' => true]);
                }

                break;

            default:
                return parent::populateAgg($name, $buckets);
        }

        return $buckets;
    }

    protected function setSort($request)
    {
        switch ($request->sort) {
            case 'alphabetic':
                $field = sprintf('i18n.%s.authorizedFormOfName.alphasort', $this->selectedCulture);
                $this->search->query->setSort([$field => $request->sortDir]);

                break;

            case 'identifier':
                $this->search->query->setSort(['descriptionIdentifier.untouched' => $request->sortDir]);

                break;

            case 'lastUpdated':
            default:
                $this->search->query->setSort(['updatedAt' => $request->sortDir]);
        }
    }

    protected function doSearch($request)
    {
        $this->setSort($request);

        $options = ['search' => $this->search];

        // Allow for only searching for actors directly related to term
        if (!empty($request->onlyDirect)) {
            $options['direct'] = true;
        }

        return TermNavigateRelatedComponent::getEsDocsRelatedToTerm('QubitActor', $this->resource, $options);
    }
}
