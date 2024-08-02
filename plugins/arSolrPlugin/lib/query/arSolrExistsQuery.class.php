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
     */
    protected array $query = [];

    /**
     * Field to be queried.
     */
    protected ?string $field = null;

    /**
     * Type of query.
     */
    protected ?string $type = null;

    /**
     * Constructor.
     *
     * @param string $field
     */
    public function __construct($field)
    {
        $this->setField($field);
    }

    public function setField($field)
    {
        if (empty($field)) {
            return;
        }

        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setType($type)
    {
        if (empty($type)) {
            return;
        }

        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
    }

    protected function generateQueryParams()
    {
        $field = $this->getField();
        if (!isset($field)) {
            throw new Exception('Field is not set.');
        }

        $type = $this->getType();
        if (!isset($type)) {
            throw new Exception("Field 'type' is not set.");
        }

        $this->query = [
            'query' => [
                'lucene' => [
                    'query' => "{$type}.{$field}:*",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }
}
