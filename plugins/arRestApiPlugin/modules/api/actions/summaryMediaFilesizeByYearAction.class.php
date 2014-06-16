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

class ApiSummaryMediaFilesizeByYearAction extends QubitApiAction
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

    // Use a term stats facet to calculate total bytes used per media category
    $facetName = 'collection_year_file_stats';
    $this->facetEsQuery('TermsStats', $facetName, 'tmsObject.collectionYear', $query, array('valueField' => 'digitalObject.byteSize'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    $facets = $resultSet->getFacets();

    foreach($facets[$facetName]['terms'] as $index => $term)
    {
      // take note of average
      $average = $term['mean'];
      $facets[$facetName]['terms'][$index]['average'] = $average;

      // convert millisecond timestamp to human-readable
      $facets[$facetName]['terms'][$index]['year'] = intval($term['term']);

      // strip out extra data
      foreach(array('count', 'total_count', 'min', 'max', 'mean', 'term', 'total') as $element)
      {
        unset($facets[$facetName]['terms'][$index][$element]);
      }
    }

    // sort by year
    function compare_year($a, $b)
    {
      return $a['year'] > $b['year'];
    }

    usort($facets[$facetName]['terms'], 'compare_year');

    return $facets[$facetName]['terms'];
  }
}
