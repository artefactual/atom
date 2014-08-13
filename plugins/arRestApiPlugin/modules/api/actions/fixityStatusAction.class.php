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

      $this->addItemToArray($report, 'time_completed', arRestApiPluginUtils::convertDate($doc['timeCompleted']));
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

    $failsRecovered = 0;

    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $report = array();

      $this->addItemToArray($report, 'id', (int)$hit->getId());

      if (isset($doc['success']))
      {
        $report['outcome'] = (bool)$doc['success'];
      }

      $this->addItemToArray($report, 'time_completed', arRestApiPluginUtils::convertDate($doc['timeCompleted']));
      $this->addItemToArray($report, 'aip_name', $doc['aip']['name']);
      $this->addItemToArray($report, 'aip_uuid', $doc['aip']['uuid']);

      if (isset($doc['timeCompleted']) && isset($doc['timeStarted']))
      {
        $duration = strtotime($doc['timeCompleted']) - strtotime($doc['timeStarted']);
        $report['duration'] = $duration;
      }

      // Determine whether a recovery, for this AIP, is awaiting approval
      $criteria = new Criteria;

      $criteria->add(QubitFixityRecovery::AIP_ID, $doc['aip']['id']);
      $criteria->add(QubitFixityRecovery::TIME_COMPLETED, null, Criteria::ISNULL);

      $report['recoveryPending'] = (null != QubitFixityRecovery::getOne($criteria));

      // Add result of most recent recovery resolved for this AIP (if not awaiting administrator approval)
      $criteria = new Criteria;

      $criteria->add(QubitFixityRecovery::AIP_ID, $doc['aip']['id']);
      $criteria->add(QubitFixityRecovery::TIME_COMPLETED, null, Criteria::ISNOTNULL);
      $criteria->addDescendingOrderByColumn(QubitFixityRecovery::ID);

      if (null != ($recovery = QubitFixityRecovery::getOne($criteria)) && null != $recovery->timeCompleted)
      {
        $report['lastRecoveryResolved'] = array(
          'outcome' => (bool)$recovery->success,
          'message' => $this->simplifyRestoreResponseMessage($recovery->message),
          'timeStarted' => $recovery->timeStarted,
          'timeCompleted' => $recovery->timeCompleted
        );

        // Note if last recovery occurred after failure was reported and add to fails recovered total
        $reportTimestamp = strtotime($doc['timeStarted']);
        $recoveredTimestamp = strtotime($recovery->timeStarted);

        if ($recovery->success && ($recoveredTimestamp > $reportTimestamp))
        {
          $report['lastRecoveryResolved']['fixesFailure'] = true;
          $failsRecovered++;
        }
      }

      $data['lastFails'][] = $report;
    }

    $data['unrecoveredFailsCount'] = $resultSet->getTotalHits() - $failsRecovered;

    // Currently checking
    $sql  = 'SELECT
                fix.id,
                fix.uuid,
                fix.session_uuid,
                aip.filename
                FROM fixity_report fix
                LEFT JOIN aip
                ON fix.aip_id = aip.id
                WHERE fix.time_started =
                  (SELECT MAX(time_started) FROM fixity_report)
                AND fix.time_completed IS NULL';

    if (false !== $currentlyChecking = QubitPdo::fetchOne($sql))
    {
      $report = array();

      $this->addItemToArray($report, 'id', $currentlyChecking->id);

      $this->addItemToArray($report, 'aip_uuid', $currentlyChecking->uuid);
      $this->addItemToArray($report, 'aip_name', $currentlyChecking->filename);

      $data['currentlyChecking'][] = $report;

      // Store session_uuid of currently checking
      if (isset($currentlyChecking->session_uuid))
      {
        $currentlyCheckingSessionUuid = $currentlyChecking->session_uuid;
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
                HAVING COUNT(*) > 1
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
      $now = new DateTime('now');

      // Using strtotime() with $now->format() because $now->getTimeStamp() changes timezone
      $data['timeSinceLastCheck'] =  strtotime($now->format('Y-m-d H:i:s')) - strtotime($result->max);
    }

    return $data;
  }

  protected function simplifyRestoreResponseMessage($message)
  {
    if (strpos($message, 'APPROVE:') === 0)
    {
      $message = 'Recovery successful';
    } else if (strpos($message, 'REJECT:') === 0)
    {
      $message = 'Recovery rejected';
    } else if (strpos($message, 'APPROVE (failed):') === 0)
    {
      $message = 'Recovery failed';
    }

    return $message;
  }
}
