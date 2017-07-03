<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *names.id
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class ActorRelatedInformationObjectsAction extends sfAction
{
  public function execute($request)
  {
    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    if ((empty($request->actorId) || !ctype_digit($request->actorId))
      || (empty($request->page) || !ctype_digit($request->page))
      || (isset($request->eventTypeId) && !ctype_digit($request->eventTypeId)))
    {
      $this->forward404();
    }

    $limit = sfConfig::get('app_hits_per_page', 10);
    $culture = $this->context->user->getCulture();

    $resultSet = self::getRelatedInformationObjects($request->actorId, $request->page, $limit, $request->eventTypeId);

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
   * Get related IOs by event type. If no event type id
   * is provided, 'Subject of' IOs are returned
   */
  public static function getRelatedInformationObjects($actorId, $page, $limit, $eventTypeId = null)
  {
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\BoolQuery;

    if (!isset($eventTypeId))
    {
      // Get subject of IOs (name access points)
      $queryTerm = new \Elastica\Query\Term(array('names.id' => $actorId));

      $queryBool->addMust($queryTerm);
    }
    else
    {
      // Get related by event IOs 
      $queryBoolDates = new \Elastica\Query\BoolQuery;
      $queryBoolDates->addMust(new \Elastica\Query\Term(array('dates.actorId' => $actorId)));
      $queryBoolDates->addMust(new \Elastica\Query\Term(array('dates.typeId' => $eventTypeId)));

      // Use nested query and mapping object to allow querying
      // over the actor and event ids from the same event
      $queryNested = new \Elastica\Query\Nested();
      $queryNested->setPath('dates');
      $queryNested->setQuery($queryBoolDates);

      $queryBool->addMust($queryNested);
    }

    QubitAclSearch::filterDrafts($queryBool);
    $title = sprintf('i18n.%s.title.untouched', sfContext::getInstance()->user->getCulture());

    $query->setQuery($queryBool);
    $query->setSort(array($title => 'asc'));
    $query->setSize($limit);
    $query->setFrom($limit * ($page - 1));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    return $resultSet;
  }
}
