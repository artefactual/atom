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
    protected array $query = [];

    /**
     * Query Term Field.
     *
     * @var string
     */
    protected ?string $field = null;

    /**
     * Query Term Value.
     *
     * @var string
     */
    protected ?string $termValue = null;

    /**
     * Field type
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
            foreach ($term as $field => $value) {
                $this->setTerm($field, $value);
            }
        }
    }

    public function setTerm($field, $value)
    {
        $this->termField = $field;
        $this->termValue = $value;
    }

    public function getTermValue()
    {
        return $this->termValue;
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

        $termValue = $this->getTermValue();
        if (!isset($termValue)) {
            throw new Exception('Term value is not set.');
        }

        $type = $this->getType();
        if (!isset($type)) {
            throw new Exception("Field 'type' is not set.");
        }

        $this->query = [
            'query' => [
                'edismax' => [
                    'query' => "{$type}.{$termField}:{$termValue}",
                ],
            ],
            'offset' => $this->offset,
            'limit' => $this->size,
        ];
    }
}
