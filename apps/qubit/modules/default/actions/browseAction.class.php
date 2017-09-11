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
  protected function populateAggs($resultSet)
  {
    $this->aggs = array();

    // Stop if no aggregations available
    if (!$resultSet->hasAggregations())
    {
      return;
    }

    foreach ($resultSet->getAggregations() as $name => $agg)
    {
      if (isset($this::$AGGS[$name]['populate']) && !$this::$AGGS[$name]['populate'])
      {
        $this->aggs[$name] = $agg;

        continue;
      }

      // Pass if the aggregation is empty
      if (!isset($agg['buckets']) || count($agg['buckets']) == 0)
      {
        continue;
      }

      $this->aggs[$name] = $this->populateAgg($name, $agg['buckets']);

      // Get unique descriptions count for languages aggregation
      if ($name == 'languages')
      {
        // If the query is being filtered by language we need to execute
        // the same query again without language clause to get the count
        if (isset($this->search->filters['languages']))
        {
          // Find an remove language clause from the query
          $queryParams = $this->search->query->toArray();
          $mustClauses = array();

          foreach ($queryParams['query']['bool']['must'] as $mustClause)
          {
            if (isset($mustClause['term']['i18n.languages']))
            {
              continue;
            }

            $mustClauses[] = $mustClause;
          }

          $queryParams['query']['bool']['must'] = $mustClauses;

          $this->search->query->setRawQuery($queryParams);

          $resultSetWithoutLanguageFilter = QubitSearch::getInstance()->index->getType($this::INDEX_TYPE)->search($this->search->query);

          $count = $resultSetWithoutLanguageFilter->getTotalHits();
        }
        // Without language filter the count equals the number of hits
        else
        {
          $count = $resultSet->getTotalHits();
        }

        $i18n = sfContext::getInstance()->i18n;

        $uniqueTerm = array(
          'key' => 'unique_language',
          'display' => $i18n->__('Unique records'),
          'doc_count' => $count);

        // Add unique term at the biginning of the array
        // only when there are other terms
        if (!empty($this->aggs[$name]))
        {
          $this->aggs[$name] = array_merge(array($uniqueTerm), $this->aggs[$name]);
        }
      }
    }
  }

  protected function populateAgg($name, $buckets)
  {
    switch ($name)
    {
      case 'languages':
        foreach ($buckets as $key => $bucket)
        {
          $buckets[$key]['display'] = ucfirst(sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($bucket['key']));
        }

        break;
    }

    return $buckets;
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

    if (property_exists($this, 'AGGS'))
    {
      if (!isset($this->getParameters))
      {
        $this->getParameters = $request->getGetParameters();
      }

      $this->search->addAggs($this::$AGGS);
      $this->search->addAggFilters($this::$AGGS, $this->getParameters);
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
