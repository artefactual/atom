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
 * @package    qtDominionPlugin
 * @subpackage repository
 * @author     MJ Suhonos <mj@artefactual.com>
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class RepositoryBrowseAction extends sfAction
{
  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'types',
      'contact.i18n.region');

  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    // Force limit temporary
    $request->limit = 250;

    $queryBool = new Elastica_Query_Bool();
    $queryBool->addShould(new Elastica_Query_MatchAll());

    $this->filters = array();
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

            $queryBool->addMust(new Elastica_Query_Term(
              array(strtr($param, '_', '.') => $facetValue)));
          }
        }
      }
    }

    $query = new Elastica_Query();
    $query->setSort(array('_score' => 'desc', 'slug' => 'asc'));
    $query->setLimit($request->limit);
    $query->setQuery($queryBool);

    if (!empty($request->page))
    {
      $query->setFrom(($request->page - 1) * $request->limit);
    }

    foreach (self::$FACETS as $item)
    {
      $facet = new Elastica_Facet_Terms($item);
      $facet->setField($item);
      $facet->setSize(50);
      $query->addFacet($facet);
    }

    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitRepository')->search($query);
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
      $facets = array();

      foreach ($this->pager->getFacets() as $name => $facet)
      {
        if (isset($facet['terms']))
        {
          $ids = array();
          foreach ($facet['terms'] as $item)
          {
            $ids[$item['term']] = $item['count'];
          }
        }

        switch ($name)
        {
          case 'types':
            $criteria = new Criteria;
            $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);
            $types = QubitTerm::get($criteria);

            foreach ($types as $item)
            {
              $typeNames[$item->id] = $item->name;
            }

            foreach ($facet['terms'] as $item)
            {
              $facets[strtr($name, '.', '_')]['terms'][$item['term']] = array(
                'count' => $item['count'],
                'term' => $typeNames[$item['term']]);
            }

            break;

          case 'contact.i18n.region':
            foreach ($facet['terms'] as $item)
            {
              $facets[strtr($name, '.', '_')]['terms'][$item['term']] = array(
                'count' => $item['count'],
                'term' => $item['term']);
            }

            break;
        }
      }

      $this->pager->facets = $facets;
    }
  }
}
