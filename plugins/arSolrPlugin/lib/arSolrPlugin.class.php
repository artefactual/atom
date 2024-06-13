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
 * arSolrPlugin main class.
 *
 * @author      MJ Suhonos <mj@suhonos.ca>
 * @author      Jesús García Crespo <jesus@sevein.com>
 */
class arSolrPlugin extends QubitSearchEngine
{
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

        $SOLR_COLLECTION = 'atom';
        $this->solrClientOptions = [
            'hostname' => 'solr1',
            'login' => 'solr',
            'password' => '',
            'port' => 8983,
            'collection' => $SOLR_COLLECTION,
            'path' => '/solr/'.$SOLR_COLLECTION,
        ];
        $this->solrBaseUrl = 'http://'.$this->solrClientOptions['hostname'].':'.$this->solrClientOptions['port'];
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
            $url = $this->solrBaseUrl.'/solr/'.$this->solrClientOptions['collection'].'/update/';
            $query = '{"delete": {"query": "*:*"}}';
            arSolrPlugin::makeHttpRequest($url, 'POST', $query);
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

            // Load mappings
            $this->loadAndNormalizeMappings();
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

        $url = $this->solrBaseUrl.'/solr/'.$this->solrClientOptions['collection'].'/update/json/docs';
        arSolrPlugin::makeHttpRequest($url, 'POST', json_encode($data));

        unset($data['id']);
    }

    public function getSolrUrl()
    {
        return $this->solrBaseUrl;
    }

    public function getSolrCollection()
    {
        return $this->solrClientOptions['collection'];
    }

    public function search($query)
    {
        $url = $this->getSolrUrl().'/solr/'.$this->getSolrCollection().'/query';
        $response = arSolrPlugin::makeHttpRequest($url, 'POST', json_encode($query->getQueryParams()));

        return $response->response->docs;
    }

    public static function makeHttpRequest($url, $method = 'GET', $body = null)
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => "Content-Type: application/json\r\n".
                            "Accept: application/json\r\n",
            ],
        ];
        if ($body) {
            $options['http']['content'] = $body;
        }
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return json_decode($result);
    }

    /**
     * Initialize Solr index if it does not exist.
     */
    protected function initialize()
    {
        if (sfConfig::get('app_diacritics')) {
            $this->config['index']['configuration']['analysis']['char_filter']['diacritics_lowercase'] = $this->loadDiacriticsMappings();
        }
        $url = $this->solrBaseUrl.'/solr/admin/collections?action=LIST';
        $response = arSolrPlugin::makeHttpRequest($url);

        if (false !== array_search($this->solrClientOptions['collection'], $response->collections)) {
            $this->log('Collection found. Not initializing');
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
            $url = $this->solrBaseUrl.'/solr/admin/collections?action=CREATE&name='.$this->solrClientOptions['collection'].'&numShards=2&replicationFactor=1&wt=json';
            arSolrPlugin::makeHttpRequest($url);

            $addFieldKeys = [];
            $configParams = [];

            // Add fields to 'all' field
            //array_push($addFieldQuery, $this->getFieldQuery('all', 'text_general', true, false, false));

            $url = $this->solrBaseUrl.'/api/collections/'.$this->solrClientOptions['collection'].'/config/';
            $updateDefaultHandler = '{"update-requesthandler": {"name": "/select", "class": "solr.SearchHandler", "defaults": {"echoParams": "explicit"}}}';
            arSolrPlugin::makeHttpRequest($url, 'POST', $updateDefaultHandler);

            // Load and normalize mappings
            $this->loadAndNormalizeMappings();

            // Iterate over types (actor, informationobject, ...)
            foreach ($this->mappings as $typeName => $typeProperties) {
                $typeName = 'Qubit'.sfInflector::camelize($typeName);

                foreach ($typeProperties['properties'] as $subType => $value) {
                    if (null != $value['type'] && 'nested' !== $value['type'] && 'object' !== $value['type']) {
                        array_key_exists($subType, $addFieldKeys) ?: $addFieldKeys[$subType] = $this->getFieldQuery($subType, $this->setType($value['type']), false);
                    } else {
                        if (null === $value['type']) {
                            // array fields
                            array_key_exists($subType, $addFieldKeys) ?: $addFieldKeys[$subType] = $this->getFieldQuery($subType, '_nest_path_', true);

                            $nestedFields = $this->addNestedFields($subType, $value['properties']);
                            foreach($nestedFields as $field) {
                                $addFieldKeys[$field['name']] = $field;
                            }
                        } else {
                            // object and nested fields
                            $fields = [];
                            foreach ($value['properties'] as $fieldName => $value) {
                                if ('object' === $value['type']) {
                                    foreach ($value['properties'] as $propertyName => $v) {
                                        array_push($fields, '"'.$subType.':/'.$fieldName.'/'.$propertyName.'"');
                                        array_key_exists($propertyName, $addFieldKeys) ?: $addFieldKeys[$propertyName] = $this->getFieldQuery($propertyName, $this->setType($v['type']), false);
                                    }
                                } elseif (null != $value['type']) {
                                    array_push($fields, '"'.$subType.':/'.$fieldName.'"');
                                    array_key_exists($fieldName, $addFieldKeys) ?: $addFieldKeys[$fieldName] = $this->getFieldQuery($fieldName, $this->setType($value['type']), false);
                                }
                            }

                            if (array_key_exists($subType, $configParams)) {
                                foreach ($fields as $field) {
                                    if (!in_array($field, $configParams[$subType])) {
                                        array_push($configParams[$subType], $field);
                                    }
                                }
                            } else {
                                $configParams[$subType] = $fields;
                            }
                        }
                    }
                }
            }

            foreach($configParams as $param => $value) {
                $this->defineConfigParams($param, $value);
            }

            $addFieldQuery = [];
            foreach ($addFieldKeys as $value) {
                array_push($addFieldQuery, $value);
            }
            $addQuery = ['add-field' => $addFieldQuery];
            $this->addFieldsToType(json_encode($addQuery));
        }
    }

    private function addNestedFields($key, $properties)
    {
        $nestedField = [];
        foreach ($properties as $k => $v) {
            if (null === $v['type']) {
                $this->addNestedFields($k, $v['properties']);
            } else {
                array_push($nestedField, $this->getFieldQuery($key.'.'.$k, $this->setType($v['type']), false));
            }
        }

        return $nestedField;
    }

    private function defineConfigParams($name, $fields)
    {
        $url = $this->solrBaseUrl.'/solr/'.$this->solrClientOptions['collection'].'/config/params';
        $query = '"set": {"'.$name.'": {"split": "/'.$name.'", "f":['.implode(',', $fields).']}}';
        arSolrPlugin::makeHttpRequest($url, 'POST', $query);
    }

    private function setType($type)
    {
        if ('integer' === $type) {
            return 'pint';
        }
        if ('date' === $type) {
            return 'pdate';
        }
        if ('long' === $type) {
            return 'plong';
        }
        if ('text' === $type) {
            return 'text_general';
        }
        if ('keyword' === $type) {
            return 'string';
        }
        if ('geo_point' === $type) {
            return 'location';
        }

        return $type;
    }

    private function getFieldQuery($field, $type, $multiValue, $stored = true)
    {
        $stored = $stored ? 'true' : 'false';
        $multiValue = $multiValue ? 'true' : 'false';
        $addFieldQuery = [
            'name' => $field,
            'stored' => $stored,
            'type' => $type,
            'indexed' => 'true',
            'multiValued' => $multiValue,
        ];
        $this->log(sprintf('Defining mapping %s...', $field));

        return $addFieldQuery;
    }

    private function addFieldsToType($query)
    {
        $this->log("Adding fields now");
        $this->log($query);
        $url = $this->solrBaseUrl.'/solr/'.$this->solrClientOptions['collection'].'/schema/';
        arSolrPlugin::makeHttpRequest($url, 'POST', $query);
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
