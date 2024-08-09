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
 * arSolrQuery.
 */
class arSolrQuery extends arSolrAbstractQuery
{
    /**
     * Query Params.
     *
     * @var mixed
     */
    protected $query;

    /**
     * Array of fields to be queried.
     *
     * @var array
     */
    protected $fields;

    /**
     * Default operator.
     *
     * @var string defaults to 'AND'
     */
    protected ?string $operator = 'AND';

    /**
     * Search query.
     *
     * @var string
     */
    protected ?string $searchQuery;

    /**
     * Aggregations.
     */
    protected array $aggregations = [];

    /**
     * Field type.
     *
     * @var string
     */
    protected ?string $type = null;

    /**
     * Constructor.
     *
     * @param mixed $searchQuery
     */
    public function __construct($searchQuery)
    {
        $this->setSearchQuery($searchQuery);
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getDefaultOperator()
    {
        return $this->operator;
    }

    public function setDefaultOperator($operator)
    {
        if ('AND' !== $operator && 'OR' !== $operator) {
            throw new Exception('Invalid operator. AND and OR are the only acceptable operator types.');
        }

        $this->operator = $operator;
    }

    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    public function getQueryParams()
    {
        $this->generateQueryParams();

        return $this->query;
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function generateQueryParams()
    {
        $fields = $this->getFields();
        if (!isset($fields)) {
            throw new Exception('Fields not set.');
        }

        $type = $this->getType();
        if (!isset($type)) {
            throw new Exception("Field 'type' is not set.");
        }

        $typedFields = [];
        foreach ($fields as $field) {
            array_push($typedFields, "{$type}.{$field}");
        }

        if ($this->aggregations) {
            $aggregationField = "{$type}.{$this->aggregations['field']}";
            $this->query = [
                'query' => [
                    'edismax' => [
                        'q.op' => $this->operator,
                        'stopwords' => 'true',
                        'query' => "{$this->searchQuery}~",
                        'qf' => implode(' ', $typedFields),
                    ],
                ],
                'facet' => [
                    'categories' => [
                        'type' => 'terms',
                        'field' => $aggregationField,
                        'limit' => $this->aggregations['size'],
                    ],
                ],
                'offset' => $this->offset,
                'limit' => $this->size,
            ];
        } else {
            $this->query = [
                'query' => [
                    'edismax' => [
                        'q.op' => $this->operator,
                        'stopwords' => 'true',
                        'query' => "{$this->searchQuery}~",
                        'qf' => implode(' ', $typedFields),
                    ],
                ],
                'offset' => $this->offset,
                'limit' => $this->size,
            ];
        }
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
}
