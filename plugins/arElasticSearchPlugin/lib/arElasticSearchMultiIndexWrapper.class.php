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
    protected $_instance;

    protected $_indexPrefix;

    public function __construct($prefix)
    {
        $this->_instance = [];

        $this->_indexPrefix = $prefix;
    }

    public function createIndex($name, Elastica\Index $index)
    {
        $name = $this->getIndexName($name);
        $this->_instance[$name] = $index;
    }

    // Converts camelized Qubit class names to lower case index name used for ElasticSearch
    public function getIndexName($name)
    {
        return $this->_indexPrefix.'_'.strtolower($name);
    }

    public function delete()
    {
        foreach ($this->_instance as $index) {
            $index->delete();
        }
    }

    public function addDocuments($name, $documents)
    {
        $name = $this->getIndexName($name);
        $this->_instance[$name]->addDocuments($documents);
    }

    public function deleteDocuments($name, $documents)
    {
        $name = $this->getIndexName($name);
        $this->_instance[$name]->deleteDocuments($documents);
    }

    public function refresh()
    {
        foreach ($this->_instance as $index) {
            $index->refresh();
        }
    }

    public function getType($name)
    {
        $name = $this->getIndexName($name);

        return $this->_instance[$name];
    }

    public function getInstance()
    {
        return $this->_instance;
    }
}
