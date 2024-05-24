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
 * arSolrBoolQuery.
 */
class arSolrBoolQuery extends arSolrQuery
{
    /**
     * Add should part to query.
     *
     * @param array $args Should query
     *
     * @return $this
     */
    public function addShould($args): self
    {
        return $this->_addQuery('should', $args);
    }

    /**
     * Add must part to query.
     *
     * @param array $args Must query
     *
     * @return $this
     */
    public function addMust($args): self
    {
        return $this->_addQuery('must', $args);
    }

    /**
     * Add must not part to query.
     *
     * @param array $args Must not query
     *
     * @return $this
     */
    public function addMustNot($args): self
    {
        return $this->_addQuery('must_not', $args);
    }

    /**
     * Adds a query to the current object.
     *
     * @param string $type Query type
     * @param array  $args Query
     *
     * @throws Exception If not valid query
     *
     * @return $this
     */
    protected function _addQuery(string $type, $args): self
    {
        if (!is_array($args)) {
            throw new Exception('Invalid parameter. Has to be array.');
        }

        return $this->addParam($type, $args);
    }
}
