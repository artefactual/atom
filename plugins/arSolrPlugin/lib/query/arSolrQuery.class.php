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
            $this->fields = arSolrPluginUtil::getBoostedSearchFields(arSolrPluginUtil::getAllFields('informationObject'));
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

    public function getFields()
    {
        return $this->fields;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getSearchQuery()
    {
        return $this->searchQuery;
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
                'edismax' => [
                    'q.op' => $this->operator,
                    'stopwords' => 'true',
                    'query' => $this->searchQuery,
                    'qf' => implode(' ', $this->fields),
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
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
