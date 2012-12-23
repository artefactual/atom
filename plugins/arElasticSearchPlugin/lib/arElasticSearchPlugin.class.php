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
  public $index = null;

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
        $typeName = 'Qubit'.sfInflector::camelize($typeName);

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

  // ---------------------------------------------------------------------------

  /**
   * Centralize document addition to keep control of the batch queue
   * It may be better to extend Elastica?
   */
  public function addDocument($data, $type)
  {
    $document = new Elastica_Document($data['id'], $data);
    $document->setType($type);

    if ($this->batchMode)
    {
      // Add this document to the batch queue
      $this->batchDocs[] = $document;

      // If we have a full batch, send in bulk
      if (count($this->batchDocs) >= $this->batchSize)
      {
        $this->index->addDocuments($this->batchDocs);
        $this->index->flush();

        $this->batchDocs = array();
      }
    }
    else
    {
      $this->index->getType($type)->addDocument($document);
      $this->index->flush();
    }
  }

  /**
   * Function helper to parse query strings
   */
  public function parse(string $query)
  {
    if (empty($query))
    {
      throw new Exception('No search terms specified.');
    }

    $query = new Elastica_Query_QueryString($query);
    $query->setDefaultOperator('AND');

    return $query;
  }

  // ---------------------------------------------------------------------------

  public function delete($object)
  {
    $this->index->getType(get_class($object))->deleteById($object->id);
  }

  public function updateAccession(QubitAccession $object)
  {
    return arElasticSearchAccession::update($object);
  }

  public function updateActor(QubitActor $object)
  {
    return arElasticSearchActor::update($object);
  }

  public function updateContactInformation(QubitContactInformation $object)
  {
    return arElasticSearchContactInformation::update($object);
  }

  public function updateInformationObject(QubitInformationObject $object)
  {
    return arElasticSearchInformationObject::update($object);
  }

  public function updateTerm(QubitTerm $term)
  {
    return arElasticSearchTerm::update($object);
  }
}
