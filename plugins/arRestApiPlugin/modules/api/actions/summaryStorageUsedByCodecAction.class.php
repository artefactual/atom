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

class ApiSummaryStorageUsedByCodecAction extends QubitApiAction
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

    // Use a term stats facet to calculate total bytes used per codec type
    $codecTypes = array(
      'general_track_stats' => 'generalTracks',
      'video_track_stats'   => 'videoTracks',
      'audio_track_stats'   => 'audioTracks'
    );

    // Add facet for each codec type
    foreach($codecTypes as $facetName => $mediaInfoPropName)
    {
      $this->facetEsQuery('TermsStats', $facetName, 'digitalObjects.metsData.mediainfo.'. $mediaInfoPropName .'.codec', $query, array('valueField' => 'digitalObjects.digitalObject.byteSize'));
    }

    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($query);
    }
    catch (Exception $e)
    {
      return array();
    }

    $facets = $resultSet->getFacets();

    // Amalgamate facet data for each codec type
    $results = array();

    foreach($codecTypes as $facetName => $mediaInfoPropName)
    {
      foreach($facets[$facetName]['terms'] as $index => $term)
      {
        $mediaType = $term['term'];
        $facets[$facetName]['terms'][$index]['codec'] = strtoupper($mediaType);

        // strip out extra data
        foreach(array('count', 'total_count', 'min', 'max', 'mean', 'term') as $element) {
          unset($facets[$facetName]['terms'][$index][$element]);
        }
      }

      $results = array_merge($results, $facets[$facetName]['terms']);
    }

    return $results;
  }
}
