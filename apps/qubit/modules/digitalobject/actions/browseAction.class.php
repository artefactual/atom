<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Browse list for digital objects
 *
 * @package    qubit
 * @subpackage digitalobject
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectBrowseAction extends sfAction
{
  public static
    $FACETS = array(
      'digitalObject.mediaTypeId');

  protected function filterQuery($query)
  {
    $this->filters = array();

    $queryTerm = new Elastica_Query_Term();

    foreach ($this->request->getGetParameters() as $param => $value)
    {
      if (in_array(strtr($param, '_', '.'), self::$FACETS))
      {
        foreach (explode(',', $value) as $facetValue)
        {
          // don't include empty filters (querystring sanitization)
          if ('' != preg_replace('/[\s\t\r\n]*/', '', $facetValue))
          {
            $this->filters[$param][] = $facetValue;
            $query->addMust($queryTerm->setTerm(strtr($param, '_', '.'), $facetValue));
          }
        }
      }
    }

    return $query;
  }

  protected function populateFacets()
  {
    if (!$this->pager->hasFacets())
    {
      return false;
    }

    $facets = array();

    foreach ($this->pager->getFacets() as $name => $facet)
    {
      if (isset($facet['terms']))
      {
        $ids = array();
        foreach ($facet['terms'] as $term)
        {
          $ids[$term['term']] = $term['count'];
        }
      }

      switch ($name)
      {
        case 'digitalObject.mediaTypeId':
          $criteria = new Criteria;
          $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

          $mediaTypes = QubitTerm::get($criteria);

          foreach ($mediaTypes as $mediaType)
          {
            $mediaTypeNames[$mediaType->id] = $mediaType->getName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
          }

          foreach ($facet['terms'] as $term)
          {
            $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
              'count' => $term['count'],
              'term' => $mediaTypeNames[$term['term']]);
          }

          break;
      }

      $this->pager->facets = $facets;
    }
  }

  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    // Create query object
    $queryBool = new Elastica_Query_Bool();
    $queryBool->addShould(new Elastica_Query_MatchAll());
    $queryBool->addMust(new Elastica_Query_Term(array('hasDigitalObject' => true)));

    // Filter query with existing facets
    $queryBool = $this->filterQuery($queryBool);

    $query = new Elastica_Query($queryBool);

    // Add facets
    $facet = new Elastica_Facet_Terms('digitalObject.mediaTypeId');
    $facet->setField('digitalObject.mediaTypeId');
    $facet->setSize(50);
    $query->addFacet($facet);

    // Set sort and limit
    $query->setLimit($request->limit);
    $query->setSort(array('_score' => 'desc', 'slug' => 'asc'));

    if (!empty($request->page))
    {
      $query->setFrom(($request->page - 1) * $request->limit);
    }

    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return;
    }

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);

    if ($this->pager->hasResults())
    {
      $this->populateFacets();
    }
  }
}
