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
    // Iterate over types (actor, information_object, ...)
    foreach ($this->mapping as $typeName => &$typeProperties)
    {
      // Look for special attributes like i18n or timestamp and update the
      // mapping accordingly. For example, 'timestamp' adds the created_at
      // and updated_at fields each time is used.
      if (isset($typeProperties['_attributes']))
      {
        foreach ($typeProperties['_attributes'] as $attributeName => $attributeValue)
        {
          switch ($attributeName)
          {
            case 'i18n':
              $this->setIfNotSet($typeProperties, 'source_culture', array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false));
              $this->setIfNotSet($typeProperties, 'i18n', array(
                'type' => 'object',
                'include_in_root' => true,
                'properties' => array(
                  'culture' => array('type' => 'string', 'index' => 'not_analyzed', 'include_in_all' => false))));

              break;

            case 'timestamp':
              $this->setIfNotSet($typeProperties, 'created_at', array('type' => 'date'));
              $this->setIfNotSet($typeProperties, 'updated_at', array('type' => 'date'));

              break;
          }
        }

        unset($typeProperties['_attributes']);
      }
    }
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
