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
 * Show paginated list of actors.
 *
 * @package    qubit
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 * @version    svn:$Id: browseAction.class.php 11008 2012-03-01 19:07:08Z sevein $
 */
class ActorBrowseAction extends DefaultBrowseAction
{
  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'entityTypeId');

  public function execute($request)
  {
    // TODO
    // ??? $query = QubitAcl::searchFilterByResource($query, QubitActor::getById(QubitActor::ROOT_ID));

    if (!isset($request->limit))
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

    $queryBool = new Elastica_Query_Bool();

    if ('' == preg_replace('/[\s\t\r\n]*/', '', $request->subquery))
    {
      $queryBool->addMust(new Elastica_Query_MatchAll());
    }
    else
    {
      $queryText = new Elastica_Query_QueryString($request->subquery);
      $queryText->setDefaultOperator('AND');
      $queryText->setDefaultField('i18n.authorizedFormOfName');
      $queryBool->addMust($queryText);
    }

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

            $queryBool->addMust(new Elastica_Query_Term(array(
              strtr($param, '_', '.') => $facetValue)));
          }
        }
      }
    }

    $query = new Elastica_Query();
    $query->setLimit($request->limit);
    $query->setQuery($queryBool);

    switch ($request->sort)
    {
      case 'nameDown':
        $query->setSort(array('slug' => 'desc', '_score' => 'desc'));

        break;

      case 'nameUp':
        $query->setSort(array('slug' => 'asc', '_score' => 'desc'));

      break;

      case 'updatedDown':
        $query->setSort(array('updatedAt' => 'desc', '_score' => 'desc'));

        break;

      case 'updatedUp':
        $query->setSort(array('updatedAt' => 'asc', '_score' => 'desc'));

        break;

      default:
        if ('alphabetic' == $this->sortSetting)
        {
          $query->setSort(array('slug' => 'asc', '_score' => 'desc'));
        }
        else if ('lastUpdated' == $this->sortSetting)
        {
          // $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);
        }
    }

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
      $resultSet = QubitSearch::getInstance()->index->getType('QubitActor')->search($query);
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
      $this->types = array();

      $facets = array();
      foreach ($resultSet->getFacets() as $name => $facet)
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
          case 'entityTypeId':
            $criteria = new Criteria;
            $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

            foreach (QubitTerm::get($criteria) as $item)
            {
              $this->types[$item->id] = $item->name;
            }

            foreach ($facet['terms'] as $term)
            {
              $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
                'count' => $term['count'],
                'term' => $this->types[$term['term']]);
            }

            break;
        }
      }

      $this->pager->facets = $facets;
    }
  }
}
