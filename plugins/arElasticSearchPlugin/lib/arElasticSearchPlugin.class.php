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
 * arElasticSearchPlugin main class
 *
 * @package     AccesstoMemory
 * @subpackage  arElasticSearchPlugin
 * @author      MJ Suhonos <mj@suhonos.ca>
 * @author      Jesús García Crepso <jesus@sevein.com>
 */
class arElasticSearchPlugin extends QubitSearchEngine
{
  /**
   * Elastic_Client object
   *
   * @var mixed Defaults to null.
   */
  protected $client = null;

  /**
   * Elastic_Index object
   *
   * @var mixed Defaults to null.
   */
  protected $index = null;

  /**
   * elasticsearch bulk API makes it possible to perform many index/delete
   * operations in a single call. This can greatly increase the indexing speed.
   * This array will be used to store documents in batches.
   *
   * @var array
   */
  private $batchDocs = array();

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->config = arElasticSearchPluginConfiguration::$config;
    $this->mapping = arElasticSearchPluginConfiguration::$mapping;

    $this->client = new Elastica_Client($this->config['server']);
    $this->index = $this->client->getIndex($this->config['index']['name']);

    $this->initialize();
  }

  public function __destruct()
  {
    // If there are still documents in the batch queue, send them
    if ($this->config['batch_mode'] && count($this->batchDocs) > 0)
    {
      $this->index->addDocuments($this->batchDocs);

      // I don't think that this is necessary
      // $this->index->flush();
    }

    $this->index->refresh();
  }

  /**
   * Initialize ES index if it does not exist
   */
  protected function initialize()
  {
    try
    {
      // Uncomment if you want to delete the index and create it again
      $this->index->delete();

      $this->index->open();
    }
    catch (Exception $e)
    {
      // If the index has not been initialized, create it
      if ($e instanceof Elastica_Exception_Response)
      {
        $this->index->create($this->config['index']['configuration'], true);
      }

      // Iterate over types (actor, information_object, ...)
      foreach ($this->mapping['mapping'] as $typeName => $typeProperties)
      {
        // Look for special attributes like i18n or timestamp and update the
        // mapping accordingly. For example, 'timestamp' adds the created_at
        // and updated_at fields each time is used.
        if (isset($typeProperties['_attributes']))
        {
          foreach ($typeProperties['_attributes'] as $attributeName => $attributeValue)
          {
            switch ($attributeName)
            {
              case 'i18n':
                $typeProperties['source_culture'] = array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false);
                $typeProperties['i18n'] = array(
                  'type' => 'object',
                  'include_in_root' => true,
                  'properties' => array(
                    'culture' => array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false)));

                break;

              case 'timestamp':
                $typeProperties['created_at'] = array('type' => 'date');
                $typeProperties['updated_at'] = array('type' => 'date');

                break;
            }
          }

          unset($typeProperties['_attributes']);
        }

        // Define mapping in elasticsearch
        $mapping = new Elastica_Type_Mapping();
        $mapping->setType($this->index->getType($typeName));
        $mapping->setProperties($typeProperties);
        $mapping->send();
      }
    }
  }

  /**
   * Optimize ES index
   */
  public function optimize($args = array())
  {
    return $this->client->optimizeAll($args);
  }

  public function populate()
  {
    $timer = new QubitTimer;

    // Delete index and initialize again
    $this->index->delete();
    $this->initialize();
    $this->log('Index erased');

    // Populate
    $this->log('Populating index...');

    // TODO

    $this->log(sprintf('Index populated with %s documents in %s seconds',
      array(
        $total,
        $timer->elapsed())));
  }
}
