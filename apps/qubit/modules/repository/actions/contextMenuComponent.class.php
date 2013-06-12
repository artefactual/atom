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
  const LIMIT = 10;

  public function execute($request)
  {
    if (!isset($request->getAttribute('sf_route')->resource))
    {
      return sfView::NONE;
    }

    $this->limit = self::LIMIT;

    $this->resource = $request->getAttribute('sf_route')->resource;

    $queryBool = new \Elastica\Query\Bool();
    $queryBool->addShould(new \Elastica\Query\MatchAll());
    $queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    $queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $this->resource->id)));

    $query = new \Elastica\Query($queryBool);

    $query->setLimit($this->limit);
    $query->setSort(array(sprintf('i18n.%s.title.untouched', $this->context->user->getCulture()) => 'asc'));

    // Filter
    $filter = new \Elastica\Filter\Bool;

    // Filter out descriptions without title
    $filterExists = new \Elastica\Filter\Exists(sprintf('i18n.%s.title', $this->context->user->getCulture()));
    $filter->addMust($filterExists);

    // Filter drafts
    QubitAclSearch::filterDrafts($filter);

    // Set filter
    $query->setFilter($filter);

    $this->resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
  }
}
