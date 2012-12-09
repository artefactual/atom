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
 * This class is used to provide a model mapping for storing QubitContactInformation objects
 * within an ElasticSearch document index.
 *
 * @package    arElasticSearchPlugin
 * @author     MJ Suhonos <mj@suhonos.ca>
 */
class QubitContactInformationMapping extends QubitMapping
{
  static function getProperties()
  {
    return array(
      'contactPerson' => array(
        'type' => 'string',
        'index' => 'no'),
      'streetAddress' => array(
        'type' => 'string',
        'index' => 'no'),
      'postalCode' => array(
        'type' => 'string',
        'include_in_all' => false),
      'countryCode' => array(
        'type' => 'string',
        'index' => 'not_analyzed',
        'include_in_all' => false),
      'location' => array(
        'type' => 'geo_point'))
      + self::getI18nProperties();
  }

  static function serialize($object)
  {
    $serialized = array();
    $serialized['contactPerson'] = $object->contactPerson;
    $serialized['streetAddress'] = $object->streetAddress;
    $serialized['postalCode'] = $object->postalCode;
    $serialized['countryCode'] = $object->countryCode;
    $serialized['location']['lat'] = $object->latitude;
    $serialized['location']['lon'] = $object->longitude;

    $serialized['sourceCulture'] = $object->sourceCulture;
    $contactI18ns = $object->contactInformationI18ns->indexBy('culture');
    $serialized['i18n'] = self::serializeI18ns($object, $contactI18ns);

    return $serialized;
  }
}
