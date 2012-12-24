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
  public function __construct()
  {
    // $this->logger ...
    // $this->count ...
    // $this->verbose
  }

  # abstract public function populate();
  # abstract public function serialize($object);
  # abstract public function update($object);

  public static function serializeI18ns($object, array $parentClasses = array())
  {
    // Build list of classes to get i18n fields
    // For example: Repository -> Actor, User -> Actor, etc...
    $classes = array_merge(array(get_class($object)), $parentClasses);

    // This is the array that we are building and returning
    $i18ns = array();

    foreach (QubitSetting::getByScope('i18n_languages') as $setting)
    {
      $culture = $setting->getValue(array('sourceCulture' => true));

      $i18ns[$culture] = array();

      foreach ($classes as $class)
      {
        // Use table maps to find existing i18n columns
        $className = str_replace('Qubit', '', $class) . 'I18nTableMap';
        $map = new $className;

        foreach ($map->getColumns() as $column)
        {
          if (!$column->isPrimaryKey() && !$column->isForeignKey())
          {
            $colName = $column->getPhpName();

            if (null !== $colValue = $object->__get($colName))
            {
              $i18ns[$culture][$colName] = $object->__get($colName, array('cultureFallback' => false));
            }
          }
        }
      }
    }

    return $i18ns;
  }
}
