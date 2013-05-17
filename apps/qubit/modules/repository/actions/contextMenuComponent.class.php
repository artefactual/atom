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

class RepositoryContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (!isset($request->getAttribute('sf_route')->resource))
    {
      return sfView::NONE;
    }

    $this->resource = $request->getAttribute('sf_route')->resource;

    $queryBool = new \Elastica\Query\Bool();
    $queryBool->addShould(new \Elastica\Query\MatchAll());
    $queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    $queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $this->resource->id)));

    $query = new \Elastica\Query($queryBool);

    QubitAclSearch::filterDrafts($query);

    $query->setLimit($request->limit);
    $query->setSort(array('slug' => 'asc', '_score' => 'desc'));

    if (!empty($request->page))
    {
      $query->setFrom(($request->page - 1) * $request->limit);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
  }
}
