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
 */
class arSolrPlugin extends QubitSearchEngine
{
    public $lang_codes = ['ar', 'hy', 'ba', 'br', 'bg', 'ca', 'cz', 'da', 'nl', 'en', 'fi', 'fr', 'gl', 'ge', 'el', 'hi', 'hu', 'id', 'it', 'no', 'fa', 'pt', 'ro', 'ru', 'es', 'sv', 'tr'];

    public $langs = [];

    public $client;

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

        $this->config = arSolrPluginConfiguration::$config;

        $this->client = new arSolrClient($this->config['solr']);

        // Load batch mode configuration
        $this->batchMode = true === $this->config['batch_mode'];
        $this->batchSize = $this->config['batch_size'];

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
        $finder = sfFinder::type('file')->name('solrSchema.yml');
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
        $solrMapping = new arSolrMapping();
        $solrMapping->loadYAML(array_shift($files));

        return $solrMapping;
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

        return $diacriticsFiles;
    }

    public function flush()
    {
        try {
            $this->client->flushIndex();
        } catch (Exception $e) {
            $this->log('Error flushing index');
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
        if ($this->batchMode) {
            // Batch add documents, if any
            if (count($this->batchAddDocs) > 0) {
                try {
                    $response = $this->client->addDocuments($this->batchAddDocs);

                    if ($response->error) {
                        $this->log(var_export($response->error, true));
                        $this->log(json_encode($this->batchAddDocs));
                    }
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
                    $response = $this->client->deleteDocuments($this->batchDeleteDocs);

                    if ($response->error) {
                        $this->log(var_export($response->error, true));
                        $this->log(json_encode($this->batchDeleteDocs));
                    }
                } catch (Exception $e) {
                    // Clear batchDeleteDocs if something went wrong too
                    $this->batchDeleteDocs = [];

                    throw $e;
                }

                $this->batchDeleteDocs = [];
            }
        }
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

        // Add the last batch of documents
        $this->flushBatch();

        $this->addAutoCompleteConfigs();
        $this->setAnalyzers();

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

        if ($this->batchMode) {
            // Add this document to the batch add queue
            $document = [
                $type => $data,
            ];
            array_push($this->batchAddDocs, $document);

            // If we have a full batch, send additions and deletions in bulk
            if (count($this->batchAddDocs) >= $this->batchSize) {
                $this->flushBatch();
            }
        } else {
            $response = $this->client->addDocument([$type => $data]);

            if ($response->error) {
                $this->log(var_export($response->error, true));
                $this->log(json_encode([$type => $data]));
            }
        }
    }

    public function partialUpdate($object, $data)
    {
        if (!$this->enabled) {
            return;
        }

        if ($object instanceof QubitUser) {
            return;
        }

        $type = get_class($object);
        $response = $this->client->updateDocument([$type => $data]);

        if ($response->error) {
            $this->log(var_export($response->error, true));
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

        $response = $this->client->updateById($id, $className, $data);

        if ($response->error) {
            $this->log(var_export($response->error, true));
        }
    }

    public function delete($object)
    {
        if (!$this->enabled) {
            return;
        }

        if ($object instanceof QubitUser) {
            return;
        }

        if ($this->batchMode) {
            // The document being deleted may not have been added to the index yet (if it's
            // still queued up in $this->batchAddDocs) so create a document object representing
            // the document to be deleted and add this document object to the batch delete
            // queue.
            $document = $this->client->createDocumentWithId($object->id, get_class($object));

            $this->batchDeleteDocs[] = $document;

            // If we have a full batch, send additions and deletions in bulk
            if (count($this->batchDeleteDocs) >= $this->batchSize) {
                $this->flushBatch();
            }
        } else {
            try {
                $this->client->deleteById($object->id, get_class($object));
            } catch (Exception $e) {
                // Ignore
            }
        }
    }

    public function search($query, $type)
    {
        return $this->client->search($query);
    }

    /**
     * Initialize Solr index if it does not exist.
     */
    protected function initialize()
    {
        if (false !== $this->client->checkCollectionExists()) {
            $this->log('Collection found. Not initializing');
        } else {
            $this->log('Initializing Solr Index');

            $this->log('Creating Solr Collection');
            $this->client->createCollection();

            $topLevelProperties = [];
            $subProperties = [];

            // Load and normalize mappings
            $this->loadAndNormalizeMappings();

            // Iterate over types (actor, informationobject, ...)
            foreach ($this->mappings as $typeName => $typeProperties) {
                $typeName = 'Qubit'.sfInflector::camelize($typeName);
                array_push($topLevelProperties, $this->getFieldQuery($typeName, '_nest_path_', true));

                $this->addSubProperties($typeProperties['properties'], $subProperties, $typeName, $typeProperties['properties']);
            }

            $this->client->addFields($topLevelProperties);
            $this->client->addFields($subProperties);

            $this->addAutoCompleteFields();
        }
    }

    private function addAutoCompleteFields()
    {
        // list should also include QubitInformationObject.referenceCode but
        // since this field does not include a language code, it is added to
        // $addCopyField manually
        $autocompleteFields = [
            'QubitRepository.i18n.%s%.authorizedFormOfName',
            'QubitInformationObject.aip.type.i18n.%s%.name',
            'QubitInformationObject.i18n.%s%.title',
            'QubitActor.i18n.%s%.authorizedFormOfName',
            'QubitActor.places.i18n.%s%.name',
            'QubitActor.subjects.i18n.%s%.name',
            'QubitTerm.i18n.%s%.name',
            'QubitAip.type.i18n.%s%.name',
        ];

        $fields = [];
        $copyFields = [];

        foreach ($this->langs as $lang) {
            $langField = [
                'name' => "autocomplete_{$lang}",
                'type' => "text_{$lang}",
                'stored' => 'true',
                'multiValued' => 'true',
            ];

            $refField = [
                'source' => 'QubitInformationObject.referenceCode',
                'dest' => "autocomplete_{$lang}",
            ];

            array_push($fields, $langField);
            array_push($copyFields, $refField);

            foreach ($autocompleteFields as $field) {
                $field = str_replace('%s%', $lang, $field);
                array_push($copyFields, [
                    'source' => $field,
                    'dest' => "autocomplete_{$lang}",
                ]);
            }
        }

        $this->client->addFields($fields);
        $this->client->addCopyFields($copyFields);
    }

    private function addAutoCompleteConfigs()
    {
        foreach ($this->langs as $lang) {
            $this->client->modifyConfigParams('add-searchComponent', [
                'name' => "autocomplete_{$lang}",
                'class' => 'solr.SuggestComponent',
                'suggester' => [
                    'name' => "autocomplete_{$lang}",
                    'field' => "autocomplete_{$lang}",
                    'lookupImpl' => 'FuzzyLookupFactory',
                    'dictionaryImpl' => 'DocumentDictionaryFactory',
                    'suggestAnalyzerFieldType' => "text_{$lang}",
                ],
            ]);
            $this->client->modifyConfigParams('add-requestHandler', [
                'name' => "/autocomplete_{$lang}",
                'class' => 'solr.SearchHandler',
                'components' => ["autocomplete_{$lang}"],
                'defaults' => [
                    'suggest' => 'true',
                    'suggest.count' => 5,
                    'suggest.dictionary' => "autocomplete_{$lang}",
                ],
            ]);
        }
    }

    private function addSubProperties($properties, &$propertyFields, $parentType = '', $parentProperties = null)
    {
        if (!$parentProperties) {
            $parentProperties = $properties;
        }

        $atomicTypes = ['keyword', 'string', 'text', 'text_general', 'date', 'pdate', 'pdates', 'long', 'plongs', 'integer', 'boolean', 'location'];
        foreach ($properties as $propertyName => $value) {
            $fieldName = $parentType ? "{$parentType}.{$propertyName}" : $propertyName;

            $fields = explode('.', $fieldName);
            $lang = $fields[count($fields) - 2];
            if (in_array($lang, $this->lang_codes) && !in_array($lang, $this->langs)) {
                array_push($this->langs, $lang);
            }

            $i18nIndex = array_search('i18n', $fields);
            if ('languages' == $propertyName || ($value['multivalue'] && false == $i18nIndex)) {
                $multiValue = true;
            } else {
                $multiValue = $this->getMultiValue($fieldName);
            }

            if (in_array($value['type'], $atomicTypes)) {
                if ('text' === $value['type']) {
                    $typeName = $this->setLanguageType($fieldName);
                } else {
                    $typeName = $this->setType($value['type']);
                }
                $field = $this->getFieldQuery($fieldName, $typeName, $multiValue);
                array_push($propertyFields, $field);
            } elseif ('object' == $value['type']) {
                $field = $this->getFieldQuery($fieldName, '_nest_path_', true);
                array_push($propertyFields, $field);
            }

            if ($value['properties']) {
                $this->addSubProperties($value['properties'], $propertyFields, $fieldName, $parentProperties);
            }
        }
    }

    private function getMultiValue($fieldName)
    {
        $properties = explode('.', $fieldName);
        $properties[0] = str_replace('Qubit', '', $properties[0]);
        $properties[0] = lcfirst($properties[0]);

        $path = [];
        foreach ($properties as $property) {
            array_push($path, $property);
            array_push($path, 'properties');
        }

        array_pop($path);

        $mapping = &$this->mappings;
        foreach ($path as $key) {
            if (!array_key_exists($key, $mapping)) {
                $mapping[$key] = [];
            }
            $mapping = &$mapping[$key];
        }

        if (array_key_exists('multivalue', $mapping)) {
            return true;
        }

        return false;
    }

    private function setLanguageType($fieldName)
    {
        $substrings = explode('.', $fieldName);
        $lang = $substrings[count($substrings) - 2];

        if (in_array($lang, $this->lang_codes)) {
            return 'text_'.$lang;
        }
        if ('pt_BR' === $lang) {
            return 'text_pt';
        }

        return 'text_en';
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

    private function setAnalyzers()
    {
        foreach ($this->config['index']['configuration']['analysis']['analyzer'] as $key => $analyzer) {
            $charFilters = [];
            $filters = [];

            foreach ($this->config['index']['configuration']['analysis']['char_filter'] as $charFilter) {
                array_push($charFilters, $charFilter);
            }

            foreach ($this->config['index']['configuration']['analysis']['analyzer'][$key]['filter'] as $filter) {
                array_push($filters, $this->config['index']['configuration']['analysis']['filter'][$filter]);
            }

            if (sfConfig::get('app_diacritics')) {
                $diacritics = $this->loadDiacriticsMappings();
                array_push($charFilters, [
                    'class' => 'solr.MappingCharFilterFactory',
                    'mapping' => $diacritics[0],
                ]);
            }

            $analyzerParams = [
                'name' => $key,
                'class' => 'solr.TextField',
                'analyzer' => [
                    'tokenizer' => ['class' => $analyzer['tokenizer']],
                    'charFilters' => $charFilters,
                    'filters' => $filters,
                ],
            ];

            $this->client->replaceFieldType($analyzerParams);
        }
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
