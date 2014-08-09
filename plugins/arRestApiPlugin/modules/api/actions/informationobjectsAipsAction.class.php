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

class ApiInformationObjectsAipsAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $results = $this->getResults();
    $data['results'] = $results['results'];
    $data['facets'] = $results['facets'];
    $data['total'] = $results['total'];
    $data['overview'] = $this->getOverview();

    return $data;
  }

  protected function getResults()
  {
    // TODO
    return array();
  }

  protected function getOverview()
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\Term(array('partOf.id' => $this->request->id)));

    // Add facets to the query
    $this->facetEsQuery('TermsStats', 'type', 'type.id', $query, array(
      'valueField' => 'sizeOnDisk'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($query);
    $facets = $resultSet->getFacets();

    $results = array();
    $totalSize = $totalCount = 0;
    foreach ($facets['type']['terms'] as $facet)
    {
      $results[$facet['term']] = array(
        'size' => $facet['total'],
        'count' => $facet['count']);

      $totalSize += $facet['total'];
      $totalCount += $facet['count'];
    }

    // Get unclassified counts (missing type.id)
    $results['unclassified']['count'] = $results['unclassified']['size'] = 0;
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      if (isset($doc['type']['id']))
      {
        continue;
      }

      $results['unclassified']['size'] += $doc['sizeOnDisk'];
      $results['unclassified']['count'] ++;
    }

    $results['total'] = array(
      'size' => $totalSize + $results['unclassified']['size'],
      'count' => $totalCount + $results['unclassified']['count']);

    return $results;
  }
}
