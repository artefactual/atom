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
     * Assemble BoolQuery.
     *
     * @return arSolrBoolQuery object
     */
    public function generateQueryParams()
    {
        $params = $this->getParams();
        $aggregations = $this->getAggregations();
        $sort = $this->getSort();

        $mustQuery = [];
        $mustNotQuery = [];
        $shouldQuery = [];

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

        if ($shouldQuery) {
            $boolQuery['query']['bool']['should'] = $shouldQuery;
        }

        if ($mustQuery) {
            $boolQuery['query']['bool']['must'] = $mustQuery;
        }

        if ($mustNotQuery) {
            $boolQuery['query']['bool']['must_not'] = $mustNotQuery;
        }

        if ($aggregations) {
            $boolQuery['facet'] = $aggregations;
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

    public function getAggregations()
    {
        $params = $this->getParams();

        return $params['aggregations'];
    }

    public function setAggregations($agg)
    {
        return $this->addParam('aggregations', $agg);
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

    protected function _addQuery(string $type, $args): self
    {
        if (!\is_array($args) && !($args instanceof arSolrAbstractQuery)) {
            throw new Exception('Invalid parameter. Has to be array or instance of arSolrAbstractQuery');
        }

        return $this->addParam($type, $args);
    }
}
