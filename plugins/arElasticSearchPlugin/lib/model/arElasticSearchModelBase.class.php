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

  # abstract public function update($object);

  public function __construct()
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $this->search = QubitSearch::getInstance();

    $this->log(" - Loading " . get_class($this) . "...");
  }

  public function getCount($object)
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
        foreach (arElasticSearchMapping::getI18nFields($class) as $colName)
        {
          if (null !== $colValue = $object->__get($colName))
          {
            $i18ns[$culture][$colName] = $object->__get($colName, array('culture' => $culture, 'fallback' => false));
          }
        }
      }
    }

    return $i18ns;
  }
}
