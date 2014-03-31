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

class ApiInformationObjectsMoveAction extends QubitApiAction
{
  protected function post($request, $payload)
  {
    if (QubitInformationObject::ROOT_ID === (int)$this->request->id)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (null === $io = QubitInformationObject::getById($this->request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (!isset($payload->parent_id))
    {
      throw new QubitApiException;
    }

    $io->parentId = $payload->parent_id;

    $io->save();

    // TODO: return full object
    return array(
      'id' => (int)$io->id,
      'parent_id' => (int)$io->parentId);
  }
}
