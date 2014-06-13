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

class ApiInformationObjectsUpdateAssociationAction extends QubitApiAction
{
  protected function put($request, $payload)
  {
    if (null === $rel = QubitRelation::getById($request->id))
    {
      throw new QubitApi404Exception('Relation not found');
    }

    if (empty($payload->type_id) && !ctype_digit($payload->type_id))
    {
      throw new QubitApiBadRequestException('Missing parameter type_id or misformed');
    }

    $rel->typeId = $payload->type_id;

    if (empty($payload->description))
    {
      $rel->setDescription(null);
    }
    else
    {
      $rel->setDescription($payload->description);
    }

    try
    {
      $rel->save();
    }
    catch (Exception $e)
    {
      new QubitApiException('Failed!');
    }

    return sfView::NONE;
  }
}
