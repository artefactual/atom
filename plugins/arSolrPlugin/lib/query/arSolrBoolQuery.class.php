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

        $mustQuery = [];
        $mustNotQuery = [];
        $shouldQuery = [];

        $boolQuery = [
            'query' => [],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];

        foreach ($params['must'] as $query) {
            $mustClause = [
                'edismax' => [
                    'query' => $query->getSearchQuery(),
                    'q.op' => $query->getDefaultOperator(),
                    'stopwords' => 'true',
                    'qf' => implode(' ', $query->getFields()),
                ],
            ];
            array_push($mustQuery, $mustClause);
        }

        foreach ($params['must_not'] as $query) {
            $mustNotClause = [
                'edismax' => [
                    'query' => $query->getSearchQuery(),
                    'q.op' => $query->getDefaultOperator(),
                    'stopwords' => 'true',
                    'qf' => implode(' ', $query->getFields()),
                ],
            ];
            array_push($mustNotQuery, $mustNotClause);
        }

        foreach ($params['should'] as $query) {
            $shouldClause = [
                'edismax' => [
                    'query' => $query->getSearchQuery(),
                    'q.op' => $query->getDefaultOperator(),
                    'stopwords' => 'true',
                    'qf' => implode(' ', $query->getFields()),
                ],
            ];
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

        $this->query = $boolQuery;
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

    /**
     * Sets the filter.
     *
     * @return $this
     */
    public function addFilter(arSolrAbstractQuery $filter): self
    {
        return $this->addParam('filter', $filter);
    }

    /**
     * Sets boost value of this query.
     *
     * @param float $boost Boost value
     */
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Sets the minimum number of should clauses to match.
     *
     * @param int|string $minimum Minimum value
     *
     * @return $this
     */
    public function setMinimumShouldMatch($minimum): self
    {
        return $this->setParam('minimum_should_match', $minimum);
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
    }

    protected function _addQuery(string $type, $args): self
    {
        if (!\is_array($args) && !($args instanceof arSolrQuery)) {
            throw new Exception('Invalid parameter. Has to be array or instance of arSolrAbstractQuery');
        }

        return $this->addParam($type, $args);
    }
}
