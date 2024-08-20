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

class arSolrTermsQuery extends arSolrAbstractQuery
{
    /**
     * Query Params.
     *
     * @var mixed
     */
    protected array $query = [];

    /**
     * Query Term Field.
     *
     * @var string
     */
    protected ?string $termField = null;

    /**
     * Query Term Value.
     *
     * @var array
     */
    protected ?array $termValues = null;

    /**
     * Field type.
     *
     * @var string
     */
    protected ?string $type = null;

    /**
     * Constructor.
     *
     * @param mixed      $searchQuery
     * @param null|mixed $term
     */
    public function __construct($term = null)
    {
        if ($term) {
            foreach ($term as $field => $values) {
                $this->setTerms($field, $values);
            }
        }
    }

    public function setTerms($field, $values)
    {
        $this->termField = $field;
        $this->termValues = $values;
    }

    public function getTermValues()
    {
        return $this->termValues;
    }

    public function getTermField()
    {
        return $this->termField;
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
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

    protected function generateQueryParams()
    {
        $termField = $this->getTermField();
        if (!isset($termField)) {
            throw new Exception('Term field is not set.');
        }

        $termValues = $this->getTermValues();
        if (!isset($termValues)) {
            throw new Exception('Term values are not set.');
        }

        $type = $this->getType();
        if (!isset($type)) {
            throw new Exception("Field 'type' is not set.");
        }

        $queryString = implode(' OR ', $termValues);
        $this->query = [
            'query' => [
                'edismax' => [
                    'query' => "{$type}.{$termField}:({$queryString})",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }
}
