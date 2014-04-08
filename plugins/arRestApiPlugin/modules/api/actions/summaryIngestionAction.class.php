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

class ApiSummaryIngestionAction extends QubitApiAction
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
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Assign query
    $query->setQuery($queryBool);

    // We don't need details, just facet rsults
    $query->setLimit(0);

    // Add facets to the query to get total level of description types
    $this->facetEsQuery('Terms', 'levelOfDescriptionId', 'levelOfDescriptionId', $query);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    // Derive level of description type count from facets
    $counts = array();

    $levelOfDescriptionInfo = array(
      sfConfig::get('app_drmc_lod_artwork_record_id')               => 'artwork',
      sfConfig::get('app_drmc_lod_supporting_technology_record_id') => 'supporting_technology',
      sfConfig::get('app_drmc_lod_component_id')                    => 'component'
    );

    $facets = $resultSet->getFacets();

    foreach($facets['levelOfDescriptionId']['terms'] as $term) {
      $termId = $term['term'];
      if (isset($levelOfDescriptionInfo[$termId]))
      {
        $description = $levelOfDescriptionInfo[$termId];
        $counts[$description] = $term['count'];
      }
    }

    return $counts;
  }
}
