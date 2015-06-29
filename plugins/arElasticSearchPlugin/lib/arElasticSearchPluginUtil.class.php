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
  const
    DATE_FORMAT = 'Y-m-d',
    DATETIME_FORMAT = 'Y-m-d H:i:s',
    DATETIME_DEFAULT_FORMAT = 'Y-m-d\TH:i:s\Z';

  public static function convertDate($date, $format = null)
  {
    if (is_null($date))
    {
      return;
    }

    if (is_null($format))
    {
      $format = self::DATETIME_DEFAULT_FORMAT;
    }

    if ($date instanceof DateTime)
    {
      $value = $date->format($format);
    }
    else
    {
      if (is_int($date))
      {
        $timestamp = $date;
      }
      else
      {
        $timestamp = strtotime($date);
      }

      $value = date($format, $timestamp);
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
   */
  public static function normalizeDateWithoutMonthOrYear($date, $endDate = false)
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
   * Set all fields for a QueryString, removing those hidden for public users
   */
  public static function setAllFields(\Elastica\Query\QueryString $query, $options = array())
  {
    // Set _all for authenticated users
    if (sfContext::getInstance()->user->isAuthenticated())
    {
      $query->setDefaultField('_all');
    }
    else
    {
      // Get available cultures
      $cultures = array();
      foreach (QubitSetting::getByScope('i18n_languages') as $setting)
      {
        $cultures[] = $setting->getValue(array('sourceCulture' => true));
      }

      $allFields = $filteredFields = $hiddenFields = $relations = array();

      if (!isset($options['type']))
      {
        $options['type'] = 'informationObject';
      }

      // Load ES mappings
      $mappings = arElasticSearchPlugin::loadMappings();

      // Get all string fields included in _all for the type and actual culture
      if (isset($mappings[$options['type']]))
      {
        $allFields = self::getAllObjectStringFields($mappings[$options['type']], $prefix = '', $cultures);
      }

      // Get actual template tor the type
      switch ($options['type'])
      {
        case 'informationObject':

          $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template');
          if (isset($infoObjectTemplate))
          {
            $template = $infoObjectTemplate->getValue(array('sourceCulture'=>true));
          }

          break;

        // TODO: Other types (actor, term, etc)
      }

      // Create array with relations (hidden field => ES mapping field) for the actual template and cultures
      if (isset($template))
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
      foreach (QubitSetting::getByScope('element_visibility') as $setting)
      {
        if(!(bool) $setting->getValue(array('sourceCulture' => true))
          && isset($relations[$setting->name])
          && $relations[$setting->name] != '')
        {
          foreach ($relations[$setting->name] as $fieldName)
          {
            $hiddenFields[] = $fieldName;
          }
        }
      }

      // Remove hidden fields from ES mapping fields
      $filteredFields = array_diff($allFields, $hiddenFields);

      // Set filtered fields for the query (use array_values() because array_diff() adds keys)
      $query->setFields(array_values($filteredFields));
    }
  }

  /**
   * Gets all string fields included in _all from a mapping type array and cultures
   */
  protected static function getAllObjectStringFields($object, $prefix, $cultures)
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
              // Concatenate object name ($prefix) and field name
              $fields[] = $prefix.'i18n.'.$culture.'.'.$fieldName;
            }
          }
        }
        // Get nested objects fields
        else if (isset($propertyProperties['type']) && $propertyProperties['type'] == 'object')
        {
          $fields = array_merge($fields, self::getAllObjectStringFields($object['properties'][$propertyName], $prefix.$propertyName.'.', $cultures));
        }
        // Get foreing objects fields (couldn't find a better why that checking the dynamic property)
        else if (isset($propertyProperties['dynamic']))
        {
          $fields = array_merge($fields, self::getAllObjectStringFields($object['properties'][$propertyName], $prefix.$propertyName.'.', $cultures));
        }
        // Get string fields included in _all
        else if ((!isset($propertyProperties['include_in_all']) || $propertyProperties['include_in_all'])
          && (isset($propertyProperties['type']) && $propertyProperties['type'] == 'string'))
        {
          // Concatenate object name ($prefix) and field name
          $fields[] = $prefix.$propertyName;
        }
      }
    }

    return $fields;
  }

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
}
