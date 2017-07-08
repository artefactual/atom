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
 * arElasticSearchPluginUtil
 *
 * @package     AccesstoMemory
 * @subpackage  arElasticSearchPlugin
 */
class arElasticSearchPluginUtil
{
  public static function convertDate($date)
  {
    if (is_null($date))
    {
      return;
    }

    if ($date instanceof DateTime)
    {
      $value = $date->format('Y-m-d\TH:i:s\Z');
    }
    else
    {
      $value = \Elastica\Util::convertDate($date);
    }

    return $value;
  }

  /**
   * Given a date string in the format YYYY-MM-DD, if either MM or DD is
   * set to 00 (such as when indexing a MySQL date with *only* the year filled
   * in), fill in the blank MM or DD with 01s. e.g. 2014-00-00 -> 2014-01-01
   *
   * @param  string  date  The date string
   * @param  bool  endDate  If this is set to true, use 12-31 instead
   *
   * @return  mixed  A string indicating the normalized date in YYYY-MM-DD format,
   *                 otherwise null indicating an invalid date string was given.
   */
  public static function normalizeDateWithoutMonthOrDay($date, $endDate = false)
  {
    if (!strlen($date))
    {
      return null;
    }

    $dateParts = explode('-', $date);

    if (count($dateParts) !== 3)
    {
      throw new sfException("Invalid date string given: {$date}. Must be in format YYYY-MM-DD");
    }

    list($year, $month, $day) = $dateParts;

    // Invalid year. Return null now since cal_days_in_month will fail
    // with year 0000. See #8796
    if ((int)$year === 0)
    {
      return null;
    }

    if ((int)$month === 0)
    {
      $month = $endDate ? '12' : '01';
    }

    if ((int)$day === 0)
    {
      $day = $endDate ? cal_days_in_month(CAL_GREGORIAN, $month, $year) : '01';
    }

    return implode('-', array($year, $month, $day));
  }

  /**
   * Get all available language codes that are enabled in the administrator settings.
   *
   * @return array  An array containing the above language codes as strings.
   */
  private static function getAvailableLanguages()
  {
    $cultures = array();
    foreach (QubitSetting::getByScope('i18n_languages') as $setting)
    {
      $cultures[] = $setting->getValue(array('sourceCulture' => true));
    }

    return $cultures;
  }

  /**
   * Retrieve the default template type given a specified ES index type.
   *
   * @return string  The default template (e.g. isad)
   */
  private static function getTemplate($indexType)
  {
    switch ($indexType)
    {
      case 'informationObject':
        $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template');
        if (isset($infoObjectTemplate))
        {
          return $infoObjectTemplate->getValue(array('sourceCulture'=>true));
        }

      // TODO: Other index types (actor, term, etc)
    }
  }

  /**
   * Retrieve a list of fields that are set to hidden in the visible elements settings.
   *
   * @return array  An array specifying which fields are to be hidden from anonymous users.
   */
  private static function getHiddenFields()
  {
    // Create array with relations (hidden field => ES mapping field) for the actual template and cultures
    $relations = array();
    $cultures = array();
    foreach (QubitSetting::getByScope('i18n_languages') as $setting)
    {
      $cultures[] = $setting->getValue(array('sourceCulture' => true));
    }

    if (null !== $template = self::getTemplate('informationObject'))
    {
      switch ($template)
      {
        case 'isad':

          $relations = array(
            'isad_archival_history' => self::getI18nFieldNames('i18n.%s.archivalHistory', $cultures),
            'isad_immediate_source' => self::getI18nFieldNames('i18n.%s.acquisition', $cultures),
            'isad_appraisal_destruction' => self::getI18nFieldNames('i18n.%s.appraisal', $cultures),
            'isad_notes' => '',
            'isad_physical_condition' => self::getI18nFieldNames('i18n.%s.physicalCharacteristics', $cultures),
            'isad_control_description_identifier' => '',
            'isad_control_institution_identifier' => self::getI18nFieldNames('i18n.%s.institutionResponsibleIdentifier', $cultures),
            'isad_control_rules_conventions' => self::getI18nFieldNames('i18n.%s.rules', $cultures),
            'isad_control_status' => '',
            'isad_control_level_of_detail' => '',
            'isad_control_dates' => self::getI18nFieldNames('i18n.%s.revisionHistory', $cultures),
            'isad_control_languages' => '',
            'isad_control_scripts' => '',
            'isad_control_sources' => self::getI18nFieldNames('i18n.%s.sources', $cultures),
            'isad_control_archivists_notes' => '');

          break;

        case 'rad':

          $relations = array(
            'rad_archival_history' => self::getI18nFieldNames('i18n.%s.archivalHistory', $cultures),
            'rad_physical_condition' => self::getI18nFieldNames('i18n.%s.physicalCharacteristics', $cultures),
            'rad_immediate_source' => self::getI18nFieldNames('i18n.%s.acquisition', $cultures),
            'rad_general_notes' => '',
            'rad_conservation_notes' => '',
            'rad_control_description_identifier' => '',
            'rad_control_institution_identifier' => self::getI18nFieldNames('i18n.%s.institutionResponsibleIdentifier', $cultures),
            'rad_control_rules_conventions' => self::getI18nFieldNames('i18n.%s.rules', $cultures),
            'rad_control_status' => '',
            'rad_control_level_of_detail' => '',
            'rad_control_dates' => self::getI18nFieldNames('i18n.%s.revisionHistory', $cultures),
            'rad_control_language' => '',
            'rad_control_script' => '',
            'rad_control_sources' => self::getI18nFieldNames('i18n.%s.sources', $cultures));

          break;

        // TODO: Other templates (dacs, dc, isaar, etc)
      }
    }

    // Obtain hidden fields
    $hiddenFields = array();

    foreach (QubitSetting::getByScope('element_visibility') as $setting)
    {
      if(!(bool)$setting->getValue(array('sourceCulture' => true)) && isset($relations[$setting->name])
        && $relations[$setting->name] != '')
      {
        foreach ($relations[$setting->name] as $fieldName)
        {
          $hiddenFields[] = $fieldName;
        }
      }
    }

    return $hiddenFields;
  }

  /**
   * Set fields for a QueryString, removing those hidden for public users and those included in the except array.
   *
   * Tried to add the result fields to the cache but APC (our default caching engine) uses separate
   * memory spaces for web/cli and the cached fields can't be removed in arSearchPopulateTask
   */
  public static function setFields(\Elastica\Query\QueryString $query, $indexType, $except = array())
  {
    // Load ES mappings
    $mappings = arElasticSearchPlugin::loadMappings()->asArray();

    if (!isset($mappings[$indexType]))
    {
      throw new sfException('Unrecognized index type: ' . $indexType);
    }

    $cultures = self::getAvailableLanguages();
    $i18nIncludeInAll = null;

    if ($indexType === 'informationObject')
    {
      $i18nIncludeInAll = $mappings[$indexType]['_attributes']['i18nIncludeInAll'];
    }

    // Get all string fields included in _all for the index type
    $allFields = self::getAllObjectStringFields(
      $indexType,
      $mappings[$indexType],
      $prefix = '',
      $cultures,
      false,
      $i18nIncludeInAll
    );

    self::setBoostValues($indexType, $allFields, $cultures);

    // Remove fields in except (use array_values() because array_diff() adds keys)
    if (count($except) > 0)
    {
      $allFields = array_values(array_diff($allFields, $except));
    }

    // Do not check hidden fields for authenticated users, actors or repositories
    if (sfContext::getInstance()->user->isAuthenticated() || $indexType == 'actor' || $indexType == 'repository')
    {
      $query->setFields($allFields);

      return;
    }

    // Remove hidden fields from ES mapping fields (use array_values() because array_diff() adds keys)
    $filteredFields = array_values(array_diff($allFields, self::getHiddenFields()));

    $query->setFields($filteredFields);
  }

  /**
   * Delegates out to other functions based on indexType, which will in turn set the boost values for each field.
   *
   * @param string $indexType  which index type we're setting the field boost values for.
   * @param array &$fields  a reference to the fields we're setting the boost values on.
   * @param array $cultures  a list of cultures we'll be boosting for in i18n fields
   */
  private static function setBoostValues($indexType, &$fields, $cultures)
  {
    switch ($indexType)
    {
      case 'informationObject':
        arElasticSearchInformationObject::setBoostValues($fields, $cultures);
        break;
    }
  }

  /**
   * Check whether an i18n field should be included in the list of fields for an _all search
   *
   * @param string $prefix  The current prefix for the field name, e.g. "creators." for "creators.name"
   * @param string $fieldName  The current field name, e.g. "name" in "creators.name"
   * @param array $i18nIncludeInAll  A list of i18n fields to be allowed when searching _all
   *
   * @return bool  True if we should include this field in the _all search, false otherwise.
   */
  private static function checkI18nIncludeInAll($prefix, $fieldName, $i18nIncludeInAll)
  {
    if (!$i18nIncludeInAll)
    {
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
   * @param string $rootIndexType  The current, top level index type we're adding fields to, e.g. "informationObject".
   *
   *                               Note that since we recursively call getAllObjectStringFields to get foreign type
   *                               fields, this value may not be the "current" index being parsed, e.g. when adding
   *                               creators.name actor fields inside informationObject.
   *
   * @param array &$fields  A reference to our list of fields we're searching over with our _all query.
   * @param string $prefix  The current prefix for the field name, e.g. "creators." for "creators.name"
   * @param string $culture  The current culture for the i18n field we're adding.
   * @param string $fieldName  The current field name, e.g. "name" in "creators.name"
   * @param bool $foreignType  Whether or not this field in question is being parsed for a foreign type,
   *                           e.g. inside informationObject.creators
   *
   * @param array $i18nIncludeInAll  A list of i18n fields to be allowed when searching _all
   *
   *
   */
  private static function handleI18nStringFields($rootIndexType, &$fields, $prefix, $culture, $fieldName, $foreignType,
                                                 $i18nIncludeInAll)
  {
    // We may add special rules for other index types in the future
    switch ($rootIndexType)
    {
      case 'informationObject':
        if ($foreignType && false === self::checkI18nIncludeInAll($prefix, $fieldName, $i18nIncludeInAll))
        {
          return; // Skip field
        }

        break;
    }

    // Concatenate object name ($prefix), culture and field name
    $fields[] = $prefix.'i18n.'.$culture.'.'.$fieldName;
  }

  /**
   * Handle adding non-i18n string properties to our fields list. This is a helper function for
   * getAllObjectStringFields().
   *
   * Depending on the index type, there may be special rules we need to check before adding string fields to
   * our fields list.
   *
   * @param string $rootIndexType  The current, top level index type we're adding fields to, e.g. "informationObject".
   *
   *                               Note that since we recursively call getAllObjectStringFields to get foreign type
   *                               fields, this value may not be the "current" index being parsed, e.g. when adding
   *                               creators.name actor fields inside informationObject.
   *
   * @param array &$fields  A reference to our list of fields we're searching over with our _all query.
   * @param string $prefix  The current prefix for the prop name, e.g. "informationObject." in "informationObject.slug"
   * @param string $propertyName  The current property name, e.g. "slug" in "informationObject.slug"
   * @param bool $foreignType  Whether or not this field in question is being parsed for a foreign type,
   *                           e.g. inside informationObject.creators
   */
  private static function handleNonI18nStringFields($rootIndexType, &$fields, $prefix, $propertyName, $foreignType)
  {
    // We may add special rules for other index types in the future
    switch ($rootIndexType)
    {
      case 'informationObject':
        if ($foreignType)
        {
          return; // Skip all foreign type non-i18n string fields for info objects
        }

        break;
    }

    // Concatenate object name ($prefix) and field name
    $fields[] = $prefix.$propertyName;
  }

  /**
   * Gets all string fields included in _all from a mapping object array and cultures.
   *
   * This function will be called recursively on foreign types and nested fields.
   *
   *
   * @param string $rootIndexType  The current, top level index type we're adding fields to, e.g. "informationObject".
   *
   *                               Note that since we recursively call getAllObjectStringFields to get foreign type
   *                               fields, this value may not be the "current" index being parsed, e.g. when adding
   *                               creators.name actor fields inside informationObject.
   *
   * @param array $object  An array containing the current object mappings.
   * @param string $prefix  The current prefix for the prop name, e.g. "informationObject." in "informationObject.slug"
   * @param array $cultures  A list of cultures we'll be adding i18n fields from
   * @param bool $foreignType  Whether or not this field in question is being parsed for a foreign type,
   *                           e.g. inside informationObject.creators
   *
   * @param array $i18nIncludeInAll  A list of i18n fields to be allowed when searching _all
   */
  protected static function getAllObjectStringFields($rootIndexType, $object, $prefix, $cultures, $foreignType = false,
                                                     $i18nIncludeInAll = null)
  {
    $fields = array();

    if (isset($object['properties']))
    {
      foreach ($object['properties'] as $propertyName => $propertyProperties)
      {
        // Get i18n fields for selected cultures, they're always included in _all
        if ($propertyName == 'i18n')
        {
          foreach ($cultures as $culture)
          {
            if (!isset($propertyProperties['properties'][$culture]['properties']))
            {
              continue;
            }

            foreach ($propertyProperties['properties'][$culture]['properties'] as $fieldName => $fieldProperties)
            {
              self::handleI18nStringFields($rootIndexType, $fields, $prefix, $culture, $fieldName, $foreignType,
                                           $i18nIncludeInAll);
            }
          }
        }
        // Get nested objects fields
        else if (isset($propertyProperties['type']) && $propertyProperties['type'] == 'object')
        {
          $nestedFields = self::getAllObjectStringFields(
            $rootIndexType,
            $object['properties'][$propertyName],
            $prefix.$propertyName.'.',
            $cultures
          );

          $fields = array_merge($fields, $nestedFields);
        }
        // Get foreign objects fields (couldn't find a better way than checking the dynamic property)
        else if (isset($propertyProperties['dynamic']))
        {
          $foreignObjectFields = self::getAllObjectStringFields(
            $rootIndexType,
            $object['properties'][$propertyName],
            $prefix.$propertyName.'.',
            $cultures,
            true,
            $i18nIncludeInAll
          );

          $fields = array_merge($fields, $foreignObjectFields);
        }
        // Get string fields included in _all
        else if ((!isset($propertyProperties['include_in_all']) || $propertyProperties['include_in_all'])
          && (isset($propertyProperties['type']) && $propertyProperties['type'] == 'string'))
        {
          self::handleNonI18nStringFields($rootIndexType, $fields, $prefix, $propertyName, $foreignType);
        }
      }
    }

    return $fields;
  }

  /**
   * Expands i18n field names into various specified cultures, with the option to add boosting.
   *
   * @param array $fields  Which fields to expand. For example, 'i18n.%s.title' will expand to 'i18n.en.title',
   *                       'i18n.fr.title', 'i18n.es.title', etc.
   *
   * @param array $cultures  An array specifying which cultures to expand to. If not specified, we look up which
   *                         cultures are active in AtoM and go off that.
   *
   * @param array $boost  An array specifying filedName => (int)boostValue to add boost values onto the fields.
   */
  public static function getI18nFieldNames($fields, $cultures = null, $boost = array())
  {
    // Get all available cultures if $cultures isn't set
    if (empty($cultures))
    {
      $cultures = array();
      foreach (QubitSetting::getByScope('i18n_languages') as $setting)
      {
        $cultures[] = $setting->getValue(array('sourceCulture' => true));
      }
    }

    // Make sure cultures is an array
    if (!is_array($cultures))
    {
      $cultures = array($cultures);
    }

    // Make sure fields is an array
    if (!is_array($fields))
    {
      $fields = array($fields);
    }

    // Format fields
    $i18nFieldNames = array();
    foreach ($cultures as $culture)
    {
      foreach ($fields as $field)
      {
        $formattedField = sprintf($field, $culture);

        if (isset($boost[$field]))
        {
          $formattedField .= '^'.$boost[$field];
        }

        $i18nFieldNames[] = $formattedField;
      }
    }

    return $i18nFieldNames;
  }

  /*
   * Gets all premis data related to an information object
   */
  public static function getPremisData($ioId, $conn)
  {
    $premisData = array();

    $sql  = 'SELECT *
      FROM '.QubitPremisObject::TABLE_NAME.' premis
      WHERE premis.information_object_id = ?';

    $statement = $conn->prepare($sql);
    $statement->execute(array($ioId));

    foreach ($statement->fetch() as $field => $value)
    {
      if (empty($value))
      {
        continue;
      }

      switch ($field)
      {
        case 'last_modified':
          $premisData['lastModified'] =  arElasticSearchPluginUtil::convertDate($value);

          break;

        case 'date_ingested':
          $premisData['dateIngested'] =  arElasticSearchPluginUtil::convertDate($value);

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

    $sql  = 'SELECT property.name, i18n.value
      FROM '.QubitProperty::TABLE_NAME.' property
      JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
        ON property.id = i18n.id
      WHERE property.scope = "premisData"
        AND property.source_culture = i18n.culture
        AND property.object_id = ?';

    $statement = $conn->prepare($sql);
    $statement->execute(array($ioId));

    foreach ($statement->fetchAll(PDO::FETCH_OBJ) as $property)
    {
      $value = unserialize($property->value);

      switch ($property->name)
      {
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

    if (!empty($premisData))
    {
      return $premisData;
    }
  }

  /**
   * Escapes the special chars specified in the "escape_queries" setting
   *
   * @param string $term Query term to escape
   *
   * @return string Escaped query term
   */
  public static function escapeTerm($term)
  {
    $specialChars = trim(sfConfig::get('app_escape_queries', ''));

    // Return term directly if the setting is empty
    if (empty($specialChars))
    {
      return $term;
    }

    // Split into array removing whitespaces
    $specialChars = preg_split('/\s*,\s*/', $specialChars);

    // Escaping \ has to be first
    if (in_array('\\', $specialChars))
    {
      $term = str_replace('\\', '\\\\', $term);
    }

    foreach ($specialChars as $char)
    {
      // Ignore empty chars and \
      if (empty($char) || $char == '\\')
      {
        continue;
      }

      $term = str_replace($char, '\\' . $char, $term);
    }

    return $term;
  }
}
