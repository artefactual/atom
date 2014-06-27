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

class ApiActorsBrowseAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $results = $this->getResults();
    $data['results'] = $results['results'];
    $data['facets'] = $results['facets'];
    $data['total'] = $results['total'];

    return $data;
  }

  protected function getResults()
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $this->prepareEsSorting($query, array(
      'createdAt' => 'createdAt'));

    // Filter query
    if (isset($this->request->query) && 1 !== preg_match('/^[\s\t\r\n]*$/', $this->request->query))
    {
      $queryString = new \Elastica\Query\QueryString($this->request->query);
      $queryString->setDefaultOperator('OR');

      $queryBool->addMust($queryString);
    }

    // Limit fields
    $query->setFields(array(
      'slug',
      'createdAt',
      'updatedAt',
      'sourceCulture',
      'i18n'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitActor')->search($query);

    // Build array from results
    $results = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $result = array();

      $this->addItemToArray($result, 'slug', $doc['slug']);
      $this->addItemToArray($result, 'created_at', arRestApiPluginUtils::convertDate($doc['createdAt']));
      $this->addItemToArray($result, 'updated_at', arRestApiPluginUtils::convertDate($doc['updatedAt']));
      $this->addItemToArray($result, 'authorized_form_of_name', get_search_i18n($doc, 'authorizedFormOfName'));

      $results[$hit->getId()] = $result;
    }

    $facets = array();

    return
      array(
        'total' => $resultSet->getTotalHits(),
        'facets' => $facets,
        'results' => $results);
  }
}
