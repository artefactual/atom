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

class ApiInformationObjectsReadAssociationAction extends QubitApiAction
{
  protected function get($request)
  {
    if (null === $rel = QubitRelation::getById($request->id))
    {
      throw new QubitApi404Exception('Relation not found');
    }

    $data = array(
      'id' => (int)$rel->id,
      'subject' => array(
        'id' => (int)$rel->subject->id,
        'title' => $rel->subject->__toString()
      ),
      'object' => array(
        'id' => (int)$rel->object->id,
        'title' => $rel->object->__toString()
      ),
      'type' => array(
        'id' => (int)$rel->type->id,
        'name' => $rel->type->__toString()
      )
    );

    $description = $rel->getDescription(array('cultureFallback' => true));
    if (!empty($description))
    {
      $data['description'] = $description;
    }

    return $data;
  }
}
