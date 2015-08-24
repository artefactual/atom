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
            $this->filterBool = new \Elastica\Filter\Bool;
            QubitAclSearch::filterDrafts($this->filterBool);

            if (0 < count($this->filterBool->toArray()))
            {
              $this->search->query->setFilter($this->filterBool);
            }
          }

          $resultSetWithoutLanguageFilter = QubitSearch::getInstance()->index->getType($this::INDEX_TYPE)->search($this->search->query);

          $facets[$name]['terms']['unique'] = array(
            'count' => $resultSetWithoutLanguageFilter->getTotalHits(),
            'term' => 'Unique records');
        }
        // Without language filter the count equals the number of hits
        else
        {
          $facets[$name]['terms']['unique'] = array(
            'count' => $resultSet->getTotalHits(),
            'term' => 'Unique records');
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

  /**
   * Determine url parameters based on if the "top-level descriptions" or "all descriptions"
   * radio buttons are selected. Also determine which radio button is 'checked'.
   *
   * Filter out non-top level descriptions from the ES query if the user has selected
   * "top-level descriptions."
   */
  protected function handleTopLevelDescriptionsOnlyFilter()
  {
    $this->topLvlDescUrl = $this->context->routing->generate(null, array('topLod' => true) +
                           $this->request->getParameterHolder()->getAll());

    $this->allLvlDescUrl = $this->context->routing->generate(null, array('topLod' => false) +
                           $this->request->getParameterHolder()->getAll());

    if (isset($this->request->topLod) && $this->request->topLod)
    {
      $this->checkedTopDesc = 'checked';
      $this->checkedAllDesc = '';

      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    }
    else
    {
      $this->checkedTopDesc = '';
      $this->checkedAllDesc = 'checked';
    }
  }

  public function execute($request)
  {
    // Force subclassing
    if ('default' == $this->context->getModuleName() && 'browse' == $this->context->getActionName())
    {
      $this->forward404();
    }

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

    $indexType = property_exists($this, 'INDEX_TYPE') ? $this::INDEX_TYPE : null;
    $facets    = property_exists($this, 'FACETS') ? $this::$FACETS : null;

    $this->search = new arElasticSearchPluginQuery(
      $indexType,
      $facets,
      $request->limit,
      $request->page);

    $this->search->addFilters($this->request->getGetParameters());

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
