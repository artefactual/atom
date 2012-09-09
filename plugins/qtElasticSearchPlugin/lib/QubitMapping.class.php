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

/**
 * Parent class to provide global model mapping methods for storing objects
 * within an ElasticSearch document index.
 *
 * @package    qtElasticSearchPlugin
 * @author     MJ Suhonos <mj@artefactual.com>
 * @version    svn:$Id: QubitMapping.class.php 10316 2011-11-14 22:40:18Z mj $
 */
class QubitMapping
{
  public static function getI18nProperties()
  {
    return array(
      'sourceCulture' => array(
        'type' => 'string',
        'index' => 'not_analyzed',
        'include_in_all' => false),
     'i18n' => array(
      'type' => 'object',
      'include_in_root' => true,
      'properties' => array(
        'culture' => array(
          'type' => 'string',
          'index' => 'not_analyzed',
          'include_in_all' => false))));
  }

  public static function getTimestampProperties()
  {
    return array(
      'createdAt' => array(
          'type' => 'date'
        ),
      'updatedAt' => array(
          'type' => 'date'));
  }

  public static function getI18nFields($class)
  {
    // use reflection on i18n object to get property list from class constants
    if (class_exists($class . 'I18n'))
    {
      $reflect = new ReflectionClass($class . 'I18n');
      $i18nFields = $reflect->getConstants();

      // these constants cannot be accessed by __get()
      unset($i18nFields['DATABASE_NAME']);
      unset($i18nFields['TABLE_NAME']);

      // id and culture are not used for indexing
      unset($i18nFields['ID']);
      unset($i18nFields['CULTURE']);

      return array_map(array('sfInflector', 'camelize'), array_map('strtolower', array_keys($i18nFields)));
    }
  }

  public static function serializeI18ns($object, $objectI18ns)
  {
    // get all i18n-ized versions of this object
    foreach ($objectI18ns as $culture => $objectI18n)
    {
      // index all values on the i18n-ized object
      foreach (self::getI18nFields(get_class($object)) as $method)
      {
        $value = call_user_func(array($objectI18n, 'get' . $method));
        if (!is_null($value))
        {
          $i18ns[$culture][lcfirst($method)] = $value;
        }
      }
    }

    $serializedI18ns = array();
    foreach ($i18ns as $culture => $doc)
    {
      $doc['culture'] = $culture;
      $serializedI18ns[] = $doc;
    }

    return $serializedI18ns;
  }
}
