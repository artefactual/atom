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
   * Mappings configuration, mapping.yml
   *
   * @var mixed Defaults to null.
   */
  protected $mappings = null;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->config = arElasticSearchPluginConfiguration::$config;
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
      // $this->index->delete();

      $this->index->open();
    }
    catch (Exception $e)
    {
      // If the index has not been initialized, create it
      if ($e instanceof Elastica_Exception_Response)
      {
        $this->index->create($this->config['index']['configuration'], true);
      }

      // Load mappings
      $this->loadMappings();

      // Iterate over types (actor, information_object, ...)
      foreach ($this->mappings as $typeName => $typeProperties)
      {
        // Define mapping in elasticsearch
        $mapping = new Elastica_Type_Mapping();
        $mapping->setType($this->index->getType($typeName));
        $mapping->setProperties($typeProperties);
        $mapping->send();
      }
    }
  }

  protected function loadMappings()
  {
    // Avoid reload
    if (null !== $this->mappings)
    {
      return $this->mappings;
    }

    // Find mapping.yml
    $finder = sfFinder::type('file')->name('mapping.yml');
    $files = array_unique(array_merge(
      $finder->in(sfConfig::get('sf_config_dir')),
      $finder->in(ProjectConfiguration::getActive()->getPluginSubPaths('/config'))));

    if (!count($files))
    {
      throw new sfException('You must create a mapping.xml file.');
    }

    // Load first mapping.yml file found
    $esMapping = new arElasticSearchMapping();
    $esMapping->loadYAML(array_shift($files));

    $this->mappings = $esMapping->asArray();
  }

  /**
   * Optimize index
   */
  public function optimize($args = array())
  {
    return $this->client->optimizeAll($args);
  }

  /**
   * Populate index
   */
  public function populate()
  {
    // Delete index and initialize again
    $this->index->delete();
    $this->initialize();
    $this->log('Index erased');

    $this->log('Populating index...');

    // Document counter and timer
    $total = 0;
    $timer = new QubitTimer;

    $this->loadMappings();

    foreach ($this->mappings as $typeName => $typeProperties)
    {
      $className = 'arElasticSearch'.sfInflector::camelize($typeName);
      $class = new $className;

      $class->populate();
    }

    $this->log(vsprintf('Index populated with %s documents in %s seconds',
      array(
        $total,
        $timer->elapsed())));
  }
}
