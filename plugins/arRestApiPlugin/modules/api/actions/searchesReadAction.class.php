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

class ApiSearchesReadAction extends QubitApiAction
{
  protected function get($request)
  {
    try
    {
      $result = QubitSearch::getInstance()->index->getType('QubitDrmcQuery')->getDocument($this->request->id);
    }
    catch (\Elastica\Exception\NotFoundException $e)
    {
      throw new QubitApi404Exception('Search not found');
    }

    $doc = $result->getData();
    $search = array();

    $this->addItemToArray($search, 'id', $result->getId());
    $this->addItemToArray($search, 'name', $doc['name']);
    $this->addItemToArray($search, 'type', $doc['type']);
    $this->addItemToArray($search, 'description', $doc['description']);
    $this->addItemToArray($search, 'criteria', unserialize($doc['query']));

    return $search;
  }
}
