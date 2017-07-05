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
   * Elasticsearch bulk API makes it possible to perform many operations in a
   * single call. This can greatly increase the indexing speed.
   *
   * This array will be used to store documents to add in a batch.
   *
   * @var array
   */
  private $batchAddDocs = array();

  /**
   * This array will be used to store documents to delete in a batch.
   *
   * @var array
   */
  private $batchDeleteDocs = array();

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
   * Minimum version of Elasticsearch supported
   */
  const MIN_VERSION = '1.3.0';

  /**
   * Constructor
   */
  public function __construct(array $options = array())
  {
    parent::__construct();

    $this->cache = QubitCache::getInstance();

    $this->config = arElasticSearchPluginConfiguration::$config;
    $this->client = new \Elastica\Client($this->config['server']);

    // Verify the version running in the server
    $this->checkVersion();

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
   * Obtain the version of the Elasticsearch server
   */
  private function getVersion()
  {
    $data = $this->client->request('/')->getData();
    if (null === $version = @$data['version']['number'])
    {
      throw new \Elastica\Exception\ResponseException('Unexpected response');
    }

    return $version;
  }

  /**
   * Check if the server version is recent enough and cache it if so to avoid
   * hitting Elasticsearch again for each request
   */
  private function checkVersion()
  {
    // Avoid the check if the cache entry is still available
    if ($this->cache->has('elasticsearch_version_ok'))
    {
      return;
    }

    // This is slow as it hits the server
    $version = $this->getVersion();
    if (!version_compare($version, self::MIN_VERSION, '>='))
    {
      $message = sprintf('The version of Elasticsearch that you are running is out of date (%s), and no longer compatible with this version of AtoM. Please upgrade to version %s or newer.', $version, self::MIN_VERSION);
      throw new \Elastica\Exception\ClientException($message);
    }

    // We know at this point that the server meets the requirements. We cache it
    // for an hour.
    $this->cache->set('elasticsearch_version_ok', 1, 3600);
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

      // Load and normalize mappings
      $this->loadAndNormalizeMappings();

      // Iterate over types (actor, informationobject, ...)
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

  private function loadAndNormalizeMappings()
  {
    if (null === $this->mappings)
    {
      $mappings = self::loadMappings();
      $mappings->cleanYamlShorthands(); // Remove _attributes, _foreign_types, etc.
      $this->mappings = $mappings->asArray();
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
    return $esMapping;
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

  /*
   * Flush batch of documents if we're in batch mode.
   *
   * We process additions before deletions to avoid an error due to deleting a
   * document that hasn't been created yet.
   */
  public function flushBatch()
  {
    if ($this->batchMode)
    {
      // Batch add documents, if any
      if (count($this->batchAddDocs) > 0)
      {
        try
        {
          $this->index->addDocuments($this->batchAddDocs);
        }
        catch (Exception $e)
        {
          // Clear batchAddDocs if something went wrong too
          $this->batchAddDocs = array();

          throw $e;
        }

        $this->batchAddDocs = array();
      }

      // Batch delete documents, if any
      if (count($this->batchDeleteDocs) > 0)
      {
        try
        {
          $this->index->deleteDocuments($this->batchDeleteDocs);
        }
        catch (Exception $e)
        {
          // Clear batchDeleteDocs if something went wrong too
          $this->batchDeleteDocs = array();

          throw $e;
        }

        $this->batchDeleteDocs = array();
      }
    }
  }

  /**
   * Populate index
   */
  public function populate($excludeTypes = null)
  {
    $excludeTypes = (!empty($excludeTypes)) ? $excludeTypes : array();

    // Delete index and initialize again if all document types are to be indexed
    if (!count($excludeTypes))
    {
      $this->flush();
      $this->log('Index erased.');
    }
    else
    {
      // Initialize index if necessary
      $this->initialize();

      // Load mappings if index initialization wasn't needed
      $this->loadAndNormalizeMappings();
    }

    // Display what types will be indexed
    $this->displayTypesToIndex($excludeTypes);

    $this->log('Populating index...');

    // Document counter, timer and errors
    $total = 0;
    $timer = new QubitTimer;
    $errors = array();
    $showErrors = false;

    foreach ($this->mappings as $typeName => $typeProperties)
    {
      if (!in_array(strtolower($typeName), $excludeTypes))
      {
        $camelizedTypeName = sfInflector::camelize($typeName);
        $className = 'arElasticSearch'.$camelizedTypeName;

        // If excluding types then index as a whole hasn't been flushed: delete type's documents
        if (count($excludeTypes))
        {
          $this->index->getType('Qubit'. $camelizedTypeName)->deleteByQuery(new \Elastica\Query\MatchAll);
        }

        $class = new $className;
        $class->setTimer($timer);

        $typeErrors = $class->populate();
        if (count($typeErrors) > 0)
        {
          $showErrors = true;
          $errors = array_merge($errors, $typeErrors);
        }

        $total += $class->getCount();
      }
    }

    $this->log(vsprintf('Index populated with %s documents in %s seconds.',
      array(
        $total,
        $timer->elapsed())));

    if (!$showErrors)
    {
      return;
    }

    // Log errors
    $this->log('The following errors have been encountered:');
    foreach ($errors as $error)
    {
      $this->log($error);
    }
    $this->log('Please, contact an administrator.');
  }

  /**
   * Display types that will be indexed
   */
  private function displayTypesToIndex($excludeTypes)
  {
    $typeCount = 0;

    $this->log('Types that will be indexed:');

    foreach ($this->mappings as $typeName => $typeProperties)
    {
      if (!in_array(strtolower($typeName), $excludeTypes))
      {
        $this->log(' - '. $typeName);
        $typeCount++;
      }
    }

    if (!$typeCount)
    {
      $this->log('   None');
    }
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
      // Add this document to the batch add queue
      $this->batchAddDocs[] = $document;

      // If we have a full batch, send additions and deletions in bulk
      if (count($this->batchAddDocs) >= $this->batchSize)
      {
        $this->flushBatch();
        $this->index->refresh();
      }
    }
    else
    {
      $this->index->getType($type)->addDocument($document);
    }
  }

  /**
   * Partial data will be merged into the existing document
   * (simple recursive merge, inner merging of objects,
   * replacing core "keys/values" and arrays). There is no
   * way to delete a field using this method but, if it's
   * considered where needed, it can be set to 'null'.
   */
  public function partialUpdate($object, $data)
  {
    if (!$this->enabled)
    {
      return;
    }

    if ($object instanceof QubitUser)
    {
      return;
    }

    $document = new \Elastica\Document($object->id, $data);

    try
    {
      $this->index->getType(get_class($object))->updateDocument($document);
    }
    catch (\Elastica\Exception\NotFoundException $e)
    {
      // Create document if it's not found
      $this->update($object);
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

    $query = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($query));
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

    if ($this->batchMode)
    {
      // The document being deleted may not have been added to the index yet (if it's
      // still queued up in $this->batchAddDocs) so create a document object representing
      // the document to be deleted and add this document object to the batch delete
      // queue.
      $document = new \Elastica\Document($object->id);
      $document->setType(get_class($object));

      $this->batchDeleteDocs[] = $document;

      // If we have a full batch, send additions and deletions in bulk
      if (count($this->batchDeleteDocs) >= $this->batchSize)
      {
        $this->flushBatch();
        $this->index->refresh();
      }
    }
    else
    {
      try
      {
        $this->index->getType(get_class($object))->deleteById($object->id);
      }
      catch (\Elastica\Exception\NotFoundException $e)
      {
        // Ignore
      }
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
