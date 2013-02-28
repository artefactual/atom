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
      'types',
      'contact.i18n.region');

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

      case 'contact.i18n.region':
        /*
        foreach ($facet['terms'] as $item)
        {
          $facets[strtr($name, '.', '_')]['terms'][$item['term']] = array(
            'count' => $item['count'],
            'term' => $item['term']);
        }
        */

        break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    $this->queryBool->addMust(new Elastica_Query_MatchAll());

    // TODO, ACL filter
    // $this->query = QubitAclSearch::filterBy...

    switch ($request->sort)
    {
      case 'alphabetic':
        $this->query->setSort(array('_score' => 'desc', 'slug' => 'asc'));

        break;

      case 'lastUpdated':
        $this->query->setSort(array('_score' => 'desc', 'updatedAt' => 'asc'));

        break;

      default:
        if ('alphabetic' == $this->sortSetting)
        {
          $this->query->setSort(array('_score' => 'desc', 'slug' => 'asc'));
        }
        else if ('lastUpdated' == $this->sortSetting)
        {
          $this->query->setSort(array('_score' => 'desc', 'updatedAt' => 'asc'));
        }
    }

    $this->query->setQuery($this->queryBool);
    // $this->query->setFields(array('slug', 'sourceCulture', 'i18n', 'entityTypeId', 'updatedAt'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitRepository')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
