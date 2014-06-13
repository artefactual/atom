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

class ApiReportsReadAction extends QubitApiAction
{
  protected function get($request)
  {
    if (ctype_digit($request->input))
    {
      try
      {
        $result = QubitSearch::getInstance()->index->getType('QubitSavedQuery')->getDocument($request->input);
      }
      catch (\Elastica\Exception\NotFoundException $e)
      {
        throw new QubitApi404Exception('Report not found');
      }
    }
    else
    {
      $query = new \Elastica\Query;
      $queryBool = new \Elastica\Query\Bool;

      $queryText = new \Elastica\Query\QueryString($request->input);
      $queryText->setFields(array('slug'));

      $queryBool->addMust($queryText);
      $queryBool->addMust(new \Elastica\Query\Term(array('typeId' => sfConfig::get('app_drmc_term_report_id'))));

      $query->setQuery($queryBool);

      $resultSet = QubitSearch::getInstance()->index->getType('QubitSavedQuery')->search($query);

      if ($resultSet->getTotalHits() < 1)
      {
        throw new QubitApi404Exception('Report not found');
      }

      $result = $resultSet->getResults();
      $result = $result[0];
    }

    $doc = $result->getData();
    $report = array();

    $this->addItemToArray($report, 'id', $result->getId());
    $this->addItemToArray($report, 'name', $doc['name']);
    $this->addItemToArray($report, 'type', $doc['scope']);
    $this->addItemToArray($report, 'description', $doc['description']);
    $this->addItemToArray($report, 'range', unserialize($doc['params']));
    $this->addItemToArray($report, 'created_at', $doc['createdAt']);
    $this->addItemToArray($report, 'user_name', $doc['user']['name']);

    return $report;
  }
}
