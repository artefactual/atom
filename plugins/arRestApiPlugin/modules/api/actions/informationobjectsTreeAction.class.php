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

class ApiInformationObjectsTreeAction extends QubitApiAction
{
  protected function get($request)
  {
    // Get parent slug so we can determine its ID
    $criteria = new Criteria;
    $criteria->add(QubitSlug::SLUG, $request->parent_slug);

    $slug = QubitSlug::getOne($criteria);

    $io = QubitInformationObject::getById($slug->objectId);

    $result = $this->informationObjectToArray($io);

    $children = $this->getChildren($io->id);

    if (count($children))
    {
      $result['children'] = $children;
    }

    return $result;
  }

  function getChildren($parentId)
  {
    $results = array();

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::PARENT_ID, $parentId);

    $inforationObjects = QubitInformationObject::get($criteria);

    foreach($inforationObjects as $io)
    {
      $item = $this->informationObjectToArray($io);

      $children = $this->getChildren($io->id);

      if (count($children))
      {
        $item['children'] = $children;
      }

      array_push($results, $item);
    }

    return $results;
  }

  private function informationObjectToArray($io)
  {
    $ioData = array(
      'title' => $io->title,
      'identifier' => $io->identifier,
      'slug' => $io->slug
    );

    if (null !== $io->getLevelOfDescription())
    {
      $ioData['level'] = $io->getLevelOfDescription()->getName(array('culture' => 'en'));
    }

    return $ioData;
  }
}
