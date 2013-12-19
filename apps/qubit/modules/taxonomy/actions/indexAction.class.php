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

class TaxonomyIndexAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->resource = $this->getRoute()->resource;

    $culture = $this->context->user->getCulture();

    $this->query = new \Elastica\Query();
    $this->query->setLimit($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new \Elastica\Query\Bool();
    $this->filterBool = new \Elastica\Filter\Bool;

    $query = new \Elastica\Query\Term;
    $query->setTerm('taxonomyId', $this->resource->id);
    $this->queryBool->addMust($query);

    if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $queryString = new \Elastica\Query\QueryString($request->subquery);

      switch ($request->subqueryfield)
      {
        case 'Preferred label':
          $queryString->setDefaultField('i18n.'.$culture.'.name');

          break;

        case '\'Use for\' labels':
          $queryString->setDefaultField('useFor.i18n.'.$culture.'.name');

          break;

        case 'All labels':
        default:
          // Search over preferred label (boosted by five) and "Use for" labels
          $queryString->setFields(array('i18n.'.$culture.'.name^5', 'useFor.i18n.'.$culture.'.name'));
          $queryString->setDefaultOperator('OR');

          break;
      }

      // Filter results by subquery
      $filter = new \Elastica\Filter\Query($queryString);
      $this->filterBool->addMust($filter);
    }

    $this->query->setQuery($this->queryBool);

    // Set filter
    if (0 < count($this->filterBool->toArray()))
    {
      $this->query->setFilter($this->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitTerm')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
