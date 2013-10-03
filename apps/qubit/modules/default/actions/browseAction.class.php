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
    foreach ($this::$FACETS as $name => $item)
    {
      if (!is_array($item))
      {
        $facet = new \Elastica\Facet\Terms($item);
        $facet->setField($item);
        $facet->setSize(10);

        $this->query->addFacet($facet);

        continue;
      }

      switch ($item['type'])
      {
        case 'range':
          $facet = new \Elastica\Facet\Range($name);
          $facet->setField($item['field']);
          $facet->addRange($item['from'], $item['to']);

          break;

        case 'term':
          $facet = new \Elastica\Facet\Terms($name);
          $facet->setField($item['field']);

          break;

        case 'query':
          $facet = new \Elastica\Facet\Query($name);
          $facet->setQuery(new \Elastica\Query\Term($item['field']));

          break;
      }

      // Sets the amount of terms to be returned
      if (isset($item['size']))
      {
        $facet->setSize($item['size']);
      }

      $filter = new \Elastica\Filter\Bool;

      // Sets a filter for this facet
      if (isset($item['filter']))
      {
        switch ($item['filter'])
        {
          case 'hideDrafts':
            QubitAclSearch::filterDrafts($filter);

          break;
        }
      }

      if ($name != 'languages')
      {
        $code = $this->request->getParameter('languages', sfConfig::get('sf_default_culture'));
        $term = new \Elastica\Filter\Term(array('i18n.languages' => $code));
        $filter->addMust($term);
      }

      // Apply facet filter if exists
      if (0 < count($filter->toArray()))
      {
        $facet->setFilter($filter);
      }

      $this->query->addFacet($facet);
    }
  }

  protected function addFilters()
  {
    $this->filters = array();

    // Filter languages only if the languages facet is being used
    if (isset($this::$FACETS['languages']))
    {
      $code = $this->request->getParameter('languages', sfConfig::get('sf_default_culture'));
      $this->filters['languages'] = $code;
      $term = new \Elastica\Filter\Term(array($this::$FACETS['languages']['field'] => $code));

      $this->filterBool->addMust($term);
    }

    foreach ($this->request->getGetParameters() as $param => $value)
    {
      if ('languages' == $param || !array_key_exists($param, $this::$FACETS))
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

        $term = new \Elastica\Query\Term(array($this::$FACETS[$param]['field'] => $facetValue));

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
      if (!isset($facet['terms']) && !isset($facet['count']))
      {
        continue;
      }

      if (isset($this::$FACETS[$name]['populate']) && false === $this::$FACETS[$name]['populate'])
      {
        $facets[$name] = $facet;

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
        $facets[$name]['terms'][$term['term']] = array(
          'count' => $term['count'],
          'term' => $this->types[$term['term']]);
      }
    }

    $this->pager->facets = $facets;
  }

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'languages':
        foreach ($ids as $code => $count)
        {
          $this->types[$code] = sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($code);
        }

        break;
    }
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

    if (!isset($request->sort))
    {
      $request->sort = $this->sortSetting;
    }

    $this->query = new \Elastica\Query();
    $this->query->setLimit($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new \Elastica\Query\Bool();
    $this->filterBool = new \Elastica\Filter\Bool;

    if (isset($this::$FACETS))
    {
      $this->addFacets();

      $this->addFilters();
    }
  }
}
