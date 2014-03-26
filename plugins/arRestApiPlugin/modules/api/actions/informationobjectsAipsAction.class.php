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
    $queryBool->addShould(new \Elastica\Query\Term(array('_id' => $this->request->id)));
    $queryBool->addShould(new \Elastica\Query\Term(array('ancestors' => $this->request->id)));
    $queryBool->addMust(new \Elastica\Query\Term(array('hasDigitalObject' => false)));

    // Assign query
    $query->setQuery($queryBool);

    // Filter
    $filterExists = new \Elastica\Filter\Exists('aip');
    $query->setFilter($filterExists);

    // Add facets to the query
    $this->facetEsQuery('TermsStats', 'type', 'aip.type.id', $query, array(
      'valueField' => 'sizeOnDisk'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
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

    $results['unclassified'] = array(
      'count' => $resultSet->getTotalHits() - $totalCount);

    $results['total'] = array(
      'size' => $totalSize,
      'count' => $totalCount);

    return $results;
  }
}
