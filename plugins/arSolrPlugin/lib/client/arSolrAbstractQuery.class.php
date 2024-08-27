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

class arSolrAbstractQuery
{
    /**
     * Number of results to fetch.
     *
     * @var number defaults to 10
     */
    protected $size = 10;

    /**
     * Offset for search results.
     *
     * @var number defaults to 0
     */
    protected $offset = 0;

    /**
     * Sets (overwrites) the value at the given key.
     *
     * @param string $key   Key to set
     * @param mixed  $value Key Value
     *
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Adds a single param or an array of params to the list.
     *
     * @param string $key   Param key
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    public function addParam($key, $value, ?string $subKey = null)
    {
        if (null !== $subKey) {
            $this->params[$key][$subKey] = $value;
        } else {
            $this->params[$key][] = $value;
        }

        return $this;
    }

    /**
     * Sets (overwrites) all params of this object.
     *
     * @param array $params Parameter list
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Returns the params array.
     *
     * @return array Params
     */
    public function getParams()
    {
        return $this->params;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
}
