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
     * Constructor.
     */
    public function __construct(array $options = [])
    {
        parent::__construct();

        $this->config = arSolrPluginConfiguration::$config;

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

        $url = $this->solrBaseUrl.'/solr/'.$this->solrClientOptions['collection'].'/update/json/docs';
        arSolrPlugin::makeHttpRequest($url, 'POST', json_encode([
            $type => $data,
        ]));
    }

    public function getSolrUrl()
    {
        return $this->solrBaseUrl;
    }

    public function getSolrCollection()
    {
        return $this->solrClientOptions['collection'];
    }

    public function search($query, $type)
    {
        $url = $this->getSolrUrl().'/solr/'.$this->getSolrCollection().'/query';
        $response = arSolrPlugin::makeHttpRequest($url, 'POST', json_encode($query->getQueryParams()));

        return new arSolrResultSet($response);
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

            $this->log('Creating Solr Collection');
            $url = $this->solrBaseUrl.'/solr/admin/collections?action=CREATE&name='.$this->solrClientOptions['collection'].'&numShards=2&replicationFactor=1&wt=json';
            arSolrPlugin::makeHttpRequest($url);

            $topLevelProperties = [];
            $subProperties = [];

            $url = $this->solrBaseUrl.'/api/collections/'.$this->solrClientOptions['collection'].'/config/';
            $updateDefaultHandler = '{"update-requesthandler": {"name": "/select", "class": "solr.SearchHandler", "defaults": {"echoParams": "explicit"}}}';
            arSolrPlugin::makeHttpRequest($url, 'POST', $updateDefaultHandler);

            // Load and normalize mappings
            $this->loadAndNormalizeMappings();

            // Iterate over types (actor, informationobject, ...)
            foreach ($this->mappings as $typeName => $typeProperties) {
                $typeName = 'Qubit'.sfInflector::camelize($typeName);
                array_push($topLevelProperties, $this->getFieldQuery($typeName, '_nest_path_', true));

                $this->addSubProperties($typeProperties['properties'], $subProperties, $typeName, $typeProperties['properties']);
            }

            $addQuery = ['add-field' => $topLevelProperties];
            $this->addFieldsToType(json_encode($addQuery));
            $addQuery = ['add-field' => $subProperties];
            $this->addFieldsToType(json_encode($addQuery));

            $this->addAutoCompleteFields();
        }
    }

    private function addAutoCompleteFields()
    {
        // list should also include QubitInformationObject.referenceCode but
        // since this field does not include a language code, it is added to
        // $addCopyField manually
        $autocompleteFields = [
            'QubitRepository.i18n.en.authorizedFormOfName',
            'QubitInformationObject.aip.type.i18n.en.name',
            'QubitInformationObject.i18n.en.title',
            'QubitActor.i18n.en.authorizedFormOfName',
            'QubitActor.places.i18n.en.name',
            'QubitActor.subjects.i18n.en.name',
            'QubitTerm.i18n.en.name',
            'QubitAip.type.i18n.en.name',
        ];

        foreach ($this->langs as $lang) {
            $addCopyField = '{"add-field":{"name":"autocomplete_'.$lang.'","type":"text_'.$lang.'","stored":true},';
            $copyField = '"add-copy-field":{"source":"QubitInformationObject.referenceCode","dest":"autocomplete_'.$lang.'"},';
            foreach ($autocompleteFields as $field) {
                $field = str_replace('en', $lang, $field);
                $copyField .= '"add-copy-field":{"source":"'.$field.'","dest":"autocomplete_'.$lang.'"},';
            }
            $addCopyField .= substr($copyField, 0, -1).'}';
            $url = $this->solrBaseUrl.'/api/collections/'.$this->solrClientOptions['collection'].'/schema/';
            arSolrPlugin::makeHttpRequest($url, 'POST', $addCopyField);
        }
    }

    private function addAutoCompleteConfigs()
    {
        foreach ($this->langs as $lang) {
            $url = $this->solrBaseUrl.'/api/collections/'.$this->solrClientOptions['collection'].'/config/';
            $addSearchComponent = '{
                "add-searchComponent":{
                    "name":"autocomplete_'.$lang.'",
                    "class":"solr.SuggestComponent",
                    "suggester":{
                        "name":"autocomplete_'.$lang.'",
                        "field":"autocomplete_'.$lang.'",
                        "lookupImpl":"FuzzyLookupFactory",
                        "dictionaryImpl":"DocumentDictionaryFactory",
                        "suggestAnalyzerFieldType":"text_'.$lang.'"
                    }
                }
            }';
            $addRequestHandler = '{
                "add-requestHandler":{
                    "name":"/autocomplete_'.$lang.'",
                    "class":"solr.SearchHandler",
                    "components":["autocomplete_'.$lang.'"],
                    "defaults":{
                        "suggest":true,
                        "suggest.count":5,
                        "suggest.dictionary":"autocomplete_'.$lang.'"
                    }
                }
            }';
            arSolrPlugin::makeHttpRequest($url, 'POST', $addSearchComponent);
            arSolrPlugin::makeHttpRequest($url, 'POST', $addRequestHandler);
        }
    }

    private function addSubProperties($properties, &$propertyFields, $parentType = '', $parentProperties)
    {
        $atomicTypes = ['keyword', 'string', 'text', 'text_general', 'date', 'pdate', 'pdates', 'long', 'plongs', 'integer', 'boolean', 'location'];
        foreach ($properties as $propertyName => $value) {
            $fieldName = $parentType ? "{$parentType}.{$propertyName}" : $propertyName;

            $fields = explode('.', $fieldName);
            $lang = $fields[count($fields) - 2];
            if (in_array($lang, $this->lang_codes) && !in_array($lang, $this->langs)) {
                array_push($this->langs, $lang);
            }

            $i18nIndex = array_search('i18n', $fields);

            if (in_array($value['type'], $atomicTypes)) {
                if ('text' === $value['type']) {
                    $typeName = $this->setLanguageType($fieldName);
                } else {
                    $typeName = $this->setType($value['type']);
                }
                $multiValue = $this->getMultiValue($parentProperties[$fields[$i18nIndex - 2]]['properties']);
                $field = $this->getFieldQuery($fieldName, $typeName, $multiValue?'true':'false', false);
                array_push($propertyFields, $field);
            } elseif ('object' == $value['type']) {
                $multiValue = $this->getMultiValue($parentProperties[$fields[$i18nIndex - 2]]['properties']);
                $field = $this->getFieldQuery($fieldName, '_nest_path_', $multiValue, true);
                array_push($propertyFields, $field);
            }

            if ($value['properties']) {
                $this->addSubProperties($value['properties'], $propertyFields, $fieldName, $parentProperties);
            }
        }
    }

    private function getMultiValue($properties) {
        foreach ($properties as $property) {
            if ($property['type'] != null) {
                if ($property['multivalue']) {
                    return true;
                }
            } else {
                $this->getMultiValue($property);
            }
        }
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
            $filters = [];
            if (sfConfig::get('app_diacritics')) {
                $filters = array_push($filters, ['class' => 'diacritics_lowercase']);
            }

            $charFilters = [];
            foreach ($this->config['index']['configuration']['analysis']['char_filter'] as $charFilter) {
                array_push($charFilters, $charFilter);
            }

            foreach ($this->config['index']['configuration']['analysis']['analyzer'][$key]['filter'] as $filter) {
                array_push($filters, $this->config['index']['configuration']['analysis']['filter'][$filter]);
            }

            $query = ['replace-field-type' => [
                'name' => $key,
                'class' => 'solr.TextField',
                'analyzer' => [
                    'tokenizer' => ['class' => $analyzer['tokenizer']],
                    'charFilters' => $charFilters,
                    'filters' => $filters,
                ],
            ]];

            $url = $this->solrBaseUrl.'/solr/'.$this->solrClientOptions['collection'].'/schema/';
            arSolrPlugin::makeHttpRequest($url, 'POST', json_encode($query));
        }
    }

    private function addFieldsToType($query)
    {
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
