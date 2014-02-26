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

class APIAIPsBrowseAction extends QubitAPIAction
{
  protected function get($request)
  {
    $data = array();

    $results = $this->getResults();
    $data['results'] = $results['results'];
    $data['facets'] = $results['facets'];
    $data['overview'] = $this->getOverview();

    return $data;
  }

  protected function getResults()
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Pagination and sort
    $this->prepareEsQueryBasic($query);

    // Filter selected facets
    $this->filterEsFacet('type', 'type.id', $queryBool);

    // Add facets to the query
    $this->facetEsQuery('Terms', 'type', 'type.id', $query);

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($query);

    $data = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $aip = array();
      $aip['id'] = $hit->getId();
      $aip['name'] = $doc['filename'];
      $aip['uuid'] = $doc['uuid'];
      $aip['size'] = $doc['sizeOnDisk'];
      $aip['created_at'] = $doc['createdAt'];
      $aip['type']['id'] = $doc['type']['id'];
      $aip['type']['name'] = get_search_i18n($doc['type'], 'name');
      $aip['part_of']['id'] = $doc['partOf']['id'];
      $aip['part_of']['title'] = get_search_i18n($doc['partOf'], 'title');

      $data['results'][] = $aip;
    }

    // Facets
    $facets = $resultSet->getFacets();
    $this->populateFacets($facets);
    $data['facets'] = $facets;

    return $data;
  }

  protected function getOverview()
  {
    // TODO
    return array(
      'total' => array(
        'size' => 1062650070958,
        'count' => 16),
      179 => array(
        'size' => 332430468710,
        'count' => 8),
      180 => array(
        'size' => 85899345920,
        'count' => 4),
      181 => array(
        'size' => 85899345920,
        'count' => 4),
      182 => array(
        'size' => 85899345920,
        'count' => 4),
      'unclassified' => array(
        'size' => 418115066265,
        'count' => 2));
  }

  protected function getFacetLabel($name, $term)
  {
    if ($name === 'type')
    {
      if (null !== $item = QubitTerm::getById($term))
      {
        return $item->getName(array('cultureFallback' => true));
      }
    }
  }
}
