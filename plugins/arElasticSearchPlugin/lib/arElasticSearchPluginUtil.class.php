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
      $culture = sfContext::getInstance()->user->getCulture();
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
        $allFields = self::getAllObjectStringFields($mappings[$options['type']], $prefix = '', $culture);
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

      // Create array with relations (hidden field => ES mapping field) for the actual template and culture
      if (isset($template))
      {
        switch ($template)
        {
          case 'isad':

            $relations = array(
              'isad_archival_history' => 'i18n.'.$culture.'.archivalHistory',
              'isad_immediate_source' => 'i18n.'.$culture.'.acquisition',
              'isad_appraisal_destruction' => 'i18n.'.$culture.'.appraisal',
              'isad_notes' => '',
              'isad_physical_condition' => 'i18n.'.$culture.'.physicalCharacteristics',
              'isad_control_description_identifier' => '',
              'isad_control_institution_identifier' => 'i18n.'.$culture.'.institutionResponsibleIdentifier',
              'isad_control_rules_conventions' => 'i18n.'.$culture.'.rules',
              'isad_control_status' => '',
              'isad_control_level_of_detail' => '',
              'isad_control_dates' => 'i18n.'.$culture.'.revisionHistory',
              'isad_control_languages' => '',
              'isad_control_scripts' => '',
              'isad_control_sources' => 'i18n.'.$culture.'.sources',
              'isad_control_archivists_notes' => '');

            break;

          case 'rad':

            $relations = array(
              'rad_archival_history' => 'i18n.'.$culture.'.archivalHistory',
              'rad_physical_condition' => 'i18n.'.$culture.'.physicalCharacteristics',
              'rad_immediate_source' => 'i18n.'.$culture.'.acquisition',
              'rad_general_notes' => '',
              'rad_conservation_notes' => '',
              'rad_control_description_identifier' => '',
              'rad_control_institution_identifier' => 'i18n.'.$culture.'.institutionResponsibleIdentifier',
              'rad_control_rules_conventions' => 'i18n.'.$culture.'.rules',
              'rad_control_status' => '',
              'rad_control_level_of_detail' => '',
              'rad_control_dates' => 'i18n.'.$culture.'.revisionHistory',
              'rad_control_language' => '',
              'rad_control_script' => '',
              'rad_control_sources' => 'i18n.'.$culture.'.sources');

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
          $hiddenFields[] = $relations[$setting->name];
        }
      }

      // Remove hidden fields from ES mapping fields
      $filteredFields = array_diff($allFields, $hiddenFields);

      // Set filtered fields for the query (use array_values() because array_diff() adds keys)
      $query->setFields(array_values($filteredFields));
    }
  }

  /**
   * Gets all string fields included in _all from a mapping type array and a culture
   */
  protected static function getAllObjectStringFields($object, $prefix, $culture)
  {
    $fields = array();
    if (isset($object['properties']))
    {
      foreach ($object['properties'] as $propertyName => $propertyProperties)
      {
        // Get i18n fields for the actual culture, they're always included in _all
        if ($propertyName == 'i18n' && isset($propertyProperties['properties'][$culture]['properties']))
        {
          foreach ($propertyProperties['properties'][$culture]['properties'] as $fieldName => $fieldProperties)
          {
            // Concatenate object name ($prefix) and field name
            $fields[] = $prefix.'i18n.'.$culture.'.'.$fieldName;
          }
        }
        // Get nested objects fields
        else if (isset($propertyProperties['type']) && $propertyProperties['type'] == 'object')
        {
          $fields = array_merge($fields, self::getAllObjectStringFields($object['properties'][$propertyName], $prefix.$propertyName.'.', $culture));
        }
        // Get foreing objects fields (couldn't find a better why that checking the dynamic property)
        else if (isset($propertyProperties['dynamic']))
        {
          $fields = array_merge($fields, self::getAllObjectStringFields($object['properties'][$propertyName], $prefix.$propertyName.'.', $culture));
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
}
