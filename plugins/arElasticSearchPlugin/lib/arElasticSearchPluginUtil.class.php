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
 * arElasticSearchPluginUtil.
 */
class arElasticSearchPluginUtil
{
    /**
     * Scroll per-page limit to use for bulk queries.
     */
    public const SCROLL_SIZE = 1000;

    public static function convertDate($date)
    {
        if (is_null($date)) {
            return;
        }

        if ($date instanceof DateTime) {
            $value = $date->format('Y-m-d\TH:i:s\Z');
        } else {
            $value = \Elastica\Util::convertDate($date);
        }

        return $value;
    }

    /**
     * Given a date string in the format YYYY-MM-DD, if either MM or DD is
     * set to 00 (such as when indexing a MySQL date with *only* the year filled
     * in), fill in the blank MM or DD with 01s. e.g. 2014-00-00 -> 2014-01-01.
     *
     * @param  string  date  The date string
     * @param  bool  endDate  If this is set to true, use 12-31 instead
     * @param mixed $date
     * @param mixed $endDate
     *
     * @return mixed a string indicating the normalized date in YYYY-MM-DD format,
     *               otherwise null indicating an invalid date string was given
     */
    public static function normalizeDateWithoutMonthOrDay($date, $endDate = false)
    {
        if (!strlen($date)) {
            return null;
        }

        $dateParts = explode('-', $date);

        if (3 !== count($dateParts)) {
            throw new sfException("Invalid date string given: {$date}. Must be in format YYYY-MM-DD");
        }

        list($year, $month, $day) = $dateParts;

        // Invalid year. Return null now since cal_days_in_month will fail
        // with year 0000. See #8796
        if (0 === (int) $year) {
            return null;
        }

        if (0 === (int) $month) {
            $month = $endDate ? '12' : '01';
        }

        if (0 === (int) $day) {
            $day = $endDate ? cal_days_in_month(CAL_GREGORIAN, $month, $year) : '01';
        }

        return implode('-', [$year, $month, $day]);
    }

    /**
     * Gets all string fields for a given index type, removing those hidden for public users
     * and those included in the except array. Returns a key/value array with the field names
     * as key and the boost as value, i18n fields will contain "%s" as culture placeholder, which
     * will need to be replaced/extended with the required cultures before query.
     *
     * Tried to add the result fields to the cache but APC (our default caching engine) uses separate
     * memory spaces for web/cli and the cached fields can't be removed in arSearchPopulateTask
     *
     * @param mixed $indexType
     * @param mixed $except
     */
    public static function getAllFields($indexType, $except = [])
    {
        // Load ES mappings
        $mappings = arElasticSearchPlugin::loadMappings()->asArray();

        if (!isset($mappings[$indexType])) {
            throw new sfException('Unrecognized index type: '.$indexType);
        }

        $i18nIncludeInAll = null;

        if ('informationObject' === $indexType) {
            $i18nIncludeInAll = $mappings[$indexType]['_attributes']['i18nIncludeInAll'];
        }

        // Get all string fields included in _all for the index type
        $allFields = self::getAllObjectStringFields(
            $indexType,
            $mappings[$indexType],
            $prefix = '',
            false,
            $i18nIncludeInAll
        );

        // Remove fields in except (use array_values() because array_diff() adds keys)
        if (count($except) > 0) {
            $allFields = array_values(array_diff($allFields, $except));
        }

        // Check information object hidden fields for unauthenticated users
        if ('informationObject' == $indexType && !sfContext::getInstance()->user->isAuthenticated()) {
            // Remove hidden fields from ES mapping fields (use array_values() because array_diff() adds keys)
            $allFields = array_values(array_diff($allFields, self::getHiddenFields()));
        }

        return self::setBoostValues($indexType, $allFields);
    }

    /**
     * Expands i18n field names into various specified cultures.
     *
     * @param array $fields   Which fields to expand. For example, 'i18n.%s.title' will expand to 'i18n.en.title',
     *                        'i18n.fr.title', 'i18n.es.title', etc.
     * @param array $cultures An array specifying which cultures to expand to. If not specified, we look up which
     *                        cultures are active in AtoM and go off that.
     */
    public static function getI18nFieldNames($fields, $cultures = null)
    {
        // Get all available cultures if $cultures isn't set
        if (empty($cultures)) {
            $cultures = sfConfig::get('app_i18n_languages');
        }

        // Make sure fields is an array
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $i18nFieldNames = [];

        // Format fields
        foreach ($cultures as $culture) {
            foreach ($fields as $field) {
                $i18nFieldNames[] = sprintf($field, $culture);
            }
        }

        return $i18nFieldNames;
    }

    /**
     * Generate a query string query.
     *
     * @param string $query    unescaped search term
     * @param array  $fields   fields to search (including culture if needed)
     * @param string $operator default query operator (AND/OR), default: AND
     *
     * @return \Elastica\Query\QueryString the generated query string query
     */
    public static function generateQueryString(
        $query, $fields, $operator = 'AND'
    ) {
        $queryString = new \Elastica\Query\QueryString(self::escapeTerm($query));
        $queryString->setDefaultOperator($operator);
        $queryString->setFields(self::getBoostedSearchFields($fields));
        $queryString->setAnalyzer(
            arElasticSearchMapping::getAnalyzer(
                sfContext::getInstance()->user->getCulture()
            )
        );

        return $queryString;
    }

    public static function getBoostedSearchFields($fields)
    {
        $searchFields = [];
        $cultures = sfConfig::get('app_i18n_languages');

        foreach ($fields as $field => $boost) {
            if (1 != $boost) {
                $field .= '^'.$boost;
            }

            if (false !== strpos($field, '%s')) {
                foreach ($cultures as $culture) {
                    $searchFields[] = sprintf($field, $culture);
                }
            } else {
                $searchFields[] = $field;
            }
        }

        return $searchFields;
    }

    // Gets all premis data related to an information object
    public static function getPremisData($ioId, $conn)
    {
        $premisData = [];

        $sql = 'SELECT *
            FROM '.QubitPremisObject::TABLE_NAME.' premis
            WHERE premis.information_object_id = ?';

        $statement = $conn->prepare($sql);
        $statement->execute([$ioId]);
        $row = $statement->fetch();

        // Return if no results found
        if (empty($row)) {
            return;
        }

        foreach ($row as $field => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($field) {
                case 'last_modified':
                    $premisData['lastModified'] = arElasticSearchPluginUtil::convertDate($value);

                    break;

                case 'date_ingested':
                    $premisData['dateIngested'] = arElasticSearchPluginUtil::convertDate($value);

                    break;

                case 'mime_type':
                    $premisData['mimeType'] = $value;

                    break;

                case 'size':
                    $premisData['size'] = $value;

                    break;

                case 'filename':
                    $premisData['filename'] = $value;

                    break;

                case 'puid':
                    $premisData['puid'] = $value;

                    break;
            }
        }

        $sql = 'SELECT property.name, i18n.value
            FROM '.QubitProperty::TABLE_NAME.' property
            JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
            ON property.id = i18n.id
            WHERE property.scope = "premisData"
            AND property.source_culture = i18n.culture
            AND property.object_id = ?';

        $statement = $conn->prepare($sql);
        $statement->execute([$ioId]);

        foreach ($statement->fetchAll(PDO::FETCH_OBJ) as $property) {
            $value = unserialize($property->value);

            switch ($property->name) {
                case 'fitsAudio':
                    $premisData['audio'] = $value;

                    break;

                case 'fitsDocument':
                    $premisData['document'] = $value;

                    break;

                case 'fitsText':
                    $premisData['text'] = $value;

                    break;

                case 'mediainfoGeneralTrack':
                    $premisData['mediainfo']['generalTracks'][] = $value;

                    break;

                case 'mediainfoVideoTrack':
                    $premisData['mediainfo']['videoTracks'][] = $value;

                    break;

                case 'mediainfoAudioTrack':
                    $premisData['mediainfo']['audioTracks'][] = $value;

                    break;

                case 'format':
                    $premisData['format'] = $value;

                    break;

                case 'formatIdentificationEvent':
                    $premisData['formatIdentificationEvent'] = $value;

                    break;

                case 'otherEvent':
                    $premisData['otherEvents'][] = $value;

                    break;

                case 'agent':
                    $premisData['agents'][] = $value;

                    break;
            }
        }

        if (!empty($premisData)) {
            return $premisData;
        }
    }

    /**
     * Escapes the special chars specified in the "escape_queries" setting.
     *
     * @param string $term Query term to escape
     *
     * @return string Escaped query term
     */
    public static function escapeTerm($term)
    {
        $specialChars = trim(sfConfig::get('app_escape_queries', ''));

        // Return term directly if the setting is empty
        if (empty($specialChars)) {
            return $term;
        }

        // Split into array removing whitespaces
        $specialChars = preg_split('/\s*,\s*/', $specialChars);

        // Escaping \ has to be first
        if (in_array('\\', $specialChars)) {
            $term = str_replace('\\', '\\\\', $term);
        }

        foreach ($specialChars as $char) {
            // Ignore empty chars and \
            if (empty($char) || '\\' == $char) {
                continue;
            }

            $term = str_replace($char, '\\'.$char, $term);
        }

        return $term;
    }

    /**
     * Scroll through search, returning hit IDs as an array.
     *
     * Scrolled queries are a way to return search result sets larger than the limit
     * index.max_result_window sets. Scrolled results, however, by default expire
     * fairly quickly to free Elasticsearch resources. Returning these results as an
     * array allows the results to be processed even after the scroll result expires.
     *
     * @param \Elastica\Search $search Search to cache
     *
     * @return array Array of IDs
     */
    public static function getScrolledSearchResultIdentifiers($search)
    {
        $hitIds = [];

        // Create scroll of search
        $scroll = new \Elastica\Scroll($search);

        // Scroll and add hit IDs to array
        foreach ($scroll as $resultSet) {
            foreach ($resultSet as $hit) {
                array_push($hitIds, $hit->getId());
            }
        }

        return $hitIds;
    }

    /**
     * Gets all string fields included in _all from a mapping object array and cultures.
     *
     * This function will be called recursively on foreign types and nested fields.
     *
     * @param string $rootIndexType The current, top level index type we're adding fields to, e.g. "informationObject".
     *
     *                               Note that since we recursively call getAllObjectStringFields to get foreign type
     *                               fields, this value may not be the "current" index being parsed, e.g. when adding
     *                               creators.name actor fields inside informationObject.
     * @param array  $object           an array containing the current object mappings
     * @param string $prefix           The current prefix for the prop name, e.g. "informationObject." in "informationObject.slug"
     * @param bool   $foreignType      Whether or not this field in question is being parsed for a foreign type,
     *                                 e.g. inside informationObject.creators
     * @param array  $i18nIncludeInAll A list of i18n fields to be allowed when searching _all
     */
    protected static function getAllObjectStringFields(
        $rootIndexType,
        $object,
        $prefix,
        $foreignType = false,
        $i18nIncludeInAll = null
    ) {
        $fields = [];

        if (isset($object['properties'])) {
            foreach ($object['properties'] as $propertyName => $propertyProperties) {
                // Get i18n fields, they're always included in _all
                if ('i18n' == $propertyName) {
                    // Get the fields from a single culture and format them with
                    // 'i18n.%s.' to set the required cultures at query time.
                    foreach ($propertyProperties['properties'] as $culture => $cultureProperties) {
                        if ('languages' == $culture) {
                            continue;
                        }

                        foreach ($cultureProperties['properties'] as $fieldName => $fieldProperties) {
                            self::handleI18nStringFields(
                                $rootIndexType,
                                $fields,
                                $prefix,
                                $fieldName,
                                $foreignType,
                                $i18nIncludeInAll
                            );
                        }

                        break;
                    }
                }
                // Get nested objects fields
                elseif (isset($propertyProperties['type']) && 'object' == $propertyProperties['type']) {
                    $nestedFields = self::getAllObjectStringFields(
                        $rootIndexType,
                        $object['properties'][$propertyName],
                        $prefix.$propertyName.'.'
                    );

                    $fields = array_merge($fields, $nestedFields);
                }
                // Get foreign objects fields (couldn't find a better way than checking the dynamic property)
                elseif (isset($propertyProperties['dynamic'])) {
                    $foreignObjectFields = self::getAllObjectStringFields(
                        $rootIndexType,
                        $object['properties'][$propertyName],
                        $prefix.$propertyName.'.',
                        true,
                        $i18nIncludeInAll
                    );

                    $fields = array_merge($fields, $foreignObjectFields);
                }
                // Get string fields included in _all
                elseif (
                    (
                        isset($propertyProperties['copy_to'])
                        && ('_all' == $propertyProperties['copy_to'])
                    ) && (
                        isset($propertyProperties['type'])
                        && 'text' == $propertyProperties['type']
                    )
                ) {
                    self::handleNonI18nStringFields($rootIndexType, $fields, $prefix, $propertyName, $foreignType);
                }
            }
        }

        return $fields;
    }

    /**
     * Retrieve the default template type given a specified ES index type.
     *
     * @param mixed $indexType
     *
     * @return string The default template (e.g. isad)
     */
    private static function getTemplate($indexType)
    {
        switch ($indexType) {
            case 'informationObject':
                $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template');
                if (isset($infoObjectTemplate)) {
                    return $infoObjectTemplate->getValue(['sourceCulture' => true]);
                }

            // TODO: Other index types (actor, term, etc)
        }
    }

    /**
     * Retrieve a list of fields that are set to hidden in the visible elements settings.
     *
     * @return array an array specifying which fields are to be hidden from anonymous users
     */
    private static function getHiddenFields()
    {
        // Create array with relations (hidden field => ES mapping field) for the actual template
        $relations = [];

        if (null !== $template = self::getTemplate('informationObject')) {
            switch ($template) {
                case 'isad':
                    $relations = [
                        'isad_archival_history' => 'i18n.%s.archivalHistory',
                        'isad_immediate_source' => 'i18n.%s.acquisition',
                        'isad_appraisal_destruction' => 'i18n.%s.appraisal',
                        'isad_notes' => '',
                        'isad_physical_condition' => 'i18n.%s.physicalCharacteristics',
                        'isad_control_description_identifier' => '',
                        'isad_control_institution_identifier' => 'i18n.%s.institutionResponsibleIdentifier',
                        'isad_control_rules_conventions' => 'i18n.%s.rules',
                        'isad_control_status' => '',
                        'isad_control_level_of_detail' => '',
                        'isad_control_dates' => 'i18n.%s.revisionHistory',
                        'isad_control_languages' => '',
                        'isad_control_scripts' => '',
                        'isad_control_sources' => 'i18n.%s.sources',
                        'isad_control_archivists_notes' => '',
                    ];

                    break;

                case 'rad':
                    $relations = [
                        'rad_archival_history' => 'i18n.%s.archivalHistory',
                        'rad_physical_condition' => 'i18n.%s.physicalCharacteristics',
                        'rad_immediate_source' => 'i18n.%s.acquisition',
                        'rad_general_notes' => '',
                        'rad_conservation_notes' => '',
                        'rad_control_description_identifier' => '',
                        'rad_control_institution_identifier' => 'i18n.%s.institutionResponsibleIdentifier',
                        'rad_control_rules_conventions' => 'i18n.%s.rules',
                        'rad_control_status' => '',
                        'rad_control_level_of_detail' => '',
                        'rad_control_dates' => 'i18n.%s.revisionHistory',
                        'rad_control_language' => '',
                        'rad_control_script' => '',
                        'rad_control_sources' => 'i18n.%s.sources',
                    ];

                    break;
                // TODO: Other templates (dacs, dc, isaar, etc)
            }
        }

        // Obtain hidden fields
        $hiddenFields = [];

        foreach (QubitSetting::getByScope('element_visibility') as $setting) {
            if (
                !(bool) $setting->getValue(['sourceCulture' => true])
                && isset($relations[$setting->name])
                && '' != $relations[$setting->name]
            ) {
                $hiddenFields[] = $relations[$setting->name];
            }
        }

        return $hiddenFields;
    }

    /**
     * Based on indexType, set the boost values for each field.
     *
     * @param string $indexType which index type we're setting the field boost values for
     * @param array  $fields    the fields we're setting the boost values on
     *
     * @return array key/value array with fieldname/boost
     */
    private static function setBoostValues($indexType, $fields)
    {
        $boost = $boostedFields = [];

        switch ($indexType) {
            case 'informationObject':
                $boost = [
                    'i18n.%s.title' => 10,
                    'creators.i18n.%s.authorizedFormOfName' => 6,
                    'identifier' => 5,
                    'subjects.i18n.%s.name' => 5,
                    'i18n.%s.scopeAndContent' => 5,
                    'names.i18n.%s.authorizedFormOfName' => 3,
                    'places.i18n.%s.name' => 3,
                ];

                break;
        }

        foreach ($fields as $field) {
            if (isset($boost[$field])) {
                $boostedFields[$field] = $boost[$field];
            } else {
                $boostedFields[$field] = 1;
            }
        }

        return $boostedFields;
    }

    /**
     * Check whether an i18n field should be included in the list of fields for an _all search.
     *
     * @param string $prefix           The current prefix for the field name, e.g. "creators." for "creators.name"
     * @param string $fieldName        The current field name, e.g. "name" in "creators.name"
     * @param array  $i18nIncludeInAll A list of i18n fields to be allowed when searching _all
     *
     * @return bool true if we should include this field in the _all search, false otherwise
     */
    private static function checkI18nIncludeInAll($prefix, $fieldName, $i18nIncludeInAll)
    {
        if (!$i18nIncludeInAll) {
            return; // Return and skip this check, no i18nIncludeInAll _attribute present.
        }

        return in_array($prefix.$fieldName, $i18nIncludeInAll);
    }

    /**
     * Handle adding i18n fields to our fields list. This is a helper function for getAllObjectStringFields().
     *
     * Depending on the index type, there may be special rules we need to check before adding i18n fields to
     * our fields list.
     *
     * @param string $rootIndexType The current, top level index type we're adding fields to, e.g. "informationObject".
     *
     *                               Note that since we recursively call getAllObjectStringFields to get foreign type
     *                               fields, this value may not be the "current" index being parsed, e.g. when adding
     *                               creators.name actor fields inside informationObject.
     * @param array  &$fields          A reference to our list of fields we're searching over with our _all query
     * @param string $prefix           The current prefix for the field name, e.g. "creators." for "creators.name"
     * @param string $fieldName        The current field name, e.g. "name" in "creators.name"
     * @param bool   $foreignType      Whether or not this field in question is being parsed for a foreign type,
     *                                 e.g. inside informationObject.creators
     * @param array  $i18nIncludeInAll A list of i18n fields to be allowed when searching _all
     */
    private static function handleI18nStringFields(
        $rootIndexType,
        &$fields,
        $prefix,
        $fieldName,
        $foreignType,
        $i18nIncludeInAll
    ) {
        // We may add special rules for other index types in the future
        switch ($rootIndexType) {
            case 'informationObject':
                if ($foreignType && false === self::checkI18nIncludeInAll($prefix, $fieldName, $i18nIncludeInAll)) {
                    return; // Skip field
                }

                break;
        }

        // Concatenate object name ($prefix), culture placeholder and field name
        $fields[] = $prefix.'i18n.%s.'.$fieldName;
    }

    /**
     * Handle adding non-i18n string properties to our fields list. This is a helper function for
     * getAllObjectStringFields().
     *
     * Depending on the index type, there may be special rules we need to check before adding string fields to
     * our fields list.
     *
     * @param string $rootIndexType The current, top level index type we're adding fields to, e.g. "informationObject".
     *
     *                               Note that since we recursively call getAllObjectStringFields to get foreign type
     *                               fields, this value may not be the "current" index being parsed, e.g. when adding
     *                               creators.name actor fields inside informationObject.
     * @param array  &$fields      A reference to our list of fields we're searching over with our _all query
     * @param string $prefix       The current prefix for the prop name, e.g. "informationObject." in "informationObject.slug"
     * @param string $propertyName The current property name, e.g. "slug" in "informationObject.slug"
     * @param bool   $foreignType  Whether or not this field in question is being parsed for a foreign type,
     *                             e.g. inside informationObject.creators
     */
    private static function handleNonI18nStringFields($rootIndexType, &$fields, $prefix, $propertyName, $foreignType)
    {
        // We may add special rules for other index types in the future
        switch ($rootIndexType) {
            case 'informationObject':
                if ($foreignType) {
                    return; // Skip all foreign type non-i18n string fields for info objects
                }

                break;
        }

        // Concatenate object name ($prefix) and field name
        $fields[] = $prefix.$propertyName;
    }
}
