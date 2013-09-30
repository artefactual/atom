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

/**
 * @package    AccesstoMemory
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class RepositoryBrowseAction extends DefaultBrowseAction
{
  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'types' => array('type' => 'term',
                       'field' => 'types',
                       'size' => 10),
      'regions' => array('type' => 'term',
                         'field' => 'contactInformations.i18n.en.region',
                         'size' => 10));

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'types':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->name;
        }

        break;

      case 'regions':
        foreach ($ids as $key => $count)
        {
          $this->types[$key] = $key;
        }

        break;

      default:
        parent::populateFacet($name, $ids);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('' == preg_replace('/[\s\t\r\n]*/', '', $request->query))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryText = new \Elastica\Query\QueryString($request->query);
      $queryText->setDefaultOperator('AND');
      $queryText->setDefaultField(sprintf('i18n.%s.authorizedFormOfName', $this->context->user->getCulture()));

      $this->queryBool->addMust($queryText);
    }

    // TODO, ACL filter
    // $this->query = QubitAclSearch::filterBy...

    // Sort
    if (!isset($request->sort))
    {
      if ($this->getUser()->isAuthenticated())
      {
        $request->sort = sfConfig::get('app_sort_browser_user');
      }
      else
      {
        $request->sort = sfConfig::get('app_sort_browser_anonymous');
      }
    }

    switch ($request->sort)
    {
      // Most of the times the institutions set is small so we can afford
      // alphabetic sorting without much memory consumption in ElasticSearch
      case 'alphabetic':
        $field = sprintf('i18n.%s.authorizedFormOfName.untouched', $this->context->user->getCulture());
        $this->query->setSort(array($field => 'asc'));

        break;

      case 'relevancy':
        $this->query->setSort(array('_score' => 'asc'));

        break;

      case 'lastUpdated':
      case 'mostRecent':
      default:
        $this->query->setSort(array('updatedAt' => 'desc'));
    }

    $this->query->setQuery($this->queryBool);
    // $this->query->setFields(array('slug', 'sourceCulture', 'i18n', 'entityTypeId', 'updatedAt'));

    // Set filter
    if (0 < count($this->filterBool->toArray()))
    {
      $this->query->setFilter($this->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitRepository')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
