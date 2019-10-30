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

namespace AccessToMemory\test\mock;

class QubitPhysicalObject
{
  public
    $id,
    $name,
    $typeId,
    $location,
    $culture;

  public function save($dbcon = null)
  {
    return \QubitQuery::create();
  }

  public static function getByName($name, $options = array())
  {
    $results = new \ArrayIterator;

    switch ($name)
    {
      case 'DJ001':
        $object = new self;
        $object->id       = 111111;
        $object->name     = 'DJ001';
        $object->typeId   = 1;
        $object->location = 'unknown';
        $object->culture  = 'de';

        $results->append($object);

        break;

      case 'DJ002':
        // Simulate a match on two existing records with the same name
        $object = new self;
        $object->id       = 222222;
        $object->name     = 'DJ003';
        $object->typeId   = 2;
        $object->location = 'boîte 20191031';
        $object->culture  = 'fr';

        $results->append($object);

        $object = new self;
        $object->id       = 333333;
        $object->name     = 'DJ003';
        $object->typeId   = 1;
        $object->location = 'Aisle 11, Shelf 31';
        $object->culture  = 'en';

        $results->append($object);

        break;
    }

    return $results;
  }
}