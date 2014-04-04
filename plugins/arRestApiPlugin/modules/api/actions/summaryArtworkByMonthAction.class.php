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

class ApiSummaryArtworkByMonthAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $data['results'] = $this->getResults();

    return $data;
  }

  protected function getResults()
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    // Get all artwork records
    $queryMatch = new \Elastica\Query\Match;
    $queryMatch->setField(
      'levelOfDescriptionId',
      sfConfig::get('app_drmc_lod_artwork_record_id')
    );
    $queryBool->addShould($queryMatch);

    // Assign query
    $query->setQuery($queryBool);

    // We don't need details, just facet results
    $query->setLimit(0);

    // Add facets to the query to get total level of description types
    $this->facetEsQuery('DateHistogram', 'createdAt', 'createdAt', $query, array('interval' => 'month'));

    // TODO: change createdAt to the acquisition date from TMS
    $this->facetEsQuery('DateHistogram', 'createdAt', 'createdAt', $query, array('interval' => 'month'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    $facets = $resultSet->getFacets();

    // convert timestamps to dates
    foreach($facets['createdAt']['entries'] as $index => $entry)
    {
      $timestamp = $facets['createdAt']['entries'][$index]['time'];
      $facets['createdAt']['entries'][$index]['date'] = date('Y-m-d', $timestamp);
      unset($facets['createdAt']['entries'][$index]['time']);
    }

    return array(
      'creation' => $facets['createdAt']['entries']
    );
  }
}
