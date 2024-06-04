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
 * arSolrQueryClass.
 */
class arSolrQueryClass extends arSolrQuery
{
    /**
     * Creates a query object.
     *
     * @param AbstractQuery|array|Collapse|Suggest $query Query object (default = null)
     *
     * @phpstan-param AbstractQuery|Suggest|Collapse|TRawQuery $query
     */
    public function __construct($query = null)
    {
        if (\is_array($query)) {
            $this->setRawQuery($query);
        } else {
            $this->setQuery($query);
        }
    }

    /**
     * Adds a sort param to the query.
     *
     * @param mixed $sort Sort parameter
     *
     * @phpstan-param TSortArg $sort
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html
     */
    public function addSort($sort): self
    {
        return $this->addParam('sort', $sort);
    }

    /**
     * Sets sort arguments for the query
     * Replaces existing values.
     *
     * @param array $sortArgs Sorting arguments
     *
     * @phpstan-param TSortArgs $sortArgs
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-sort.html
     */
    public function setSort(array $sortArgs): self
    {
        return $this->setParam('sort', $sortArgs);
    }

}
