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
 * arElasticSearchMultiIndexWrapper facilitates handling ElasticSearch indices
 * and has methods that match signatures of pre ES 6.x methods that used a
 * single index with multiple types instead of multiple index with a single
 * type or no type.
 * This class has an indices property which is an array of ElasticSearch
 * indices in order to keep arElasticSearchPlugin's index property backwards
 * compatible with custom themes.
 */
class arElasticSearchMultiIndexWrapper
{
    protected $indices;

    public function __construct()
    {
        $this->indices = [];
    }

    public function addIndex($name, Elastica\Index $index)
    {
        $this->indices[$name] = $index;
    }

    public function addDocuments($name, $documents)
    {
        $this->indices[$name]->addDocuments($documents);
    }

    public function deleteDocuments($name, $documents)
    {
        $this->indices[$name]->deleteDocuments($documents);
    }

    public function updateDocuments($name, $documents)
    {
        $this->indices[$name]->updateDocuments($documents);
    }

    public function deleteById($name, $documentId)
    {
        $this->indices[$name]->deleteById($documentId);
    }

    /**
     * Refresh ElasticSearch indices. If an index name is provided,
     * only that specific index will be refreshed.
     *
     * @param string $name Index name to be refreshed (optional)
     */
    public function refresh($name)
    {
        $this->indices[$name]->refresh();
    }

    // Return the index element from the array of indices
    // that matches the qualified index name
    public function getIndex($name)
    {
        return $this->indices[$name];
    }

    // Alias for getIndex. Can be safely removed once
    // calls to getIndex that are external to the plugin have
    // been removed or refactored.
    public function getType($name)
    {
        return $this->getIndex($name);
    }
}
