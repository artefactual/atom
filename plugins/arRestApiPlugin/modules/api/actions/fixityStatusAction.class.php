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

class ApiFixityStatusAction extends QubitApiAction
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

      $this->addItemToArray($report, 'id', (int)$hit->getId());

      if (isset($doc['success']))
      {
        $report['outcome'] = (bool)$doc['success'];
      }

      $this->addItemToArray($report, 'time_completed', $doc['timeCompleted']);
      $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);
      $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $report['duration'] = $duration;
      }

      $data['lastChecks'][] = $report;
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

      $this->addItemToArray($report, 'id', (int)$hit->getId());

      if (isset($doc['success']))
      {
        $report['outcome'] = (bool)$doc['success'];
      }

      $this->addItemToArray($report, 'time_completed', $doc['timeCompleted']);
      $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);
      $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $report['duration'] = $duration;
      }

      $data['lastFails'][] = $report;
    }

    $data['lastFailsCount'] = $resultSet->getTotalHits();

    // Currently checking
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $filterBool = new \Elastica\Filter\Bool;

    $queryAll = new \Elastica\Query\MatchAll();
    $filterBool->addMust(new \Elastica\Filter\Missing('timeCompleted'));
    $filterBool->addMust(new \Elastica\Filter\Exists('timeStarted'));
    $filteredQuery = new \Elastica\Query\Filtered($queryAll, $filterBool);

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

      $this->addItemToArray($report, 'id', (int)$hit->getId());

      $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);
      $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);

      $data['currentlyChecking'][] = $report;

      // Store session_uuid of currently checking
      if (isset($doc['sessionUuid']))
      {
        $currentlyCheckingSessionUuid = $doc['sessionUuid'];
      }
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
                MAX(time_completed) as max,
                session_uuid
                FROM fixity_report
                GROUP BY session_uuid
                ORDER BY time_started DESC';

    $results = QubitPdo::fetchAll($sql);

    // If there is a currently checking session uuid and match the first result
    // get the time of the last completed check
    if (isset($results[0]))
    {
      if (isset($currentlyCheckingSessionUuid) && $currentlyCheckingSessionUuid == $results[0]->session_uuid)
      {
        if (isset($results[1]))
        {
          $result = $results[1];
        }
      }
      else
      {
        $result = $results[0];
      }
    }

    $data['timeToCheckCollection'] = null;
    if (isset($result->min) && isset($result->max))
    {
      $duration = strtotime($result->max) - strtotime($result->min);
      $data['timeToCheckCollection'] = $duration;
    }

    // If there isn't a currently checking result obtain the time since last check
    if (!isset($data['currentlyChecking']) && isset($result->max))
    {
      // Obtain actual time from UTC timezone (fixity report dates are being sent like that)
      $date = new DateTime('now', new DateTimeZone('UTC'));

      // Using strtotime() with $date->format() because $date->getTimeStamp() changes timezone
      $data['timeSinceLastCheck'] =  strtotime($date->format('Y-m-d H:i:s')) - strtotime($result->max);
    }

    return $data;
  }
}
