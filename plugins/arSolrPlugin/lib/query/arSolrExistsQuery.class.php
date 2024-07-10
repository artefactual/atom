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

class arSolrExistsQuery extends arSolrAbstractQuery
{
    /**
     * Query Params.
     *
     * @var mixed
     */
    protected $query;

    /**
     * Field to be queried.
     *
     * @var string
     */
    protected $field;

    /**
     * Constructor.
     *
     * @param string $field
     */
    public function __construct($field)
    {
        $this->setField($field);
        $this->generateQueryParams();
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
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
                'lucene' => [
                    'query' => "{$this->field}:*",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }

    public function setType($type)
    {
        $this->setField("{$type}.{$this->field}");
    }
}
