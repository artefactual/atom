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

class ApiInformationobjectsRelatedTechnologiesReadAction extends QubitApiAction
{
  protected function get($request)
  {
    if (QubitInformationObject::ROOT_ID === (int)$request->id)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    if (null === $io = QubitInformationObject::getById($request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $results = array();

    $criteria = new Criteria;
    $criteria->addJoin(QubitRelation::TYPE_ID, QubitTerm::ID);
    $criteria->add(QubitRelation::SUBJECT_ID, $io->id);
    $criteria->add(QubitTerm::TAXONOMY_ID, sfConfig::get('app_drmc_taxonomy_supporting_technologies_relation_types_id'));
    foreach (QubitRelation::get($criteria) as $relation)
    {
      $item = array(
        'id' => (int)$relation->id,
        'technology_record_id' => (int)$relation->objectId,
        'type_id' => (int)$relation->typeId
      );

      $this->addItemToArray($item, 'description', $relation->description);
      $this->addItemToArray($item, 'name', QubitInformationObject::getById($relation->objectId)->getTitle());

      $results[] = $item;
    }

    $data = array(
      'results' => $results
    );

    return $data;
  }
}
