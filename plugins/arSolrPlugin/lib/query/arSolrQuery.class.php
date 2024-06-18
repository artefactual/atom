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
 * arSolrQuery.
 */
class arSolrQuery extends arSolrAbstractQuery
{
    /**
     * Query Params.
     *
     * @var mixed
     */
    protected $query;

    /**
     * Array of fields to be queried.
     *
     * @var array
     */
    protected $fields;

    /**
     * Default operator.
     *
     * @var string defaults to 'AND'
     */
    protected $operator = 'AND';

    /**
     * Search query.
     *
     * @var string
     */
    protected $searchQuery = '*:*';

    /**
     * Params.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Constructor.
     *
     * @param mixed $searchQuery
     */
    public function __construct($searchQuery)
    {
        if (!$this->fields) {
            $this->fields = arSolrPluginUtil::getBoostedSearchFields([
                'identifier' => 10,
                'donors.i18n.%s.authorizedFormOfName' => 10,
                'i18n.%s.title' => 10,
                'i18n.%s.scopeAndContent' => 10,
                'i18n.%s.locationInformation' => 5,
                'i18n.%s.processingNotes' => 5,
                'i18n.%s.sourceOfAcquisition' => 5,
                'i18n.%s.archivalHistory' => 5,
                'i18n.%s.appraisal' => 1,
                'i18n.%s.physicalCharacteristics' => 1,
                'i18n.%s.receivedExtentUnits' => 1,
                'alternativeIdentifiers.i18n.%s.name' => 1,
                'creators.i18n.%s.authorizedFormOfName' => 1,
                'alternativeIdentifiers.i18n.%s.note' => 1,
                'alternativeIdentifiers.type.i18n.%s.name' => 1,
                'accessionEvents.i18n.%s.agent' => 1,
                'accessionEvents.type.i18n.%s.name' => 1,
                'accessionEvents.notes.i18n.%s.content' => 1,
                'donors.contactInformations.contactPerson' => 1,
                'accessionEvents.dateString' => 1,
            ]);
        }
        $this->setSearchQuery($searchQuery);
        $this->generateQueryParams();
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setDefaultOperator($operator)
    {
        $this->operator = $operator;
    }

    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
    }

    public function generateQueryParams()
    {
        $this->query = [
            'query' => [
                'dismax' => [
                    'start' => $this->offset,
                    'rows' => $this->size,
                    'q.op' => $this->operator,
                    'stopwords' => 'true',
                    'query' => $this->searchQuery,
                    'qf' => implode(' ', $this->fields),
                ],
            ],
        ];
    }

    /**
     * Sets query as raw array. Will overwrite all already set arguments.
     */
    public function setRawQuery(array $query = []): self
    {
        $this->params = $query;

        return $this;
    }

    /**
     * Sets a post_filter to the current query.
     *
     * @param mixed $filter
     */
    public function setPostFilter($filter): self
    {
        return $this->setParam('post_filter', $filter);
    }

    public function setQuery($query): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Adds an Aggregation to the query.
     *
     * @param mixed $agg
     */
    public function addAggregation($agg): self
    {
        $this->params['aggs'][] = $agg;

        return $this;
    }

    public function setType($type)
    {
        $newFieldsArr = [];
        foreach ($this->fields as $field) {
            array_push($newFieldsArr, "{$type}.{$field}");
        }
        $this->setFields($newFieldsArr);
    }
}
