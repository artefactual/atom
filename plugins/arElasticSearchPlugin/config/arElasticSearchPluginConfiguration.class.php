<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class arElasticSearchPluginConfiguration extends sfPluginConfiguration
{
  public static
    $summary = 'Search index plugin. Uses an ElasticSearch instance to provide advanced search features such as faceting, fuzzy search, etc.',
    $version = '1.0.0',

    // this is the name of the index; must be unique within the server
    $index = 'atom',

    // default number of documents to include in a batch (bulk) request
    $batchSize = 500,

    // server defaults to localhost:9200 if omitted
    // can also be used to configure a cluster of ElasticSearch nodes
    // see more info at: http://ruflin.github.com/Elastica/
    $server = array(
      'host' => 'localhost',
      'port' => '9200',
      // This will write the JSON request in the file given
      // Very useful when the Elastica API behavior is unclear
      // 'log' => '/tmp/elastica.log'
    );

  public function initialize()
  {
    $enabledModules = sfConfig::get('sf_enabled_modules');
    $enabledModules[] = 'arElasticSearchPlugin';
    sfConfig::set('sf_enabled_modules', $enabledModules);
  }
}
