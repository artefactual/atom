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

    // get all artwork records
    $queryMatch = new \Elastica\Query\Match;
    $queryMatch->setField('levelOfDescriptionId', 358);
    $queryBool->addShould($queryMatch);

    // Assign query
    $query->setQuery($queryBool);

    // We don't need details, just facet results
    $query->setLimit(0);

    // Add facets to the query to get total level of description types
    $this->facetEsQuery('DateHistogram', 'createdAt', 'createdAt', $query, array('interval' => 'month'));
    $this->facetEsQuery('DateHistogram', 'createdAt', 'createdAt', $query, array('interval' => 'month'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);


    // Derive level of description type cound from facets
    $counts = array();
    $levelOfDescriptionInfo = array(
      358 => 'Artwork record',
      359 => 'Supporting technology record'
    );
    $facets = $resultSet->getFacets();
return $facets;

    foreach($facets['levelOfDescriptionId']['terms'] as $term) {
      $termId = $term['term'];
      if (isset($levelOfDescriptionInfo[$termId]))
      {
        $description = $levelOfDescriptionInfo[$termId];
        $counts[$description] = $term['count'];
      }
    }
    return $counts;

    $this->populateFacets($facets);
    $data['facets'] = $facets;

    return
      array(
        'counts' => $counts
      );
  }
}
