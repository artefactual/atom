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

abstract class arElasticSearchModelBase
{
  protected
    $timer = null,
    $count = 0;

  protected static
    $conn;

  public function __construct()
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $this->search = QubitSearch::getInstance();

    $this->log(" - Loading " . get_class($this) . "...");
  }

  public function getCount()
  {
    return $this->count;
  }

  public function setTimer($timer)
  {
    $this->timer = $timer;
  }

  protected function log($message)
  {
    $this->search->log($message);
  }

  protected function logEntry($title, $count)
  {
    $this->log(sprintf('    [%s] %s inserted (%ss) (%s/%s)',
      str_replace('arElasticSearch', '', get_class($this)),
      $title,
      $this->timer->elapsed(),
      $count,
      $this->getCount()));
  }

  public static function serializeI18ns($id, array $classes, $options = array())
  {
    if (1 > count($classes))
    {
      throw new sfException('At least one class name must be passed.');
    }

    // Build an array of i18n languages
    $allowedLanguages = array();
    foreach (QubitSetting::getByScope('i18n_languages') as $setting)
    {
      $allowedLanguages[] = $setting->getValue(array('sourceCulture' => true));
    }

    // Properties
    $i18ns = array();

    // Allow merging i18n fields, used for partial foreign types
    // when different object fields are included in the same object
    if (isset($options['merge']))
    {
      $i18ns = $options['merge'];
    }

    // Tableize class name
    foreach ($classes as &$class)
    {
      $class = str_replace('Qubit', '', $class);
      $class = sfInflector::tableize($class);
      $class .= '_i18n';

      // Build SQL query per table. I tried with joins but for some reason the
      // culture value appears empty although it workes in the command line
      $sql  = sprintf('SELECT * FROM %s WHERE id = ? ORDER BY culture ASC', $class);

      foreach (QubitPdo::fetchAll($sql, array($id)) as $item)
      {
        // Any i18n record within a culture previously not configured will
        // be ignored since the search engine will only accept known languages
        if (!in_array($item->culture, $allowedLanguages))
        {
          continue;
        }

        foreach (get_object_vars($item) as $key => $value)
        {
          // Pass if the column is unneeded or null, or if it's not set in options fields
          if (in_array($key, array('id', 'culture')) || is_null($value)
            || (isset($options['fields']) && !in_array($key, $options['fields'])))
          {
            continue;
          }

          $camelized = lcfirst(sfInflector::camelize($key));

          $i18ns[$item->culture][$camelized] = $value;
        }
      }
    }

    // Avoid including languages key inside languages when the merge option is set
    $i18ns['languages'] = array_values(array_diff(array_keys($i18ns), array('languages')));

    return $i18ns;
  }

  # abstract public function update($object);
  public static function update($object)
  {
    return true;
  }

  /**
   * Add boost values to various fields.
   *
   * @param array &$fields  An array of the fields to be modified with their boost values added.
   * @param array $i18nfields  Specifies which i18n fields to boost, and which boost value to use.
   *                           The array is in the form of 'fieldName' => (int)boostNumber
   *
   * @param array $nonI18nFields  Same as above, except for non-i18n string fields.
   */
  protected static function addBoostValuesToFields(&$fields, $i18nFields, $nonI18nFields)
  {
    // Expand all the i18n fields into their various cultures, add boost values
    $i18nBoostFields = arElasticSearchPluginUtil::getI18nFieldNames(
      array_keys($i18nFields),
      null,
      $i18nFields
    );

    foreach ($fields as &$field)
    {
      foreach ($i18nBoostFields as $i18nBoostField)
      {
        // Match boost field against current field, add boost if match found.
        // i.e.: i18n.en.title will turn into i18n.en.title^10
        if (0 === strpos($i18nBoostField, $field))
        {
          $field = $i18nBoostField;
        }
      }

      foreach ($nonI18nFields as $nonI18nBoostField => $boost)
      {
        $nonI18nBoostField = $nonI18nBoostField.'^'.$boost;

        if (0 === strpos($nonI18nBoostField, $field))
        {
          $field = $nonI18nBoostField;
        }
      }
    }
  }
}
