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

class ApiInformationobjectsRelatedTechnologiesCreateAction extends QubitApiAction
{
  protected function post($request, $payload)
  {
    if (QubitInformationObject::ROOT_ID === (int)$request->id)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (null === $io = QubitInformationObject::getById($request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $criteria = new Criteria;
    $criteria->addJoin(QubitRelation::TYPE_ID, QubitTerm::ID);
    $criteria->add(QubitRelation::SUBJECT_ID, $io->id);
    $criteria->add(QubitTerm::TAXONOMY_ID, sfConfig::get('app_drmc_taxonomy_supporting_technologies_relation_types_id'));

    // Existing relationships
    if (null !== $relations = QubitRelation::get($criteria))
    {
      foreach ($relations as $relation)
      {
        if (false !== $r = $this->searchRelationInPostData($payload, (int)$relation->id))
        {
          $relation->typeId = $r->type_id;

          if (!empty($r->description))
          {
            $relation->description = $r->description;
          }

          $relation->save();
        }
        else
        {
          $relation->delete();
        }
      }
    }

    // New relationships
    $new = false;
    foreach ($payload as $item)
    {
      if (isset($item->id))
      {
        continue;
      }

      $relation = new QubitRelation;
      $relation->objectId = $item->technology_record_id;
      $relation->typeId = $item->type_id;

      if (!empty($item->description))
      {
        $relation->description = $item->description;
      }

      $io->relationsRelatedBysubjectId[] = $relation;

      $new = true;
    }

    if ($new)
    {
      $io->save();
    }
  }

  protected function searchRelationInPostData($relations, $id)
  {
    foreach ($relations as $relation)
    {
      if (property_exists($relation, 'id') && $relation->id == $id)
      {
        return $relation;
      }
    }

    return false;
  }
}
