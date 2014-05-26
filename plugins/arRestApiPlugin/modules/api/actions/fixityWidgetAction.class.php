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

class ApiFixityWidgetAction extends QubitApiAction
{
  protected function get($request)
  {
    return $this->getResults();
  }

  protected function getResults()
  {
    // Last reports
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    $queryAll = new \Elastica\Query\MatchAll();
    $filter = new \Elastica\Filter\Exists('timeCompleted');
    $filteredQuery = new \Elastica\Query\Filtered($queryAll, $filter);

    $queryBool->addMust($filteredQuery);

    $this->prepareEsPagination($query);
    $query->setSort(array('timeStarted' => 'desc'));

    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($query);

    $data = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $report = array();

      if (isset($doc['success']))
      {
        $report['outcome'] = (bool)$doc['success'];
      }

      $this->addItemToArray($report, 'time_completed', $doc['timeCompleted']);

      if (isset($doc['aip']['name']))
      {
        $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);
      }

      if (isset($doc['aip']['uuid'])) {
        $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);
      }

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $this->addItemToArray($report, 'duration', $duration);
      }

      $data['lastChecks'][$hit->getId()] = $report;
    }

    // Last reports failed
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    $queryBool->addMust(new \Elastica\Query\Term(array('success' => false)));

    $this->prepareEsPagination($query);
    $query->setSort(array('timeStarted' => 'desc'));

    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($query);

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $report = array();

      if (isset($doc['success']))
      {
        $report['outcome'] = (bool)$doc['success'];
      }

      if (isset($doc['aip']['uuid'])) {
        $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);
      }

      $this->addItemToArray($report, 'time_completed', $doc['timeCompleted']);

      if (isset($doc['aip']['name']))
      {
        $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);
      }

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $this->addItemToArray($report, 'duration', $duration);
      }

      $data['lastFails'][$hit->getId()] = $report;
    }

    // Currently checking
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    $queryAll = new \Elastica\Query\MatchAll();
    $filter = new \Elastica\Filter\Missing('timeCompleted');
    $filteredQuery = new \Elastica\Query\Filtered($queryAll, $filter);

    $queryBool->addMust($filteredQuery);

    $query->setLimit(1);
    $query->setSort(array('timeStarted' => 'desc'));

    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($query);
    $resultSet = $resultSet->getResults();

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $report = array();

      if (isset($doc['aip']['uuid'])) {
        $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);
      }

      if (isset($doc['aip']['name']))
      {
        $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);
      }

      $data['currentlyChecking'][$hit->getId()] = $report;
    }

    // Checks in 24 hours
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    $now = new DateTime();

    $rangeQuery = new \Elastica\Query\Range('timeStarted', array('gte' => $now->modify('-1 day')->getTimestamp().'000'));
    $queryBool->addMust($rangeQuery);

    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitFixityReport')->search($query);

    $data['checksLastDay'] = $resultSet->getTotalHits();

    // Time to check colection
    $sql  = 'SELECT
                MIN(time_started) as min,
                MAX(time_completed) as max
                FROM fixity_report
                GROUP BY session_uuid
                ORDER BY time_started DESC';

    $result = QubitPdo::fetchOne($sql);

    $data['timeToCheckCollection'] = null;
    if (isset($result->min) && isset($result->max))
    {
      $duration = strtotime($result->max) - strtotime($result->min);
      $data['timeToCheckCollection'] = $duration;
    }

    return $data;
  }
}
