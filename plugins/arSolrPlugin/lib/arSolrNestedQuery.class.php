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
 * arSolrNestedQuery.
 */
class arSolrNestedQuery extends arSolrQuery
{
    public $query;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->query = $this->setRawQuery();
    }

    /**
     * Adds field to mlt query.
     *
     * @param string $path Nested object path
     *
     * @return $this
     */
    public function setPath(string $path): self
    {
        return $this->setParam('path', $path);
    }

    /**
     * Sets nested query.
     *
     * @return $this
     */
    public function setQuery($query): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Set score method.
     *
     * @param string $scoreMode options: avg, total, max and none
     *
     * @return $this
     */
    public function setScoreMode(string $scoreMode = 'avg'): self
    {
        return $this->setParam('score_mode', $scoreMode);
    }

    /**
     * 
     *
     * @param string $
     *
     * @return $this
     */
    public function addSort()
    {
        return;
    }

    /**
     * 
     *
     * @param string $
     *
     * @return $this
     */
    public function setSort()
    {
        return;
    }

    /**
     * 
     *
     * @param string $
     *
     * @return $this
     */
    public function setTerm()
    {
        return;
    }

    /**
     * 
     *
     * @param string $
     *
     * @return $this
     */
    public function setFilter()
    {
        return;
    }
}
