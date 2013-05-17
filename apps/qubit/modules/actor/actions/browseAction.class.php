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
 * Show paginated list of actors.
 *
 * @package    AccesstoMemory
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class ActorBrowseAction extends DefaultBrowseAction
{
  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'types' => array('type' => 'term',
                       'field' => 'entityTypeId',
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
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('' == preg_replace('/[\s\t\r\n]*/', '', $request->subquery))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll());
    }
    else
    {
      $queryText = new \Elastica\Query\QueryString($request->subquery);
      $queryText->setDefaultOperator('AND');
      $queryText->setDefaultField(sprintf('i18n.%s.authorizedFormOfName', $this->context->user->getCulture()));

      $this->queryBool->addMust($queryText);
    }

    $this->query = QubitAclSearch::filterByResource($this->query, QubitActor::getById(QubitActor::ROOT_ID));

    $field = sprintf('i18n.%s.authorizedFormOfName.untouched', $this->context->user->getCulture());

    switch ($request->sort)
    {
      case 'nameDown':
        $this->query->setSort(array($field => 'desc', '_score' => 'desc'));

        break;

      case 'nameUp':
        $this->query->setSort(array($field => 'asc', '_score' => 'desc'));

      break;

      case 'updatedDown':
        $this->query->setSort(array('updatedAt' => 'desc', '_score' => 'desc'));

        break;

      case 'updatedUp':
        $this->query->setSort(array('updatedAt' => 'asc', '_score' => 'desc'));

        break;

      default:
        if ('alphabetic' == $this->sortSetting)
        {
          $this->query->setSort(array($field => 'asc', '_score' => 'desc'));
        }
        else if ('lastUpdated' == $this->sortSetting)
        {
          $this->query->setSort(array('updatedAt' => 'desc', '_score' => 'desc'));
        }
    }

    $this->query->setQuery($this->queryBool);
    $this->query->setFields(array('slug', 'sourceCulture', 'i18n', 'entityTypeId', 'updatedAt'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitActor')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
