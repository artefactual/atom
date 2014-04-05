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

class ApiSummaryMediaCategoryCountAction extends QubitApiAction
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

    // Get all information objects
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Assign query
    $query->setQuery($queryBool);

    // We don't need details, just facet results
    $query->setLimit(0);

    $this->facetEsQuery('Terms', 'media_type_count', 'digitalObject.mimeType', $query);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    $facets = $resultSet->getFacets();

    foreach($facets['media_type_count']['terms'] as $index => $term)
    {
      $mediaType = $term['term'];
      $facets['media_type_count']['terms'][$index]['media_type'] = $mediaType;
      unset($facets['media_type_count']['terms'][$index]['term']);
    }

    return $facets['media_type_count']['terms'];
  }
}
