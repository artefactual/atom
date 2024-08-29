<?php

require 'plugins/arSolrPlugin/lib/client/arSolrHelper.php';

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
 * arSolrClient main class.
 */
class arSolrClient
{
    private array $config;

    /**
     * Constructor.
     */
    public function __construct(array $config = [])
    {
        if (!$config['host']) {
            throw new Exception('Solr config must contain host.');
        }

        if (!$config['port']) {
            throw new Exception('Solr config must contain port.');
        }

        if (!$config['collection']) {
            throw new Exception('Solr config must contain collection.');
        }

        $this->config = $config;
        $this->config['path'] = '/solr/'.$this->config['collection'];
        $this->config['api_url'] = 'http://'.$this->config['host'].':'.$this->config['port'];
    }

    public function flushIndex()
    {
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/update/";
        $query = '{"delete": {"query": "*:*"}}';

        return makeHttpRequest($url, 'POST', $query);
    }

    public function search($query)
    {
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/query";
        $response = makeHttpRequest($url, 'POST', json_encode($query->getQueryParams()));

        return new arSolrResultSet($response);
    }

    public function addDocument($document)
    {
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/update/json/docs";

        return makeHttpRequest($url, 'POST', json_encode($document));
    }

    public function addDocuments($documents)
    {
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/update/json/docs";

        return makeHttpRequest($url, 'POST', json_encode($documents));
    }

    public function deleteDocuments($documents)
    {
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/update";

        return makeHttpRequest($url, 'POST', json_encode([
            'delete' => $documents,
        ]));
    }

    public function deleteById($id, $type)
    {
        $document = $this->createDocumentWithId($id, $type);

        return $this->deleteDocuments($document);
    }

    public function deleteByQuery($query)
    {
        $queryParams = $query->getQueryParams();

        // Ignore offset, size, and additional params when deleting by query
        return $this->deleteDocuments([
            'query' => $queryParams['query'],
        ]);
    }

    public function createDocumentWithId($id, $type)
    {
        return ["{$type}.id" => $id];
    }

    public function getCollections()
    {
        $url = "{$this->config['api_url']}/solr/admin/collections?action=LIST";
        $response = makeHttpRequest($url);

        return $response->collections;
    }

    public function checkCollectionExists()
    {
        return array_search($this->config['collection'], $this->getCollections());
    }

    public function createCollection($numShards = 2, $replicationFactor = 1)
    {
        $url = "{$this->config['api_url']}/solr/admin/collections?action=CREATE&name={$this->config['collection']}&numShards={$numShards}&replicationFactor={$replicationFactor}&wt=json";

        return makeHttpRequest($url);
    }

    public function modifyConfigParams($paramName, $configParams)
    {
        $url = "{$this->config['api_url']}/api/collections/{$this->config['collection']}/config/";
        $jsonReqeustHandler = json_encode([
            $paramName => $configParams,
        ]);

        return makeHttpRequest($url, 'POST', $jsonReqeustHandler);
    }

    public function addFields($fields)
    {
        $addQuery = ['add-field' => $fields];
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/schema/";

        return makeHttpRequest($url, 'POST', json_encode($addQuery));
    }

    public function addCopyFields($fields)
    {
        $addQuery = ['add-copy-field' => $fields];
        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/schema/";

        return makeHttpRequest($url, 'POST', json_encode($addQuery));
    }

    public function replaceFieldType($fieldParams)
    {
        $replaceFieldQuery = ['replace-field-type' => $fieldParams];

        $url = "{$this->config['api_url']}/solr/{$this->config['collection']}/schema/";

        return makeHttpRequest($url, 'POST', json_encode($replaceFieldQuery));
    }
}
