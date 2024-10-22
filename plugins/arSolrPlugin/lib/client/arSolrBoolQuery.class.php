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

class arSolrBoolQuery extends arSolrAbstractQuery
{
    /**
     * Field type.
     *
     * @var string
     */
    protected ?string $type = null;

    /**
     * Assemble BoolQuery.
     *
     * @return arSolrBoolQuery object
     */
    public function generateQueryParams()
    {
        $params = $this->getParams();
        $sort = $this->getSort();

        $mustQuery = [];
        $mustNotQuery = [];
        $shouldQuery = [];
        $facetQuery = [];
        $postFilterQuery = [];

        $boolQuery = [
            'query' => [],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];

        foreach ($params['must'] as $query) {
            $mustQueryParams = $query->getQueryParams();
            $mustClause = $mustQueryParams['query'];
            array_push($mustQuery, $mustClause);
        }

        foreach ($params['must_not'] as $query) {
            $mustNotQueryParams = $query->getQueryParams();
            $mustNotClause = $mustNotQueryParams['query'];
            array_push($mustNotQuery, $mustNotClause);
        }

        foreach ($params['should'] as $query) {
            $shouldQueryParams = $query->getQueryParams();
            $shouldClause = $mustNotQueryParams['query'];
            array_push($shouldQuery, $shouldClause);
        }

        foreach ($params['post_filter'] as $query) {
            $postFilterParams = $query->getQueryParams();
            $postFilterClause = $postFilterParams['query'];
            array_push($postFilterQuery, $postFilterClause);
        }

        foreach ($params['aggregations'] as $name => $agg) {
            $facet = $this->_generateFacetFromAggregation($name, $agg);
            array_push($facetQuery, $facet);
        }

        if ($shouldQuery) {
            $boolQuery['query']['bool']['should'] = $shouldQuery;
        }

        if ($mustQuery) {
            $boolQuery['query']['bool']['must'] = $mustQuery;
        }

        if ($mustNotQuery) {
            $boolQuery['query']['bool']['must_not'] = $mustNotQuery;
        }

        if ($postFilterQuery) {
            $boolQuery['filter'] = $postFilterQuery;
        }

        if ($facetQuery) {
            $boolQuery['facet'] = $facetQuery;
        }

        if ($sort) {
            $sortArray = [];
            foreach ($sort as $field => $direction) {
                array_push($sortArray, "{$field} {$direction}");
            }
            $sortString = implode(',', $sortArray);
            $boolQuery['sort'] = $sortString;
        }

        $this->query = $boolQuery;
    }

    public function setAggregations($agg)
    {
        return $this->addParam('aggregations', $agg);
    }

    public function setPostFilter($filter)
    {
        return $this->addParam('post_filter', $filter);
    }

    public function setSort($sort)
    {
        foreach ($sort as $field => $direction) {
            if ('asc' !== $direction && 'dsc' !== $direction) {
                throw new Exception('Invalid sort direction. Acceptable values are: asc, dsc');
            }
        }
        $this->setParam('sort', $sort);
    }

    public function addSort($sort)
    {
        $sortArray = $this->getSort();

        if (!$sortArray) {
            $this->setSort($sort);

            return;
        }

        array_push($sortArray, $sort);
        $this->setSort($sortArray);
    }

    public function getSort()
    {
        $params = $this->getParams();

        return $params['sort'];
    }

    /**
     * Add must for BoolQuery.
     *
     * @param mixed $args
     */
    public function addMust($args): self
    {
        return $this->_addQuery('must', $args);
    }

    /**
     * Add must not for BoolQuery.
     *
     * @param mixed $args
     */
    public function addMustNot($args): self
    {
        return $this->_addQuery('must_not', $args);
    }

    /**
     * Set minimum match (mm) query for BoolQuery.
     *
     * @param mixed $args
     */
    public function addShould($args): self
    {
        return $this->_addQuery('should', $args);
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
    }

    public function setFrom($offset)
    {
        return $this->setOffset($offset);
    }

    public function setType($type)
    {
        $params = $this->getParams();

        foreach ($params['must'] as $query) {
            $this->_setTypeForQuery($query, $type);
        }

        foreach ($params['must_not'] as $query) {
            $this->_setTypeForQuery($query, $type);
        }

        foreach ($params['should'] as $query) {
            $this->_setTypeForQuery($query, $type);
        }

        foreach ($params['post_filter'] as $query) {
            $this->_setTypeForQuery($query, $type);
        }

        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function removeMustWithTermField($field)
    {
        $params = $this->getParams();
        $mustParams = [];

        foreach ($params['must'] as $query) {
            if ($query instanceof arSolrTermQuery && $query->getTermField() === $field) {
                continue;
            }

            array_push($mustParams, $params['must']);
        }

        $this->setParams('must', $mustParams);
    }

    protected function _addQuery(string $type, $args): self
    {
        if (!\is_array($args) && !($args instanceof arSolrAbstractQuery)) {
            throw new Exception('Invalid parameter. Has to be array or instance of arSolrAbstractQuery');
        }

        return $this->addParam($type, $args);
    }

    private function _generateFacetFromAggregation($name, $agg)
    {
        $type = $this->getType();
        if (!$type) {
            throw new Exception("Field 'type' must be set if using aggregations.");
        }

        $facet = [
            $name => [
                'type' => $agg['type'],
            ],
        ];

        if (isset($agg['limit'])) {
            $facet[$name]['limit'] = $agg['limit'];
        }

        switch ($agg['type']) {
            case 'terms':
                $facet = [
                    $name => [
                        'field' => "{$type}.{$agg['field']}",
                    ],
                ];

                break;

            case 'query':
                foreach ($agg['field'] as $field => $value) {
                    $facet = [
                        $name => [
                            'q' => "{$type}.{$field}:{$value}",
                        ],
                    ];
                }

                break;
        }

        return $facet;
    }

    private function _setTypeForQuery($query, $type)
    {
        if (!($query instanceof arSolrAbstractQuery)) {
            throw new Exception('Invalid Query. Has to be array or instance of arSolrAbstractQuery');
        }

        // MatchAll queries have no type since they're run on all fields
        if ($query instanceof arSolrMatchAllQuery) {
            return;
        }

        // Only set the type for another arSolrBoolQuery or if it is not already set
        if (!$query->getType()) {
            $query->setType($type);
        }
    }
}
