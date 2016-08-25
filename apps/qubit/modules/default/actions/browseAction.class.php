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
        if (isset($this->search->filters['languages']))
        {
          // We're only filtering draft descriptions and other languages, so:
          // Remove old filter (with language)
          $queryParams = $this->search->query->getParams();
          unset($queryParams['filter']);
          $this->search->query->setRawQuery($queryParams);

          // Create and add a new one only with drafts filtered (only for information object queries)
          if ($this::INDEX_TYPE == 'QubitInformationObject')
          {
            $this->filterBool = new \Elastica\Filter\BoolFilter;
            QubitAclSearch::filterDrafts($this->filterBool);

            if (0 < count($this->filterBool->toArray()))
            {
              $this->search->query->setPostFilter($this->filterBool);
            }
          }

          $resultSetWithoutLanguageFilter = QubitSearch::getInstance()->index->getType($this::INDEX_TYPE)->search($this->search->query);

          $count= $resultSetWithoutLanguageFilter->getTotalHits();
        }
        // Without language filter the count equals the number of hits
        else
        {
          $count= $resultSet->getTotalHits();
        }

        $i18n = sfContext::getInstance()->i18n;

        $uniqueTerm = array(
          'unique' => array(
            'count' => $count,
            'term' => $i18n->__('Unique records')));

        // Add unique term at the biginning of the array
        // only when there are other terms
        if (isset($facets[$name]) && count($facets[$name]['terms']))
        {
          $facets[$name]['terms'] = $uniqueTerm + $facets[$name]['terms'];
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
          $this->types[$code] = ucfirst(sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($code));
        }

        break;
    }
  }

  public function execute($request)
  {
    // Force subclassing
    if ('default' == $this->context->getModuleName() && 'browse' == $this->context->getActionName())
    {
      $this->forward404();
    }

    if (array_key_exists('query', $request->getGetParameters()))
    {
      $this->sortSetting = 'relevance'; // If we're searching, by default sort by relevance
    }
    else if ($this->getUser()->isAuthenticated())
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

    $this->limit = sfConfig::get('app_hits_per_page');
    if (isset($request->limit) && ctype_digit($request->limit))
    {
      $this->limit = $request->limit;
    }

    $skip = 0;
    if (isset($request->page) && ctype_digit($request->page))
    {
      $skip = ($request->page - 1) * $this->limit;
    }

    $this->search = new arElasticSearchPluginQuery($this->limit, $skip);

    if (property_exists($this, 'FACETS'))
    {
      if (!isset($this->getParameters))
      {
        $this->getParameters = $request->getGetParameters();
      }

      $this->search->addFacets($this::$FACETS);
      $this->search->addFacetFilters($this::$FACETS, $this->getParameters);
    }

    if (isset($this->search->filters['languages']))
    {
      $this->selectedCulture = $this->search->filters['languages'];
    }
    else
    {
      $this->selectedCulture = $this->context->user->getCulture();
    }
  }
}
