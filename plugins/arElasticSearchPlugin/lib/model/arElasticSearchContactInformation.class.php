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

class arElasticSearchContactInformation extends arElasticSearchModelBase
{
  public static function serialize($object)
  {
    $serialized = array();

    $serialized['contactPerson'] = $object->contactPerson;
    $serialized['streetAddress'] = $object->streetAddress;
    $serialized['postalCode'] = $object->postalCode;
    $serialized['countryCode'] = $object->countryCode;

    // geo point type
    if (!empty($object->latitude) && !empty($object->longitude))
    {
      $serialized['location']['lat'] = $object->latitude;
      $serialized['location']['lon'] = $object->longitude;
    }

    $serialized['sourceCulture'] = $object->sourceCulture;
    $serialized['i18n'] = self::serializeI18ns($object->id, array('QubitContactInformation', 'QubitActor'));

    return $serialized;
  }
}
