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

      // Only add language filter to facets if languages is set
      if ($name != 'languages' && isset($this->request->languages))
      {
        $term = new \Elastica\Filter\Term(array('i18n.languages' => $this->request->languages));
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

    // Filter languages only if the languages facet is being used and languages is set in the request
    if (isset($this::$FACETS['languages']) && isset($this->request->languages))
    {
      $this->filters['languages'] = $this->request->languages;
      $term = new \Elastica\Filter\Term(array($this::$FACETS['languages']['field'] => $this->request->languages));

      $this->filterBool->addMust($term);
    }

    foreach ($this->request->getGetParameters() as $param => $value)
    {
      if ('languages' == $param || !array_key_exists($param, $this::$FACETS) || ('repos' == $param && (!ctype_digit($value) || null === QubitRepository::getById($value))))
      {
        continue;
      }

      foreach (explode(',', $value) as $facetValue)
      {
        // Don't include empty filters
        if (1 === preg_match('/^[\s\t\r\n]*$/', $facetValue))
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

      // Get unique descriptions count for languages facet
      if ($name == 'languages')
      {
        // If the query is being filtered by language we need to execute
        // the same query again without language filter to get the count
        if (isset($this->filters['languages']))
        {
          // We're only filtering draft descriptions and other languages, so:
          // Remove old filter (with language)
          $queryParams = $this->query->getParams();
          unset($queryParams['filter']);
          $this->query->setRawQuery($queryParams);

          // And create and add a new one only with drafts filtered (if needed)
          $this->filterBool = new \Elastica\Filter\Bool;
          QubitAclSearch::filterDrafts($this->filterBool);

          if (0 < count($this->filterBool->toArray()))
          {
            $this->query->setFilter($this->filterBool);
          }

          $resultSetWithoutLanguageFilter = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

          $facets[$name]['terms']['unique'] = array(
            'count' => $resultSetWithoutLanguageFilter->getTotalHits(),
            'term' => 'Unique documents');
        }
        // Without language filter the count equals the number of hits
        else
        {
          $facets[$name]['terms']['unique'] = array(
            'count' => $resultSet->getTotalHits(),
            'term' => 'Unique documents');
        }
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

    if (isset($this->filters['languages']))
    {
      $this->selectedCulture = $this->filters['languages'];
    }
    else
    {
      $this->selectedCulture = $this->context->user->getCulture();
    }
  }
}
