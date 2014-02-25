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

class APIInformationObjectsBrowseAction extends QubitAPIAction
{
  protected function get($request)
  {
    return array(
      'results' => $this->getResults()
    );
  }

  protected function getResults()
  {
    $query = new \Elastica\Query();
    $queryBool = new \Elastica\Query\Bool();

    // Limit
    if (isset($request->limit) && ctype_digit($this->request->limit))
    {
      $this->query->setLimit($this->request->limit);
    }

    // Skip
    if (isset($request->skip) && ctype_digit($this->request->skip))
    {
      $this->query->setFrom($this->request->skip);
    }

    // Sort and direction, default: filename, asc
    if (!isset($this->request->sort))
    {
      $this->request->sort = 'filename';
    }


    if (!isset($this->request->sort_direction))
    {
      $this->request->sort_direction = 'asc';
    }

    $query->setSort(array($this->request->sort => $this->request->sort_direction));
    $query->setFields(array(
      'slug',
      'identifier',
      'inheritReferenceCode',
      'levelOfDescriptionId',
      'publicationStatusId',
      'ancestors',
      'parentId',
      'hasDigitalObject',
      'createdAt',
      'updatedAt',
      'sourceCulture',
      'i18n'));

    // Query
    $queryBool->addMust(new \Elastica\Query\MatchAll());

    // Filter: level of description
    if (isset($this->request->levelOfDescriptionId) && ctype_digit($this->request->levelOfDescriptionId))
    {
      $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => $this->request->levelOfDescriptionId)));
    }
    else if (isset($this->request->levelOfDescription) && is_string($this->request->levelOfDescription))
    {
      switch ($this->request->levelOfDescription)
      {
        case 'work':
          $levelId = 181;

          break;

        case 'technology-record':
          $levelId = 182;

          break;

        case 'physical-component':
          $levelId = 183;

          break;

        case 'digital-object':
          $levelId = 184;

          break;

        case 'description':
          $levelId = 185;

          break;
      }

      $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => $levelId)));
    }

    $query->setQuery($queryBool);
    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    // Build array from results
    $results = array();
    foreach ($resultSet as $hit)
    {
      $results[$hit->getId()] = $hit->getFields();
    }

    return
      array(
        'total_hits' => $resultSet->getTotalHits(),
        'contents' => $results);
  }
}
