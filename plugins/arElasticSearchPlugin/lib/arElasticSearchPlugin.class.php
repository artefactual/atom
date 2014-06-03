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
 * @author      Jesús García Crespo <jesus@sevein.com>
 */
class arElasticSearchPlugin extends QubitSearchEngine
{
  /**
   * Elastic_Client object
   *
   * @var mixed Defaults to null.
   */
  public $client = null;

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
   * If false, this plugin will perform a trial run with no changes made
   *
   * @var mixed Defaults to true.
   */
  protected $enabled = true;

  /**
   * Constructor
   */
  public function __construct(array $options = array())
  {
    parent::__construct();

    $this->config = arElasticSearchPluginConfiguration::$config;
    $this->client = new \Elastica\Client($this->config['server']);
    $this->index = $this->client->getIndex($this->config['index']['name']);

    // Load batch mode configuration
    $this->batchMode = true === $this->config['batch_mode'];
    $this->batchSize = $this->config['batch_size'];

    if (isset($options['initialize']) && $options['initialize'] === false)
    {
      return;
    }

    $this->initialize();
  }

  public function __destruct()
  {
    if (!$this->enabled)
    {
      return;
    }

    $this->flushBatch();
    $this->index->refresh();
  }

  /**
   * Initialize ES index if it does not exist
   */
  protected function initialize()
  {
    try
    {
      $this->index->open();
    }
    catch (Exception $e)
    {
      // If the index has not been initialized, create it
      if ($e instanceof \Elastica\Exception\ResponseException)
      {
        $this->index->create($this->config['index']['configuration'],
          array('recreate' => true));
      }

      // Load mappings
      if (null === $this->mappings)
      {
        $this->mappings = self::loadMappings();
      }

      // Iterate over types (actor, information_object, ...)
      foreach ($this->mappings as $typeName => $typeProperties)
      {
        $typeName = 'Qubit'.sfInflector::camelize($typeName);

        // Define mapping in elasticsearch
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($this->index->getType($typeName));
        $mapping->setProperties($typeProperties['properties']);

        // Parse other parameters
        unset($typeProperties['properties']);
        foreach ($typeProperties as $key => $value)
        {
          $mapping->setParam($key, $value);
        }

        $this->log(sprintf('Defining mapping %s...', $typeName));
        $mapping->send();
      }
    }
  }

  public static function loadMappings()
  {
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
    $esMapping = new arElasticSearchMapping;
    $esMapping->loadYAML(array_shift($files));

    return $esMapping->asArray();
  }

  /**
   * Optimize index
   */
  public function optimize($args = array())
  {
    return $this->client->optimizeAll($args);
  }

  public function flush()
  {
    try
    {
      $this->index->delete();
    }
    catch (Exception $e)
    {

    }

    $this->initialize();
  }

  /**
   * Populate index
   */
  public function populate()
  {
    // Delete index and initialize again
    $this->flush();
    $this->log('Index erased.');

    $this->log('Populating index...');

    // Document counter and timer
    $total = 0;
    $timer = new QubitTimer;

    foreach ($this->mappings as $typeName => $typeProperties)
    {
      $className = 'arElasticSearch'.sfInflector::camelize($typeName);

      $class = new $className;
      $class->setTimer($timer);
      $class->populate();

      $total += $class->getCount();
    }

    $this->log(vsprintf('Index populated with %s documents in %s seconds.',
      array(
        $total,
        $timer->elapsed())));
  }

  /**
   * Populate index
   */
  public function enable()
  {
    $this->enabled = true;
  }

  public function disable()
  {
    $this->enabled = false;
  }

  // ---------------------------------------------------------------------------

  /**
   * Adds any batch documents to the index and flushes the batch.
   */
  public function flushBatch()
  {
    // If there are still documents in the batch queue, send them
    if ($this->config['batch_mode'] && count($this->batchDocs) > 0)
    {
      $this->index->addDocuments($this->batchDocs);
      $this->index->flush();
      $this->batchDocs = array();
    }
  }

  /**
   * Centralize document addition to keep control of the batch queue
   */
  public function addDocument($data, $type)
  {
    if (!isset($data['id']))
    {
      throw new sfException('Failed to parse id field.');
    }

    // Pass the id value to the \Elastica\Document constructor instead of as
    // part of the document body. ES _id field id
    $id = $data['id'];
    unset($data['id']);

    $document = new \Elastica\Document($id, $data);
    $document->setType($type);

    if ($this->batchMode)
    {
      // Add this document to the batch queue
      $this->batchDocs[] = $document;

      // If we have a full batch, send in bulk
      if (count($this->batchDocs) >= $this->batchSize)
      {
        $this->flushBatch();
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

    $query = new \Elastica\Query\QueryString($query);
    $query->setDefaultOperator('AND');

    return $query;
  }

  // ---------------------------------------------------------------------------

  public function delete($object)
  {
    if (!$this->enabled)
    {
      return;
    }

    if ($object instanceof QubitUser)
    {
      return;
    }

    try
    {
      $this->index->getType(get_class($object))->deleteById($object->id);
    }
    catch (\Elastica\Exception\NotFoundException $e)
    {
      // Ignore
    }
  }

  public function update($object)
  {
    if (!$this->enabled)
    {
      return;
    }

    if ($object instanceof QubitUser)
    {
      return;
    }

    $className = 'arElasticSearch'.str_replace('Qubit', '', get_class($object));

    return call_user_func(array($className, 'update'), $object);
  }
}
