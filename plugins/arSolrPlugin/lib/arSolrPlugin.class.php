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
class arSolrPlugin extends QubitSearchEngine
{
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

    /**
     * Constructor.
     */
    public function __construct(array $options = [])
    {
        parent::__construct();

        $SOLR_COLLECTION = 'atom42';
        $this->solrClientOptions = [
            'hostname' => 'solr1',
            'login' => 'solr',
            'password' => '',
            'port' => 8983,
            'collection' => $SOLR_COLLECTION,
            'path' => '/solr/'.$SOLR_COLLECTION,
        ];
        $this->initialize();
    }

    public function __destruct()
    {
        if (!$this->enabled) {
            return;
        }

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
        $esMapping = new arSolrMapping();
        $esMapping->loadYAML(array_shift($files));

        return $esMapping;
    }

    public function loadDiacriticsMappings()
    {
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

        return sfYaml::load(array_shift($diacriticsFiles));
    }

    public function flush()
    {
        try {
            $url = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'].'/solr/admin/collections?action=DELETE&name='.$this->solrClientOptions['collection'];
            $options = [
              'http' => [
                  'method' => 'GET',
                  'header' => "Content-Type: application/json\r\n".
                              "Accept: application/json\r\n",
              ],
            ];
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $response = json_decode($result);
            $this->log('Printing collections');
            $this->log(print_r($response->collections, true));
        } catch (Exception $e) {
        }

        $this->initialize();

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

        // Delete index and initialize again if all document types are to be
        // indexed and not updating
        if (!count($excludeTypes) && !$update) {
            $this->flush();
            $this->log('Index erased.');
        } else {
            // Initialize index if necessary
            $this->initialize();

            // Load mappings if index initialization wasn't needed
            $this->loadAndNormalizeMappings();
        }

        // Display what types will be indexed
        // $this->displayTypesToIndex($excludeTypes);

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

        $this->log('Populating index...');
 
        // Document counter, timer and errors
        $total = 0;
        $timer = new QubitTimer();
        $errors = [];
        $showErrors = false;

        foreach ($this->mappings as $typeName => $typeProperties) {
            if (!in_array(strtolower($typeName), $excludeTypes)) {
                $camelizedTypeName = sfInflector::camelize($typeName);
                $className = 'arSolr'.$camelizedTypeName;

                // If excluding types then index as a whole hasn't been flushed: delete
                // type's documents if not updating
                // if (count($excludeTypes) && !$update) {
                //     $this->index->getType('Qubit'.$camelizedTypeName)->deleteByQuery(new \Elastica\Query\MatchAll());
                // }

                $class = new $className();
                $class->setTimer($timer);

                $typeErrors = $class->populate();
                if (count($typeErrors) > 0) {
                    $showErrors = true;
                    $errors = array_merge($errors, $typeErrors);
                }

                $total += $class->getCount();
            }
        }

        $this->log(
            vsprintf(
                'Index populated with %s documents in %s seconds.',
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

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Centralize document addition to keep control of the batch queue.
     *
     * @param mixed $data
     * @param mixed $type
     */
    public function addDocument($data, $type)
    {
        if (!isset($data['id'])) {
            throw new sfException('Failed to parse id field.');
        }

        $id = $data['id'];

        $url = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'].'/solr/'.$this->solrClientOptions['collection'].'/update/json/docs';
        $options = [
            'http' => [
                'method' => 'POST',
                'content' => json_encode($data),
                'header' => "Content-Type: application/json\r\n".
                            "Accept: application/json\r\n",
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);

        unset($data['id']);
    }

    /**
     * Initialize Solr index if it does not exist.
     */
    protected function initialize()
    {
        if (sfConfig::get('app_diacritics')) {
            $this->config['index']['configuration']['analysis']['char_filter']['diacritics_lowercase'] = $this->loadDiacriticsMappings();
        }
        $url = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'].'/solr/admin/collections?action=LIST';
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "Content-Type: application/json\r\n".
                            "Accept: application/json\r\n",
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);

        $this->log(print_r($response->collections, true));

        if (array_search($this->solrClientOptions['collection'], $response->collections) !== false) {
            $this->log("Collection found. Not initializing");
        } else {
            $this->log('Initializing Solr Index');
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

            $this->log('Creating Solr Collection');
            $url = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'].'/solr/admin/collections?action=CREATE&name='.$this->solrClientOptions['collection'].'&numShards=2&replicationFactor=1&wt=json';
            $options = [
                'http' => [
                    'method' => 'GET',
                    'header' => "Content-Type: application/json\r\n".
                                "Accept: application/json\r\n",
                ],
            ];
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $response = json_decode($result);

            $this->log(print_r($response, true));

            // Add fields to 'all' field
            $this->addFieldToType('all', 'text_general', true);

            $url = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'].'/api/collections/'.$this->solrClientOptions['collection'].'/config/';
            $updateDefaultHandler = '{"update-requesthandler": {"name": "/select", "class": "solr.SearchHandler", "defaults": {"df": "all", "rows": 10, "echoParams": "explicit"}}}';
            $options = [
                'http' => [
                    'method' => 'POST',
                    'content' => $updateDefaultHandler,
                    'header' => "Content-Type: application/json\r\n".
                                "Accept: application/json\r\n",
                ],
            ];
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $response = json_decode($result);

            $this->log(print_r($response, true));

            // Load and normalize mappings
            $this->loadAndNormalizeMappings();

            // Iterate over types (actor, informationobject, ...)
            foreach ($this->mappings as $typeName => $typeProperties) {
                $typeName = 'Qubit'.sfInflector::camelize($typeName);

                foreach ($typeProperties['properties'] as $key => $value) {
                    if ($value['type'] != null) {
                        $this->addFieldToType($key, $this->setType($value['type']), false);
                    } else {
                        // nested fields
                        $this->addFieldToType($key, '_nest_path_', true);

                        foreach ($value['properties'] as $k => $v) {
                            if ($v['type'] != null) {
                                $this->addFieldToType($key.'.'.$k, $this->setType($v['type']), false);
                            }
                        }
                    }
                }
            }
        }
    }

    private function setType($type) {
        if ($type === 'integer') {
            return 'pint';
        } else if ($type === 'date') {
            return 'pdate';
        } else if ($type === 'long') {
            return 'plong';
        }  else if ($type === 'text') {
            return 'text_general';
        } else if ($type === 'keyword') {
            return 'string';
        } else {
            // object & nested
            // TODO
            return 'WIP';
        }
    }

    private function addFieldToType($field, $type, $multiValue) {
        $url = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'].'/solr/'.$this->solrClientOptions['collection'].'/schema/';
        $addFieldQuery = '"add-field": {"name": "'.$field.'","stored": "true","type": "'.$type.'","indexed": "true","multiValued": "'.$multiValue.'"}';
        $copySourceDest = '"add-copy-field": {"source": "'.$field.'", "dest": "all"}';
        $options = [
            'http' => [
                'method' => 'POST',
                'content' => "{".$addFieldQuery.",".$copySourceDest."}",
                'header' => "Content-Type: application/json\r\n".
                            "Accept: application/json\r\n",
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);

        $this->log(sprintf('Defining mapping %s...', $field));
        $this->log(print_r($response, true));
    }

    private function loadAndNormalizeMappings()
    {
        if (null === $this->mappings) {
            $mappings = self::loadMappings();
            $mappings->cleanYamlShorthands(); // Remove _attributes, _foreign_types, etc.
            $this->mappings = $mappings->asArray();
        }
    }
}
