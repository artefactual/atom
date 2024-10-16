<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class arElasticSearchMapping
{
    /**
     * Inner objects.
     *
     * @return array
     */
    protected $nestedTypes;

    /**
     * Associative array that maps iso639-1 language codes to the different
     * analyzers that have been defined in search.yml for each stopword list
     * provided by Elasticsearch.
     */
    private static $analyzers = [
        'ar' => 'arabic',
        'hy' => 'armenian',
        'ba' => 'basque',
        'br' => 'brazilian',
        'bg' => 'bulgarian',
        'ca' => 'catalan',
        'cz' => 'czech',
        'da' => 'danish',
        'nl' => 'dutch',
        'en' => 'english',
        'fi' => 'finnish',
        'fr' => 'french',
        'gl' => 'galician',
        'ge' => 'german',
        'el' => 'greek',
        'hi' => 'hindi',
        'hu' => 'hungarian',
        'id' => 'indonesian',
        'it' => 'italian',
        'no' => 'norwegian',
        'fa' => 'persian',
        'pt' => 'portuguese',
        'ro' => 'romanian',
        'ru' => 'russian',
        'es' => 'spanish',
        'sv' => 'swedish',
        'tr' => 'turkish',
    ];

    /**
     * Dumps schema as array.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->mapping;
    }

    /**
     * Load mapping from array.
     *
     * @param array $mapping_array
     */
    public function loadArray($mapping_array)
    {
        if (is_array($mapping_array) && !empty($mapping_array)) {
            if (count($mapping_array) > 1) {
                throw new sfException('A mapping.yml must only contain 1 entry.');
            }

            // Direct access to mapping
            $this->mapping = $mapping_array['mapping'];

            $this->camelizeFieldNames();

            $this->fixYamlShorthands();

            $this->excludeNestedOnlyTypes();
        }
    }

    /**
     * Load mapping from YAML file.
     *
     * @param string $file
     */
    public function loadYAML($file)
    {
        $mapping_array = sfYaml::load($file);

        if (!is_array($mapping_array)) {
            return; // No defined schema here, skipping
        }

        $this->loadArray($mapping_array);
    }

    /**
     * Clean YAML shorthands recursively.
     *
     * We have some special YAML properties in mapping.yml that we only use internally
     * to indicate foreign types, special attributes, etc. This method will remove those
     * from the mappings array, which is necessary since when we generate our ES schema
     * we don't want to send those special properties in the mapping.
     *
     * @param array mapping  A reference to our ES YAML mappings
     * @param null|mixed $mapping
     */
    public function cleanYamlShorthands(&$mapping = null)
    {
        // If no parameter is passed, $this->mapping will be used
        if (null === $mapping) {
            $mapping = &$this->mapping;
        }

        foreach ($mapping as $key => &$value) {
            switch ($key) {
                case '_attributes':
                case '_foreign_types':
                case '_partial_foreign_types':
                case '_i18nFields':
                    unset($mapping[$key]);

                    break;

                default:
                    if (is_array($value)) {
                        $this->cleanYamlShorthands($value);
                    }

                    break;
            }
        }
    }

    /*
     * Given a class name (eg. Repository or QubitRepostiroy), returns
     * an array of i18n fields
     */
    public static function getI18nFields($class)
    {
        // Use table maps to find existing i18n columns
        $className = str_replace('Qubit', '', $class).'I18nTableMap';

        // Ignore models without i18n table that will include i18nExtra (donors)
        if (!class_exists($className)) {
            return [];
        }

        $map = new $className();

        $fields = [];
        foreach ($map->getColumns() as $column) {
            if (!$column->isPrimaryKey() && !$column->isForeignKey()) {
                $colName = $column->getPhpName();

                $fields[] = $colName;
            }
        }

        return $fields;
    }

    public static function getAnalyzer($culture)
    {
        if (isset(self::$analyzers[$culture])) {
            return self::$analyzers[$culture];
        }

        // Default to standard analyzer
        return 'standard';
    }

    /**
     * Camelize field names by creating and unsetting array items recursively.
     * Only properties are camelized, other attributes are ignored.
     *
     * @param null|mixed $mapping
     */
    protected function camelizeFieldNames(&$mapping = null)
    {
        // If no parameter is passed, $this->mapping will be used
        if (null === $mapping) {
            $mapping = &$this->mapping;
        }

        foreach ($mapping as $key => &$value) {
            $camelized = lcfirst(sfInflector::camelize($key));

            // Rename only if the camelized version is different
            // Also, omit first recursion (type names)
            if ($camelized != $key) {
                // Create new item with the camelized version of the key
                $mapping[$camelized] = $value;

                // Drop the old item from the array
                unset($mapping[$key]);
            }

            // Recurse this function over narrow items if available
            if (isset($value['properties'])) {
                $this->camelizeFieldNames($value['properties']);
            }
        }
    }

    /**
     * Fixes YAML shorthands.
     */
    protected function fixYamlShorthands()
    {
        // First, process special attributes
        foreach ($this->mapping as $typeName => &$typeProperties) {
            $this->processPropertyAttributes($typeName, $typeProperties);
        }

        // Next iteration to embed partial foreing types
        foreach ($this->mapping as $typeName => &$typeProperties) {
            $this->processPartialForeignTypes($typeProperties);
        }

        // Next iteration to embed nested types
        foreach ($this->mapping as $typeName => &$typeProperties) {
            $this->processForeignTypes($typeProperties);
        }
    }

    /**
     * Given a mapping, it parses its special attributes and update it accordingly.
     *
     * @param mixed $typeName
     */
    protected function processPropertyAttributes($typeName, array &$typeProperties)
    {
        // Stop execution if any special attribute was set
        if (!isset($typeProperties['_attributes'])) {
            return;
        }

        // Look for special attributes like i18n or timestamp and update the
        // mapping accordingly. For example, 'timestamp' adds the created_at
        // and updated_at fields each time is used.
        foreach ($typeProperties['_attributes'] as $attributeName => $attributeValue) {
            switch ($attributeName) {
                case 'i18n':
                    $languages = sfConfig::get('app_i18n_languages');
                    if (1 > count($languages)) {
                        throw new sfException('No i18n_languages in database settings.');
                    }

                    $this->setIfNotSet($typeProperties['properties'], 'sourceCulture', ['type' => 'keyword']);

                    // We are using the same mapping for all the i18n fields
                    $nestedI18nFields = [];
                    foreach ($this->getI18nFields(lcfirst(sfInflector::camelize($typeName))) as $fieldName) {
                        $nestedI18nFields[$fieldName] = $this->getI18nFieldMapping($fieldName);
                    }

                    if (isset($typeProperties['_attributes']['i18nExtra'])) {
                        foreach ($typeProperties['_attributes']['i18nExtra'] as $extraClass) {
                            foreach ($this->getI18nFields(lcfirst(sfInflector::camelize($extraClass))) as $fieldName) {
                                $nestedI18nFields[$fieldName] = $this->getI18nFieldMapping($fieldName);
                            }
                        }
                    }

                    if (isset($typeProperties['_attributes']['autocompleteFields'])) {
                        foreach ($typeProperties['_attributes']['autocompleteFields'] as $item) {
                            $nestedI18nFields[$item]['fields']['autocomplete'] = [
                                'type' => 'text',
                                'analyzer' => 'autocomplete',
                                'search_analyzer' => 'standard',
                                'store' => 'true',
                                'term_vector' => 'with_positions_offsets',
                            ];
                        }
                    }

                    if (isset($typeProperties['_attributes']['rawFields'])) {
                        foreach ($typeProperties['_attributes']['rawFields'] as $item) {
                            $nestedI18nFields[$item]['fields']['untouched'] = ['type' => 'keyword'];
                        }
                    }

                    $nestedI18nFields = $this->addSortFields($nestedI18nFields, $typeProperties);

                    // i18n documents (one per culture)
                    $nestedI18nObjects = $this->getNestedI18nObjects($languages, $nestedI18nFields);

                    // Main i18n object
                    $this->setIfNotSet($typeProperties['properties'], 'i18n', [
                        'type' => 'object',
                        'dynamic' => 'strict',
                        'include_in_root' => true,
                        'properties' => $nestedI18nObjects,
                    ]);

                    break;

                case 'timestamp':
                    $this->setIfNotSet($typeProperties['properties'], 'createdAt', ['type' => 'date']);
                    $this->setIfNotSet($typeProperties['properties'], 'updatedAt', ['type' => 'date']);

                    break;
            }
        }
    }

    /**
     * Given a mapping, adds other objects within it.
     */
    protected function processForeignTypes(array &$typeProperties)
    {
        // Stop execution if any foreign type was assigned
        if (!isset($typeProperties['_foreign_types'])) {
            return;
        }

        foreach ($typeProperties['_foreign_types'] as $fieldName => $foreignTypeName) {
            $fieldNameCamelized = lcfirst(sfInflector::camelize($fieldName));
            $foreignTypeNameCamelized = lcfirst(sfInflector::camelize($foreignTypeName));

            if (!isset($this->mapping[$foreignTypeNameCamelized])) {
                throw new sfException("{$foreignTypeName} could not be found within the mappings.");
            }

            $mapping = $this->mapping[$foreignTypeNameCamelized];

            // Add id of the foreign resource
            $mapping['properties']['id'] = ['type' => 'integer'];

            $typeProperties['properties'][$fieldNameCamelized] = $mapping;
        }
    }

    /**
     * Given a mapping, adds partial foreing objects within it.
     */
    protected function processPartialForeignTypes(array &$typeProperties)
    {
        // Stop execution if any partial foreign type was assigned
        if (!isset($typeProperties['_partial_foreign_types'])) {
            return;
        }

        foreach ($typeProperties['_partial_foreign_types'] as $fieldName => $mapping) {
            $fieldNameCamelized = lcfirst(sfInflector::camelize($fieldName));

            if (isset($mapping['_i18nFields'])) {
                $languages = sfConfig::get('app_i18n_languages');
                if (1 > count($languages)) {
                    throw new sfException('The database settings do not contain any languages.');
                }

                // Add source culture property
                $this->setIfNotSet($mapping['properties'], 'sourceCulture', ['type' => 'keyword']);

                $nestedI18nFields = [];
                foreach ($mapping['_i18nFields'] as $i18nFieldName) {
                    $i18nFieldNameCamelized = lcfirst(sfInflector::camelize($i18nFieldName));

                    // Create mapping for i18n field
                    $nestedI18nFields[$i18nFieldNameCamelized] = $this->getI18nFieldMapping($i18nFieldNameCamelized);
                }

                // Add 'untouched' when _rawFields specified in _partial_foreign_types section
                if (isset($mapping['_rawFields'])) {
                    foreach ($mapping['_rawFields'] as $item) {
                        $nestedI18nFields[$item]['fields']['untouched'] = ['type' => 'keyword'];
                    }
                    unset($mapping['_rawFields']);
                }

                // i18n documents (one per culture)
                $nestedI18nObjects = $this->getNestedI18nObjects($languages, $nestedI18nFields);

                // Main i18n object
                $this->setIfNotSet($mapping['properties'], 'i18n', [
                    'type' => 'object',
                    'dynamic' => 'strict',
                    'include_in_root' => true,
                    'properties' => $nestedI18nObjects,
                ]);
            }

            if (isset($mapping['_foreign_types'])) {
                $this->processForeignTypes($mapping);
            }

            // Add id of the partial foreign resource
            $mapping['properties']['id'] = ['type' => 'integer'];

            $typeProperties['properties'][$fieldNameCamelized] = $mapping;
        }
    }

    /**
     * Exclude nested types if there are not root objects using them.
     */
    protected function excludeNestedOnlyTypes()
    {
        // Iterate over types (actor, information_object, ...)
        foreach ($this->mapping as $typeName => $typeProperties) {
            // Pass if nested_only is not set
            if (!isset($typeProperties['_attributes']['nested_only'])) {
                continue;
            }

            unset($this->mapping[$typeName]);
        }
    }

    /**
     * Sets entry if not set.
     *
     * @param string $entry
     * @param string $key
     * @param string $value
     */
    protected function setIfNotSet(&$entry, $key, $value)
    {
        if (!isset($entry[$key])) {
            $entry[$key] = $value;
        }
    }

    protected function getI18nFieldMapping($fieldName)
    {
        return [
            'type' => 'text',
            'copy_to' => '_all',
        ];
    }

    protected function getNestedI18nObjects($languages, $nestedI18nFields)
    {
        $mapping = [];
        foreach ($languages as $culture) {
            // Iterate each field and assign a custom standard analyzer (e.g.
            // std_french in search.yml) based in the language being used. The default
            // analyzer is standard, which does not provide a stopwords list.
            foreach ($nestedI18nFields as $fn => &$fv) {
                $fv['analyzer'] = self::getAnalyzer($culture);
            }
            unset($fv);

            $mapping[$culture] = [
                'type' => 'object',
                'dynamic' => 'strict',
                'include_in_parent' => false,
                'properties' => $nestedI18nFields,
            ];
        }

        // Create a list of languages for aggregations
        $mapping['languages'] = ['type' => 'keyword'];

        return $mapping;
    }

    /**
     * Add "alphasort" Elasticsearch fields.
     *
     * Add i18n "alphasort" keyword field that is lowercase, has punctation
     * stripped, and is ASCII folded to allow more natural alphabetic sorting
     *
     * @param mixed $nestedI18nFields
     * @param mixed $typeProperties
     */
    protected function addSortFields($nestedI18nFields, $typeProperties)
    {
        if (!isset($typeProperties['_attributes']['sortFields'])) {
            return $nestedI18nFields;
        }

        foreach ($typeProperties['_attributes']['sortFields'] as $item) {
            $nestedI18nFields[$item]['fields']['alphasort'] = [
                'type' => 'keyword',
                'normalizer' => 'alphasort',
            ];
        }

        return $nestedI18nFields;
    }
}
