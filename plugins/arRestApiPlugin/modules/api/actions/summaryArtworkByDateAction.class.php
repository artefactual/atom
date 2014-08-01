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

class ApiSummaryArtworkByDateAction extends QubitApiAction
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

    // Add facets to the months in which artwork records were collected and created
    $this->facetEsQuery('DateHistogram', 'collectionDate', 'tmsObject.dateCollected', $query, array('interval' => 'year'));
    $this->facetEsQuery('DateHistogram', 'createdAt', 'createdAt', $query, array('interval' => 'month'));

    // Return empty results if search fails
    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
    }
    catch (Exception $e)
    {
      return array(
        'creation' => array(),
        'collection' => array()
      );
    }

    $facets = $resultSet->getFacets();

    // convert timestamps to month indicators and calculate running total
    foreach($facets as $facetName => $facet)
    {
      $total = 0;

      foreach($facets[$facetName]['entries'] as $index => $entry)
      {
        // calculate running total
        $total += $entry['count'];
        $facets[$facetName]['entries'][$index]['count'] = $entry['count'];
        $facets[$facetName]['entries'][$index]['total'] = $total;

        // convert millisecond timestamps to YYYY-MM format
        $timestamp = $entry['time'] / 1000;
        $facets[$facetName]['entries'][$index]['year'] = substr(date('Y-m-d', $timestamp), 0, 4);

        if ($facetName == 'createdAt')
        {
          $timestamp = $entry['time'] / 1000;
          $facets[$facetName]['entries'][$index]['month'] = substr(date('Y-m-d', $timestamp), 5, 2);
        }

        unset($facets[$facetName]['entries'][$index]['time']);
      }
    }

    return array(
      'creation' => $facets['createdAt']['entries'],
      'collection' => $facets['collectionDate']['entries']
    );
  }
}
