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
 * arElasticSearchMultiIndexWrapper facilitates handling ElasticSearch indices so that
 * all indices for an AtoM installation can share a common prefix without having to
 * explicitly specify it in common indexing and search functions.
 */
class arElasticSearchMultiIndexWrapper
{
    protected $indices;

    protected $indexPrefix;

    public function __construct($prefix)
    {
        $this->indices = [];

        $this->indexPrefix = $prefix;
    }

    public function addIndex($name, Elastica\Index $index)
    {
        $name = $this->getIndexName($name);
        $this->indices[$name] = $index;
    }

    // Converts camelized Qubit class names to lower case index name used for ElasticSearch
    public function getIndexName($name)
    {
        return $this->indexPrefix.'_'.strtolower($name);
    }

    public function delete()
    {
        foreach ($this->indices as $index) {
            $index->delete();
        }
    }

    public function addDocuments($name, $documents)
    {
        $name = $this->getIndexName($name);
        $this->indices[$name]->addDocuments($documents);
    }

    public function deleteDocuments($name, $documents)
    {
        $name = $this->getIndexName($name);
        $this->indices[$name]->deleteDocuments($documents);
    }

    public function refresh()
    {
        foreach ($this->indices as $index) {
            $index->refresh();
        }
    }

    // Return the index element from the array of indices
    // that matches the qualified index name
    public function getIndex($name)
    {
        $name = $this->getIndexName($name);

        return $this->indices[$name];
    }

    // Alias for getIndex. Can be safely removed once
    // calls to getIndex that are external to the plugin have
    // been removed or refactored.
    public function getType($name)
    {
        return $this->getIndex($name);
    }

    public function getIndices()
    {
        return $this->indices;
    }
}
