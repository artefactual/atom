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

    if ((empty($request->id) || !ctype_digit($request->id))
      || (empty($request->page) || !ctype_digit($request->page)))
    {
      $this->forward404();
    }

    $limit = sfConfig::get('app_hits_per_page', 10);
    $culture = $this->context->user->getCulture();

    $resultSet = self::getHoldings($request->id, $request->page, $limit);

    $pager = new QubitSearchPager($resultSet);
    $pager->setMaxPerPage($limit);
    $pager->setPage($request->page);
    $pager->init();

    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Qubit', 'Url'));

    $results = array();
    foreach ($pager->getResults() as $item)
    {
      $doc = $item->getData();
      $results[] = array(
        'url' => url_for(array('module' => 'informationobject', 'slug' => $doc['slug'])),
        'title' => get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture, 'cultureFallback' => true))
      );
    }

    $data = array(
      'results'     => $results,
      'start'       => $pager->getFirstIndice(),
      'end'         => $pager->getLastIndice(),
      'currentPage' => $pager->getPage(),
      'lastPage'    => $pager->getLastPage()
    );

    return $this->renderText(json_encode($data));
  }

  /**
   * Query this repository's holdings
   */
  public static function getHoldings($id, $page, $limit)
  {
    $queryBool = new \Elastica\Query\BoolQuery;
    $queryBool->addShould(new \Elastica\Query\MatchAll);
    $queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    $queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $id)));

    QubitAclSearch::filterDrafts($queryBool);
    $query = new \Elastica\Query($queryBool);

    $query->setSize($limit);
    $query->setFrom($limit * ($page - 1));

    $title = sprintf('i18n.%s.title.untouched', sfContext::getInstance()->user->getCulture());
    $query->setSort(array($title => 'asc'));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    return $resultSet;
  }
}
