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

class RepositoryHoldingsAction extends sfAction
{
  public function execute($request)
  {
    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');

    $this->repositoryId = $request->repositoryId;
    $this->page = $request->page;

    $holdings = array();
    $this->getHoldings();

    foreach ($this->pager->getResults() as $res)
    {
      $res = $res->getData();
      $url = url_for(array('module' => 'informationobject', 'slug' => $res['slug']));
      $title = $res['i18n'][$this->context->user->getCulture()]['title'];

      $holdings[] = array('url' => $url, 'title' => $title);
    }

    $results = array(
      'holdings' => $holdings,
      'start'    => $this->pager->getFirstIndice(),
      'end'      => $this->pager->getLastIndice()
    );

    return $this->renderText(json_encode($results));
  }

  /**
   * Query this repository's holdings and initialize pager.
   */
  private function getHoldings()
  {
    $limit = sfConfig::get('app_hits_per_page', 10);

    $queryBool = new \Elastica\Query\Bool();
    $queryBool->addShould(new \Elastica\Query\MatchAll());
    $queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    $queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $this->repositoryId)));

    $query = new \Elastica\Query($queryBool);

    $query->setLimit($limit);
    $query->setFrom($limit * ($this->page - 1));

    $title = sprintf('i18n.%s.title.untouched', $this->context->user->getCulture());
    $query->setSort(array($title => array('order' => 'asc', 'ignore_unmapped' => true)));

    $filter = new \Elastica\Filter\Bool;
    QubitAclSearch::filterDrafts($filter);

    if (0 < count($filter->toArray()))
    {
      $query->setFilter($filter);
    }

    $this->resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
    $this->pager = new QubitSearchPager($this->resultSet);

    $this->pager->setMaxPerPage($limit);
    $this->pager->setPage($this->page);

    $this->pager->init();
  }
}
