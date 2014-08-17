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

class ApiFixityBrowseAction extends QubitApiAction
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
    $data['last_recovery'] = array();

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $report = array();
      $report['id'] = $hit->getId();

      // If status for a specific AIP is being requested, check if a recovery is pending
      if (isset($this->request->uuid) && !isset($data['last_recovery']['pending']))
      {
        $data['last_recovery']['pending'] = arRestApiPluginUtils::aipIsPendingRecovery($doc['aip']['id']);
      }

      if (isset($doc['success']))
      {
        $report['success'] = (bool)$doc['success'];

        $recovery = arRestApiPluginUtils::getMostRecentAipRecoveryAttempt($doc['aip']['id']);

        // If info for a specific AIP is being requested, add last recovery's details to feed
        if (isset($this->request->uuid) && !isset($data['last_recovery']['message']))
        {
          $data['last_recovery']['message'] = $recovery->message;
          $data['last_recovery']['time_started'] = $recovery->timeStarted;
          $data['last_recovery']['time_completed'] = $recovery->timeCompleted;
          $data['last_recovery']['success'] = (bool)$recovery->success;
        }

        $report['recovery_needed'] = !arRestApiPluginUtils::aipIsPendingRecovery($doc['aip']['id']) && !arRestApiPluginUtils::recoveryResolvesFailureReport($doc['timeStarted'], $recovery);
      }

      $this->addItemToArray($report, 'message', $doc['message']);

      $this->addItemToArray($report, 'time_started', arRestApiPluginUtils::convertDate($doc['timeStarted']));
      $this->addItemToArray($report, 'time_completed', arRestApiPluginUtils::convertDate($doc['timeCompleted']));

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $report['duration'] = $duration;
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

      $data['results'][] = $report;
    }

    // Total this
    $data['total'] = $resultSet->getTotalHits();

    return $data;
  }
}
