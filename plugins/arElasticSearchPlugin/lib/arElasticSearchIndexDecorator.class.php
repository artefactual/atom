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

/**
 * arElasticSearchIndexDecorator can be removed when Elastica >= 6.x. It is present to
 * provide Elastica/Index::updateByQuery().
 *
 * @author      sbreker <sbreker@artefactual.com>
 */
class arElasticSearchIndexDecorator
{
    protected $_instance;

    public function __construct(Elastica\Index $instance)
    {
        $this->_instance = $instance;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->_instance, $method], $args);
    }

    public function __get($key)
    {
        return $this->_instance->{$key};
    }

    public function __set($key, $val)
    {
        return $this->_instance->{$key} = $val;
    }

    public function getInstance()
    {
        return $this->_instance;
    }

    /**
     * Update entries in the db based on a query.
     *
     * @param AbstractQuery|array|Collapse|Query|string|Suggest $query   Query object or array
     * @param AbstractScript                                    $script  Script
     * @param array                                             $options Optional params
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update-by-query.html
     */
    public function updateByQuery($query, AbstractScript $script, array $options = []): Response
    {
        $endpoint = new UpdateByQuery();
        $q = Query::create($query)->getQuery();
        $body = [
            'query' => \is_array($q) ? $q : $q->toArray(),
            'script' => $script->toArray()['script'],
        ];

        $endpoint->setBody($body);
        $endpoint->setParams($options);

        return $this->requestEndpoint($endpoint);
    }
}
