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

class arSolrTermQuery extends arSolrAbstractQuery
{
    /**
     * Query Params.
     *
     * @var mixed
     */
    protected $query;

    /**
     * Query Term Field.
     *
     * @var string
     */
    protected $termField = '';

    /**
     * Query Term Value.
     *
     * @var string
     */
    protected $termValue = '';

    /**
     * Params.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Constructor.
     *
     * @param mixed      $searchQuery
     * @param null|mixed $term
     */
    public function __construct($term = null)
    {
        foreach ($term as $field => $value) {
            $this->setTerm($field, $value);
        }
        $this->generateQueryParams();
    }

    public function setTerm($field, $value)
    {
        $this->termField = $field;
        $this->termValue = $value;
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
                'edismax' => [
                    'query' => "{$this->termField}:{$this->termValue}",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }

    public function setType($type)
    {
        $this->termField = "{$type}.{$this->termField}";
    }
}
