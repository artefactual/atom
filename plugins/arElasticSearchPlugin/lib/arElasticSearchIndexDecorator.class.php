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

use Elastica\Query;
use Elastica\Response;
use Elastica\Script\AbstractScript;
use Elasticsearch\Endpoints\UpdateByQuery;

class arElasticSearchIndexDecorator
{
    protected $_instance;

    protected $_indexPrefix;

    public function __construct($prefix)
    {
        $this->_instance = [];

        $this->_indexPrefix = $prefix;
    }

    public function createIndex($typeName, Elastica\Index $index) {
        $typeName = $this->getIndexTypeName($typeName);
        $this->_instance[$typeName] = $index;
    }

    // Converts camelized Qubit class names to lower case index name used for ElasticSearch
    public function getIndexTypeName($typeName) {
        return $this->_indexPrefix . '_' . strtolower($typeName);
    }

    public function delete() {
        foreach ($this->_instance as $index) {
            $index->delete();
        }
    }

    public function addDocuments($typeName, $documents) {
        $typeName = $this->getIndexTypeName($typeName);
        $this->_instance[$typeName]->addDocuments($documents);
    }

    public function deleteDocuments($typeName, $documents) {
        $typeName = $this->getIndexTypeName($typeName);
        $this->_instance[$typeName]->deleteDocuments($documents);
    }

    public function refresh() {
        foreach ($this->_instance as $index) {
            $index->refresh();
        }
    }

    public function getType($typeName) {
        $typeName = $this->getIndexTypeName($typeName);

        return $this->_instance[$typeName];
    }

    public function getInstance($typeName)
    {
        return $this->getType($typeName);
    }

    public function getIndices() {
        return $this->_instance;
    }
}
