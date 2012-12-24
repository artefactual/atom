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
  protected $nestedTypes = null;

  /**
   * Dumps schema as array
   *
   * @return array
   */
  public function asArray()
  {
    return $this->mapping;
  }

  /**
   * Load mapping from array
   *
   * @param array $mapping_array
   */
  public function loadArray($mapping_array)
  {
    if (is_array($mapping_array) && !empty($mapping_array))
    {
      if (count($mapping_array) > 1)
      {
        throw new sfException('A mapping.yml must only contain 1 entry.');
      }

      $this->mapping = $mapping_array['mapping'];

      $this->fixYamlShorthands();
    }
  }

  /**
   * Load mapping from YAML file
   *
   * @param string $file
   */
  public function loadYAML($file)
  {
    $mapping_array = sfYaml::load($file);

    if (!is_array($mapping_array))
    {
      return; // No defined schema here, skipping
    }

    $this->loadArray($mapping_array);
  }

  /**
   * Fixes shorthands
   */
  protected function fixYamlShorthands()
  {
    // Collect nested types since some of them are intended to be only nested
    // as properties of other types, so we are excluding them as main types
    foreach ($this->mapping as $typeName => &$typeProperties)
    {
      $nested = isset($typeProperties['_attributes']['nested']);
      $nestable = isset($typeProperties['_attributes']['nestable']);

      if (!$nested && !$nestable)
      {
        continue;
      }

      // Unset the attribute since we are already processing it
      if ($nested) unset($typeProperties['_attributes']['nested']);
      if ($nestable) unset($typeProperties['_attributes']['nestable']);

      // Store it and parse other attributes (i18n, timestamp, etc...)
      $this->nestedTypes[$typeName] = array('type' => 'object', 'properties' => $this->processPropertyAttributes($typeProperties));

      // Avoid this nested type to be mapped as a regular type
      if (!$nestable)
      {
        unset($this->mapping[$typeName]);
      }
    }

    // Iterate over types (actor, information_object, ...)
    foreach ($this->mapping as $typeName => &$typeProperties)
    {
      // Parse attributes
      $typeProperties = $this->processPropertyAttributes($typeProperties);

      // Nest foreign types
      foreach ($typeProperties as $propertyName => &$propertyValue)
      {
        if (isset($propertyValue['_attributes']['foreignType']))
        {
          $foreignType = $propertyValue['_attributes']['foreignType'];

          $typeProperties[$propertyName] = $this->nestedTypes[$foreignType];
        }
      }
    }
  }

  protected function processPropertyAttributes(array $typeProperties)
  {
    // Stop execution if any special attribute was set
    if (!isset($typeProperties['_attributes']))
    {
      return;
    }

    // Look for special attributes like i18n or timestamp and update the
    // mapping accordingly. For example, 'timestamp' adds the created_at
    // and updated_at fields each time is used.
    foreach ($typeProperties['_attributes'] as $attributeName => $attributeValue)
    {
      switch ($attributeName)
      {
        case 'i18n':
          $this->setIfNotSet($typeProperties, 'source_culture', array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false));

          // For each culture, map an object. This is how it will look like:
          // i18n{object}:
          //   en{object}: ... (title, etc... dynamically built)
          //   fr{object}: ... (title, etc... dynamically built)
          $nestedI18nObjects = array();
          foreach (QubitSetting::getByScope('i18n_languages') as $setting)
          {
            $culture = $setting->getValue(array('sourceCulture' => true));
            $nestedI18nObjects[$culture] = array(
              'type' => 'object',
              'include_in_parent' => false);
          }

          $this->setIfNotSet($typeProperties, 'i18n', array(
            'type' => 'object',
            'include_in_root' => true,
            'properties' => $nestedI18nObjects));

          break;

        case 'timestamp':
          $this->setIfNotSet($typeProperties, 'created_at', array('type' => 'date'));
          $this->setIfNotSet($typeProperties, 'updated_at', array('type' => 'date'));

          break;
      }
    }

    unset($typeProperties['_attributes']);

    return $typeProperties;
  }

  /**
   * Sets entry if not set
   *
   * @param string $entry
   * @param string $key
   * @param string $value
   */
  protected function setIfNotSet(&$entry, $key, $value)
  {
    if (!isset($entry[$key]))
    {
      $entry[$key] = $value;
    }
  }
}
