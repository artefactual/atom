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

class ApiFixityReadAction extends QubitApiAction
{
  protected function get($request)
  {
    return $this->getResults();
  }

  protected function getResults()
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    if (isset($this->request->uuid))
    {
      $queryText = new \Elastica\Query\QueryString($this->request->uuid);
      $queryText->setFields(array('uuid'));

      $queryBool->addMust($queryText);
    }
    else
    {
      $queryBool->addMust(new \Elastica\Query\MatchAll);
    }

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $query->setSort(array('timeStarted' => 'desc'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($query);

    $data = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $report = array();

      if (isset($doc['success']))
      {
        $report['success'] = (bool)$doc['success'];
      }

      $this->addItemToArray($report, 'message', $doc['message']);
      $this->addItemToArray($report, 'time_started', $doc['timeStarted']);
      $this->addItemToArray($report, 'time_completed', $doc['timeCompleted']);

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $this->addItemToArray($report, 'duration', $duration);
      }

      if (isset($doc['failures']))
      {
        foreach ($doc['failures'] as $esFail)
        {
          $fail = array();

          $this->addItemToArray($fail, 'type', $esFail['type']);
          $this->addItemToArray($fail, 'path', $esFail['path']);
          $this->addItemToArray($fail, 'hash_type', $esFail['hashType']);
          $this->addItemToArray($fail, 'expected_hash', $esFail['expectedHash']);
          $this->addItemToArray($fail, 'actual_hash', $esFail['actualHash']);
          $this->addItemToArray($fail, 'message', $esFail['message']);

          $report['failures'][] = $fail;
        }
      }

      $data['results'][$hit->getId()] = $report;
    }

    // Total this
    $data['total'] = $resultSet->getTotalHits();

    return $data;
  }
}
