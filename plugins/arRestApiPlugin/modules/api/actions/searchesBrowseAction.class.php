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

class ApiSearchesBrowseAction extends QubitApiAction
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
    // Create query objects
    $query = new \Elastica\Query;
    $filterBool = new \Elastica\Filter\Bool;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $this->prepareEsSorting($query, array(
      'name' => 'name',
      'description' => 'description',
      'createdAt' => 'createdAt',
      'updatedAt' => 'updatedAt',
      'type' => 'type',
      'user' => 'user.name'));

    // Filter selected facets
    $this->filterEsFacet('user', 'user.id', $filterBool);
    $this->filterEsFacet('type', 'type', $filterBool, 'AND', array('noInteger' => true));

    $this->filterEsRangeFacet('createdFrom', 'createdTo', 'createdAt', $queryBool);
    $this->filterEsRangeFacet('updatedFrom', 'updatedTo', 'updatedAt', $queryBool);

    // Add facets to the query
    $this->facetEsQuery('Terms', 'type', 'type', $query);
    $this->facetEsQuery('Terms', 'user', 'user.id', $query);

    // Type facet labels
    $this->typeLabels = array(
      'aips' => 'AIPs',
      'works' => 'Artwork records',
      'technology-records' => 'Supporting technology records',
      'components' => 'Components',
      'files' => 'Files');

    $now = new DateTime();
    $now->setTime(0, 0);

    $dateRanges = array(
      array('to' => $now->modify('-1 year')->getTimestamp().'000'),
      array('from' => $now->getTimestamp().'000'),
      array('from' => $now->modify('+11 months')->getTimestamp().'000'),
      array('from' => $now->modify('+1 month')->modify('-7 days')->getTimestamp().'000'));

    $this->dateRangesLabels = array(
      'Older than a year',
      'From last year',
      'From last month',
      'From last week');

    $this->facetEsQuery('Range', 'dateCreated', 'createdAt', $query, array('ranges' => $dateRanges));
    $this->facetEsQuery('Range', 'dateUpdated', 'updatedAt', $query, array('ranges' => $dateRanges));

    // Filter query
    if (isset($this->request->query) && 1 !== preg_match('/^[\s\t\r\n]*$/', $this->request->query))
    {
      $queryFields = array(
        'name.autocomplete',
        'type',
        'user.name',
        'description'
      );

      $queryText = new \Elastica\Query\QueryString($this->request->query);
      $queryText->setFields($queryFields);

      $queryBool->addMust($queryText);
    }

    // Set filter
    if (0 < count($filterBool->toArray()))
    {
      $query->setFilter($filterBool);
    }

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitDrmcQuery')->search($query);

    $data = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();

      $search = array();

      $this->addItemToArray($search, 'id', $hit->getId());
      $this->addItemToArray($search, 'name', $doc['name']);
      $this->addItemToArray($search, 'type', $doc['type']);
      $this->addItemToArray($search, 'description', $doc['description']);
      $this->addItemToArray($search, 'created_at', $doc['createdAt']);
      $this->addItemToArray($search, 'updated_at', $doc['updatedAt']);
      $this->addItemToArray($search, 'slug', $doc['slug']);
      $this->addItemToArray($search, 'criteria', unserialize($doc['query']));
      $this->addItemToArray($search['user'], 'id', $doc['user']['id']);
      $this->addItemToArray($search['user'], 'name', $doc['user']['name']);

      $data['results'][$hit->getId()] = $search;
    }

    // Facets
    $facets = $resultSet->getFacets();
    $this->populateFacets($facets);
    $data['facets'] = $facets;

    // Total this
    $data['total'] = $resultSet->getTotalHits();

    return $data;
  }

  protected function getOverview()
  {
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    $this->facetEsQuery('Terms', 'type', 'type', $query);

    $query->setQuery($queryBool);
    $query->setSort(array('createdAt' => 'desc'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitDrmcQuery')->search($query);
    $facets = $resultSet->getFacets();
    $this->populateFacets($facets);

    $results = array();

    // Totals by entity
    foreach ($facets['type']['terms'] as $facet)
    {
      $results['counts'][$facet['label'].' searches'] = $facet['count'];
    }

    // Total searches
    $results['counts']['Total searches'] = $resultSet->getTotalHits();

    // Last created
    $esResullts = $resultSet->getResults();
    $lastCreated = $esResullts[0]->getData();

    $results['latest']['Last search added']['date'] = $lastCreated['createdAt'];
    $results['latest']['Last search added']['user'] = $lastCreated['user']['name'];
    $results['latest']['Last search added']['name'] = $lastCreated['name'];
    $results['latest']['Last search added']['slug'] = $lastCreated['slug'];

    // Last updated
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    $query->setQuery($queryBool);
    $query->setSort(array('updatedAt' => 'desc'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitDrmcQuery')->search($query);

    $esResullts = $resultSet->getResults();
    $lastUpdated = $esResullts[0]->getData();

    $results['latest']['Last search modified']['date'] = $lastUpdated['createdAt'];
    $results['latest']['Last search modified']['user'] = $lastUpdated['user']['name'];
    $results['latest']['Last search modified']['name'] = $lastUpdated['name'];
    $results['latest']['Last search modified']['slug'] = $lastCreated['slug'];

    return $results;
  }

  protected function getFacetLabel($name, $id)
  {
    if ($name === 'user')
    {
      if (null !== $item = QubitUser::getById($id))
      {
        return $item->getUsername(array('cultureFallback' => true));
      }
    }

    if ($name === 'type')
    {
      return $this->typeLabels[$id];
    }

    if ($name === 'dateCreated' || $name === 'dateUpdated')
    {
      return $this->dateRangesLabels[$id];
    }
  }
}
