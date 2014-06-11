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

class ApiInformationObjectsAssociateAction extends QubitApiAction
{
  protected function post($request, $payload)
  {
    if (QubitInformationObject::ROOT_ID === (int)$request->id)
    {
      throw new QubitApiForbiddenException;
    }

    if (null === $io = QubitInformationObject::getById($request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (false === isset($payload->type_id) || false === ctype_digit($payload->type_id))
    {
      throw new QubitApiBadRequestException('Missing parameter: type_id');
    }

    if (false === isset($payload->target_id) || false === ctype_digit($payload->target_id))
    {
      throw new QubitApiBadRequestException('Missing parameter: target_id');
    }

    if ($this->existsRelation($payload->type_id, $io->id, $payload->target_id))
    {
      // TODO: 409?
      throw new QubitApiBadRequestException('This relation has already been created');
    };

    // Add relation
    $relation = new QubitRelation;
    $relation->typeId = $payload->type_id; // RELATED_MATERIAL_DESCRIPTIONS_ID
    $relation->objectId = $payload->target_id;
    if (isset($paylaod->note))
    {
      $relation->description = $payload->note;
    }

    $io->relationsRelatedBysubjectId[] = $relation;
    $io->save();

    return array(
      'source_id' => (int)$io->id,
      'target_id' => (int)$payload->target_id,
      'type_id' => (int)$payload->type_id
    );
  }

  protected function existsRelation($typeId, $sourceId, $targetId)
  {
    $sql = <<<EOL
SELECT
  COUNT(*) AS count
FROM
  relation
WHERE
  type_id = ?
  AND ((subject_id = ? AND object_id = ?) OR (subject_id = ? AND object_id = ?))
EOL;

    $r = QubitPdo::fetchOne($sql, array($typeId, $sourceId, $targetId, $targetId, $sourceId));

    return (int)$r->count > 0;
  }
}
