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

class DefaultBrowseAction extends sfAction
{
  protected function addFacets()
  {
    foreach ($this::$FACETS as $item)
    {
      if (!is_array($item))
      {
        $facet = new Elastica_Facet_Terms($item);
        $facet->setField($item);
        $facet->setSize(50);
      }
      else
      {
        switch ($item['type'])
        {
          case 'range':
            $facet = new Elastica_Facet_Range($item['name']);
            $facet->setField($item['name']);
            $facet->addRange($item['from'], $item['to']);
        }
      }

      $this->query->addFacet($facet);
    }
  }

  protected function addFilters()
  {
    $this->filters = array();

    foreach ($this->request->getGetParameters() as $param => $value)
    {
      if (!in_array(strtr($param, '_', '.'), $this::$FACETS))
      {
        continue;
      }

      foreach (explode(',', $value) as $facetValue)
      {
        // Don't include empty filters
        if ('' == preg_replace('/[\s\t\r\n]*/', '', $facetValue))
        {
          continue;
        }

        $this->filters[$param][] = $facetValue;

        $term = new Elastica_Query_Term(array(strtr($param, '_', '.') => $facetValue));

        $this->queryBool->addMust($term);
      }
    }
  }

  protected function populateFacets($resultSet)
  {
    // Stop if no facets available
    if (!$resultSet->hasFacets())
    {
      return;
    }

    // Create a map of facets containing the id and its string representation
    $this->types = array();

    $facets = array();

    foreach ($resultSet->getFacets() as $name => $facet)
    {
      // Pass if the facet is empty
      if (!isset($facet['terms']))
      {
        continue;
      }

      // Build a map of facet results
      $ids = array();
      foreach ($facet['terms'] as $item)
      {
        $ids[$item['term']] = $item['count'];
      }

      $this->populateFacet($name, $ids);

      foreach ($facet['terms'] as $term)
      {
        $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
          'count' => $term['count'],
          'term' => $this->types[$term['term']]);
      }
    }

    $this->pager->facets = $facets;
  }

  public function execute($request)
  {
    if (empty($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if ($this->getUser()->isAuthenticated())
    {
      $this->sortSetting = sfConfig::get('app_sort_browser_user');
    }
    else
    {
      $this->sortSetting = sfConfig::get('app_sort_browser_anonymous');
    }

    $this->query = new Elastica_Query();
    $this->query->setLimit($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new Elastica_Query_Bool();

    if (isset($this::$FACETS))
    {
      $this->addFacets();

      $this->addFilters();
    }
  }
}
