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
 * arElasticSearchPlugin main class.
 *
 * @author      MJ Suhonos <mj@suhonos.ca>
 * @author      Jesús García Crespo <jesus@sevein.com>
 */
class arElasticSearchPlugin extends QubitSearchEngine
{
    /**
     * Minimum version of Elasticsearch supported.
     */
    public const MIN_VERSION = '6.0.0';

    /**
     * Dummy type for the ElasticSearch index.
     * This is required in ES 6.x but it is optional in
     * ES 7.x and can be removed when ElasticSearch and
     * Elastica are upgraded.
     */
    public const ES_TYPE = '_doc';

    /**
     * Elastic_Client object.
     *
     * @var mixed defaults to null
     */
    public $client;

    /**
     * Elastic_Index object.
     *
     * @var mixed defaults to null
     */
    public $index;

    /**
     * Current batch index name, used for batch flush.
     *
     * @var mixed defaults to null
     */
    protected $currentBatchIndexName;

    /**
     * Mappings configuration, mapping.yml.
     *
     * @var mixed defaults to null
     */
    protected $mappings;

    /**
     * If false, this plugin will perform a trial run with no changes made.
     *
     * @var mixed defaults to true
     */
    protected $enabled = true;

    /**
     * Elasticsearch bulk API makes it possible to perform many operations in a
     * single call. This can greatly increase the indexing speed.
     *
     * This array will be used to store documents to add in a batch.
     *
     * @var array
     */
    private $batchAddDocs = [];

    /**
     * This array will be used to store documents to delete in a batch.
     *
     * @var array
     */
    private $batchDeleteDocs = [];

    private $batchSize;
    private $batchMode;
    private $cache;
    private $config;

    /**
     * Constructor.
     */
    public function __construct(array $options = [])
    {
        parent::__construct();

        $this->cache = QubitCache::getInstance();

        $this->config = arElasticSearchPluginConfiguration::$config;
        $this->client = new \Elastica\Client($this->config['server']);

        // Verify the version running in the server
        $this->checkVersion();

        $this->index = new arElasticSearchMultiIndexWrapper();

        // Load batch mode configuration
        $this->batchMode = true === $this->config['batch_mode'];
        $this->batchSize = $this->config['batch_size'];

        if (isset($options['initialize']) && false === $options['initialize']) {
            return;
        }

        $this->initialize();
    }

    public function __destruct()
    {
        if (!$this->enabled) {
            return;
        }

        $this->flushBatch();
    }

    public static function loadMappings()
    {
        // Find mapping.yml
        $finder = sfFinder::type('file')->name('mapping.yml');
        $files = array_unique(
            array_merge(
                $finder->in(sfConfig::get('sf_config_dir')),
                $finder->in(ProjectConfiguration::getActive()->getPluginSubPaths('/config'))
            )
        );

        if (!count($files)) {
            throw new sfException('You must create a mapping.xml file.');
        }

        // Load first mapping.yml file found
        $esMapping = new arElasticSearchMapping();
        $esMapping->loadYAML(array_shift($files));

        return $esMapping;
    }

    /**
     * Optimize index.
     *
     * @param mixed $args
     */
    public function optimize($args = [])
    {
        return $this->client->optimizeAll($args);
    }

    /*
     * Flush batch of documents if we're in batch mode.
     *
     * We process additions before deletions to avoid an error due to deleting a
     * document that hasn't been created yet.
     */
    public function flushBatch()
    {
        if (!$this->batchMode || !$this->currentBatchIndexName) {
            return;
        }

        // Batch add documents, if any
        if (count($this->batchAddDocs) > 0) {
            try {
                $this->index->addDocuments($this->currentBatchIndexName, $this->batchAddDocs);
            } catch (Exception $e) {
                // Clear batchAddDocs if something went wrong too
                $this->batchAddDocs = [];

                throw $e;
            }

            $this->batchAddDocs = [];
        }

        // Batch delete documents, if any
        if (count($this->batchDeleteDocs) > 0) {
            try {
                $this->index->deleteDocuments($this->currentBatchIndexName, $this->batchDeleteDocs);
            } catch (Exception $e) {
                // Clear batchDeleteDocs if something went wrong too
                $this->batchDeleteDocs = [];

                throw $e;
            }

            $this->batchDeleteDocs = [];
        }

        $this->index->refresh($this->currentBatchIndexName);
    }

    /**
     * Populate index.
     *
     * @param mixed $options
     */
    public function populate($options = [])
    {
        $excludeTypes = (!empty($options['excludeTypes'])) ? $options['excludeTypes'] : [];
        $update = (!empty($options['update'])) ? $options['update'] : false;

        // Make sure it's initialized, QubitSearch::disable() gets an instance
        // without initialization and it's used in the install/purgue tasks.
        $this->initialize();
        $this->loadAndNormalizeMappings();
        $this->loadDiacriticsMappings();
        $this->configureFilters();

        $indicesCount = $this->countAndDisplayIndices($excludeTypes, $update);
        if (0 == $indicesCount) {
            return;
        }

        // If we're indexing IOs or Actors we'll cache a term id => parent id
        // array with all terms from the needed taxonomies in sfConfig. This
        // array will be used to obtain the related terms ancestor ids without
        // hitting the DB in arElasticSearchModelBase.
        $indexingIos = !in_array('informationobject', $excludeTypes);
        $indexingActors = !in_array('actor', $excludeTypes);

        if ($indexingIos || $indexingActors) {
            $taxonomies = [QubitTaxonomy::SUBJECT_ID, QubitTaxonomy::PLACE_ID];

            if ($indexingIos) {
                $taxonomies[] = QubitTaxonomy::GENRE_ID;
            }

            sfConfig::set(
                'term_parent_list',
                QubitTerm::loadTermParentList($taxonomies)
            );
        }

        if ($update) {
            $this->log('Populating indices...');
        } else {
            $this->log('Defining and populating indices...');
        }

        // Document counter, timer and errors
        $total = 0;
        $timer = new QubitTimer();
        $errors = [];
        $showErrors = false;

        foreach ($this->mappings as $indexName => $indexProperties) {
            if (in_array(strtolower($indexName), $excludeTypes)) {
                continue;
            }

            $camelizedTypeName = sfInflector::camelize($indexName);
            $className = 'arElasticSearch'.$camelizedTypeName;
            $indexName = 'Qubit'.$camelizedTypeName;

            $this->recreateIndex($indexName, $indexProperties, $update);

            $class = new $className();
            $class->setTimer($timer);

            $typeErrors = $class->populate();
            if (count($typeErrors) > 0) {
                $showErrors = true;
                $errors = array_merge($errors, $typeErrors);
            }

            $total += $class->getCount();
        }

        $this->log(
            vsprintf(
                'Indices populated with %s documents in %s seconds.',
                [$total, $timer->elapsed()]
            )
        );

        if (!$showErrors) {
            return;
        }

        // Log errors
        $this->log('The following errors have been encountered:');
        foreach ($errors as $error) {
            $this->log($error);
        }
        $this->log('Please, contact an administrator.');
    }

    /**
     * Populate index.
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
     * Centralize document addition to keep control of the batch queue.
     *
     * @param mixed $data
     * @param mixed $indexName
     */
    public function addDocument($data, $indexName)
    {
        if (!$this->enabled) {
            return;
        }

        if (!isset($data['id'])) {
            throw new sfException('Failed to parse id field.');
        }

        // Pass the id value to the \Elastica\Document constructor instead of as
        // part of the document body. ES _id field id
        $id = $data['id'];
        unset($data['id']);

        $document = new \Elastica\Document($id, $data);

        // Setting a dummy type since it is required in ES 6.x
        // but it can be removed in 7.x when it becomes optional
        $document->setType(self::ES_TYPE);

        if ($this->batchMode) {
            if (!$this->currentBatchIndexName) {
                $this->currentBatchIndexName = $indexName;
            }

            if ($this->currentBatchIndexName != $indexName) {
                $this->flushBatch();
                $this->currentBatchIndexName = $indexName;
            }

            // Add this document to the batch add queue
            $this->batchAddDocs[] = $document;

            // If we have a full batch, send additions and deletions in bulk
            if (count($this->batchAddDocs) >= $this->batchSize) {
                $this->flushBatch();
            }
        } else {
            $this->index->addDocuments($indexName, [$document]);
        }
    }

    /**
     * Partial data will be merged into the existing document
     * (simple recursive merge, inner merging of objects,
     * replacing core "keys/values" and arrays). There is no
     * way to delete a field using this method but, if it's
     * considered where needed, it can be set to 'null'.
     *
     * @param mixed $object
     * @param mixed $data
     */
    public function partialUpdate($object, $data)
    {
        if (!$this->enabled) {
            return;
        }

        if ($object instanceof QubitUser) {
            return;
        }

        $indexName = get_class($object);

        $document = new \Elastica\Document($object->id, $data);

        // Setting a dummy type since it is required in ES 6.x
        // but it can be removed in 7.x when it becomes optional
        $document->setType(self::ES_TYPE);

        try {
            $this->index->updateDocuments($indexName, [$document]);
        } catch (\Elastica\Exception\NotFoundException $e) {
            // Create document if it's not found
            $this->update($object);
        }
    }

    public function partialUpdateById(string $className, int $id, array $data)
    {
        if (!$this->enabled) {
            return;
        }

        if (0 == strcmp($className, 'QubitUser')) {
            return;
        }

        $document = new \Elastica\Document($id, $data);

        try {
            $this->index->updateDocuments($className, [$document]);
        } catch (\Elastica\Exception\ResponseException $e) {
            // Create document if none exists
            $modelPdoClassName = self::modelClassFromQubitObjectClass($className).'Pdo';

            if (class_exists($modelPdoClassName)) {
                $node = new $modelPdoClassName($id);
                QubitSearch::getInstance()->addDocument($node->serialize(), $className);
            }
        }
    }

    // ---------------------------------------------------------------------------

    public function delete($object)
    {
        if (!$this->enabled) {
            return;
        }

        if ($object instanceof QubitUser) {
            return;
        }

        $indexName = get_class($object);
        if ($this->batchMode) {
            if (!$this->currentBatchIndexName) {
                $this->currentBatchIndexName = $indexName;
            }

            // The document being deleted may not have been added to the index yet (if it's
            // still queued up in $this->batchAddDocs) so create a document object representing
            // the document to be deleted and add this document object to the batch delete
            // queue.
            $document = new \Elastica\Document($object->id);
            $document->setType(self::ES_TYPE);

            if ($this->currentBatchIndexName != $indexName) {
                $this->flushBatch();
                $this->currentBatchIndexName = $indexName;
            }

            $this->batchDeleteDocs[] = $document;

            // If we have a full batch, send additions and deletions in bulk
            if (count($this->batchDeleteDocs) >= $this->batchSize) {
                $this->flushBatch();
            }
        } else {
            try {
                $this->index->deleteById($indexName, $object->id);
            } catch (\Elastica\Exception\NotFoundException $e) {
                // Ignore
            }
        }
    }

    public function update($object, $options = [])
    {
        if (!$this->enabled) {
            return;
        }

        if ($object instanceof QubitUser) {
            return;
        }

        $className = self::modelClassFromQubitObjectClass(get_class($object));

        // Pass options only to information object update
        if ($object instanceof QubitInformationObject) {
            call_user_func([$className, 'update'], $object, $options);

            return;
        }

        call_user_func([$className, 'update'], $object);
    }

    /**
     * Get ElasticSearch model class from Qubit class.
     *
     * @param string $className
     *
     * @return string ElasticSearch model class name
     */
    public static function modelClassFromQubitObjectClass($className)
    {
        return str_replace('Qubit', 'arElasticSearch', $className);
    }

    /**
     * Initialize indices wrapper.
     */
    private function initialize()
    {
        $indices = ['aip', 'term', 'actor', 'accession', 'repository', 'functionObject', 'informationObject'];
        foreach ($indices as $indexName) {
            $indexName = 'Qubit'.sfInflector::camelize($indexName);
            $prefixedIndexName = $this->config['index']['name'].'_'.strtolower($indexName);
            $index = $this->client->getIndex($prefixedIndexName);
            $this->index->addIndex($indexName, $index);
        }
    }

    private function recreateIndex($indexName, $indexProperties, $update)
    {
        $index = $this->index->getIndex($indexName);

        // No need to recreate updating an existing index.
        if ($update && $index->exists()) {
            return;
        }

        // In ES 7.x if the mapping type is updated to a dummy type,
        // this may need to include a param for include_type_name
        // set to false in order to avoid automatically creating a
        // type for the index that was just created
        $index->create(
            $this->config['index']['configuration'],
            ['recreate' => true]
        );

        // Define mapping in elasticsearch
        $mapping = new \Elastica\Type\Mapping();

        // Setting a dummy type since it is required in ES 6.x
        // but it can be removed in 7.x when it becomes optional
        $mapping->setType($index->getType(self::ES_TYPE));
        $mapping->setProperties($indexProperties['properties']);

        // Parse other parameters
        unset($indexProperties['properties']);
        foreach ($indexProperties as $key => $value) {
            $mapping->setParam($key, $value);
        }

        $this->log(sprintf(
            'Defining mapping for index %s...',
            $this->config['index']['name'].'_'.strtolower($indexName)
        ));

        // In ES 7.x this should be changed to:
        // $mapping->send($index, [ 'include_type_name' => false ])
        // which can be removed in 8.x since that is the default behaviour
        // and will have be removed by 9.x when it is discontinued
        $mapping->send();
    }

    private function loadDiacriticsMappings()
    {
        if (!sfConfig::get('app_diacritics')) {
            return;
        }

        // Find diacritics_mapping.yml
        $diacriticsFinder = sfFinder::type('file')->name('diacritics_mapping.yml');
        $diacriticsFiles = array_unique(
            array_merge(
                $diacriticsFinder->in(sfConfig::get('sf_upload_dir')),
            )
        );

        if (!count($diacriticsFiles)) {
            throw new sfException('You must create a diacritics_mapping.yml file.');
        }

        $this->config['index']['configuration']['analysis']['char_filter']['diacritics_lowercase'] = sfYaml::load(array_shift($diacriticsFiles));
    }

    /**
     *  Set filter configuration params based on markdown settings.
     */
    private function configureFilters()
    {
        // Based on markdown_enabled setting, add a new filter to strip Markdown tags
        if (
            sfConfig::get('app_markdown_enabled', true)
            && isset($this->config['index']['configuration']['analysis']['char_filter']['strip_md'])
        ) {
            foreach ($this->config['index']['configuration']['analysis']['analyzer'] as $key => $analyzer) {
                $filters = ['strip_md'];

                if ($this->config['index']['configuration']['analysis']['analyzer'][$key]['char_filter']) {
                    $filters = array_merge($filters, $this->config['index']['configuration']['analysis']['analyzer'][$key]['char_filter']);
                }

                if (sfConfig::get('app_diacritics')) {
                    $filters = array_merge($filters, ['diacritics_lowercase']);
                }

                $this->config['index']['configuration']['analysis']['analyzer'][$key]['char_filter'] = $filters;
            }
        }
    }

    /**
     * Obtain the version of the Elasticsearch server.
     */
    private function getVersion()
    {
        $data = $this->client->request('/')->getData();
        if (null === $version = @$data['version']['number']) {
            throw new \Elastica\Exception\ResponseException('Unexpected response');
        }

        return $version;
    }

    /**
     * Check if the server version is recent enough and cache it if so to avoid
     * hitting Elasticsearch again for each request.
     */
    private function checkVersion()
    {
        // Avoid the check if the cache entry is still available
        if ($this->cache->has('elasticsearch_version_ok')) {
            return;
        }

        // This is slow as it hits the server
        $version = $this->getVersion();
        if (!version_compare($version, self::MIN_VERSION, '>=')) {
            $message = sprintf('The version of Elasticsearch that you are running is out of date (%s), and no longer compatible with this version of AtoM. Please upgrade to version %s or newer.', $version, self::MIN_VERSION);

            throw new \Elastica\Exception\ClientException($message);
        }

        // We know at this point that the server meets the requirements. We cache it
        // for an hour.
        $this->cache->set('elasticsearch_version_ok', 1, 3600);
    }

    private function loadAndNormalizeMappings()
    {
        if (null === $this->mappings) {
            $mappings = self::loadMappings();
            $mappings->cleanYamlShorthands(); // Remove _attributes, _foreign_types, etc.
            $this->mappings = $mappings->asArray();
        }
    }

    /**
     * Count and display indices that will be created/updated.
     *
     * @param array $excludeTypes
     * @param bool  $update
     *
     * @return int Count of indices
     */
    private function countAndDisplayIndices($excludeTypes, $update)
    {
        $count = 0;

        $this->log(sprintf('Indices that will be %s:', $update ? 'updated' : 'created'));

        foreach ($this->mappings as $indexName => $indexProperties) {
            $indexName = strtolower($indexName);
            if (!in_array($indexName, $excludeTypes)) {
                $this->log(' - '.$this->config['index']['name'].'_'.$indexName);
                ++$count;
            }
        }

        if (!$count) {
            $this->log('   None');
        }

        return $count;
    }
}
