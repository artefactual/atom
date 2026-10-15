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

class AccessionBrowseAction extends DefaultBrowseAction
{
    public static $AGGS = [
        'acquisitionType' => [
            'type' => 'term',
            'field' => 'acquisitionType.id',
            'size' => 10,
        ],
        'resourceType' => [
            'type' => 'term',
            'field' => 'resourceType.id',
            'size' => 10,
        ],
        'processingStatus' => [
            'type' => 'term',
            'field' => 'processingStatus.id',
            'size' => 10,
        ],
        'processingPriority' => [
            'type' => 'term',
            'field' => 'processingPriority.id',
            'size' => 10,
        ],
        'donor' => [
            'type' => 'term',
            'field' => 'donors.id',
            'size' => 10,
        ],
        'creator' => [
            'type' => 'term',
            'field' => 'creators.id',
            'size' => 10,
        ],
    ];

    public function execute($request)
    {
        // If a global search has been requested, translate that into an advanced search
        if (isset($request->subquery)) {
            $request->sq0 = $request->subquery;
        }

        // Add first criterion to the search box if it's over any field
        if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->sq0) && !isset($request->sf0)) {
            $request->subquery = $request->sq0;
        }

        // Create the query and filter it with the selected aggs
        parent::execute($request);

        // Add advanced search filters to process sq0 query
        $this->search->addAdvancedSearchFilters([], $request->getParameterHolder()->getAll(), 'accession');

        $this->search->query->setQuery($this->search->queryBool);

        $this->setSort($request);

        // Do the search
        $resultSet = QubitSearch::getInstance()
            ->index
            ->getIndex('QubitAccession')
            ->search($this->search->query);

        $limit = sfConfig::get('app_hits_per_page', 10);
        if (isset($request->limit) && ctype_digit($request->limit)) {
            $limit = $request->limit;
        }

        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ?: 1);
        $this->pager->setMaxPerPage($limit);
        $this->pager->init();

        $this->populateAggs($resultSet);
    }

    /**
     * Set sort order based on requested ordering.
     *
     * Modifies $this->search in-place.
     *
     * @param mixed $request
     */
    protected function setSort($request)
    {
        switch ($request->sort) {
            case 'identifier': // For backward compatibility
            case 'accessionNumber':
                $this->search->query->setSort(['identifier.untouched' => $request->sortDir]);

                break;

            case 'title':
            case 'alphabetic': // For backward compatibility
                $field = sprintf('i18n.%s.title.alphasort', $this->context->user->getCulture());
                $this->search->query->addSort([$field => $request->sortDir]);

                break;

            case 'acquisitionDate':
                $this->search->query->addSort(['date' => ['order' => $request->sortDir, 'missing' => '_last']]);

                break;

            case 'relevance':
                // Keep boost options
                break;

            case 'lastUpdated':
            default:
                $this->search->query->setSort(['updatedAt' => $request->sortDir]);

                break;
        }
    }

    /**
     * Implement aggregations for fields in $AGGS.
     *
     * @param mixed $name
     * @param mixed $buckets
     */
    protected function populateAgg($name, $buckets)
    {
        switch ($name) {
            case 'acquisitionType':
            case 'resourceType':
            case 'processingStatus':
            case 'processingPriority':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

                foreach (QubitTerm::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->getName(['cultureFallback' => true]);
                }

                break;

            case 'donor':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitDonor::ID, $ids, Criteria::IN);

                foreach (QubitDonor::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
                }

                break;

            case 'creator':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitActor::ID, $ids, Criteria::IN);

                foreach (QubitActor::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
                }

                break;

            default:
                return parent::populateAgg($name, $buckets);
        }

        return $buckets;
    }
}
