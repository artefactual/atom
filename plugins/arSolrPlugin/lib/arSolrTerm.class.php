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
 * arSolrTerm.
 */
class arSolrTerm extends arSolrQuery
{
    /**
     * Calls setTerm with the given $term array
     * 
     * @param array $term
     */
    public function __construct(array $term = [])
    {
        $this->setRawTerm($term);
    }

    /**
     * Set term can be used instead of addTerm to set more special
     * values for a term.
     *
     * @param array $term Term array
     *
     * @return $this
     */
    public function setRawTerm(array $term): self
    {
        return $this->setParams($term);
    }

    /**
     * Adds a term to the term query.
     *
     * @param string                $key   Key to query
     * @param bool|float|int|string $value Values(s) for the query
     * @param float                 $boost OPTIONAL Boost value (default = 1.0)
     *
     * @return $this
     */
    public function setTerm(string $key, $value, float $boost = 1.0): self
    {
        return $this->setRawTerm([$key => ['value' => $value, 'boost' => $boost]]);
    }
}
