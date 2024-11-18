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

        if (
            (empty($request->actorId) || !ctype_digit($request->actorId))
            || (empty($request->page) || !ctype_digit($request->page))
            || (isset($request->eventTypeId) && !ctype_digit($request->eventTypeId))
        ) {
            $this->forward404();
        }

        $limit = sfConfig::get('app_hits_per_page', 10);
        $culture = $this->context->user->getCulture();

        // Avoid pagination over ES' max result window config (default: 10000)
        $maxResultWindow = arElasticSearchPluginConfiguration::getMaxResultWindow();

        if ((int) $limit * (int) $request->page > $maxResultWindow) {
            // Return nothing to not break the list
            return;
        }

        $resultSet = self::getRelatedInformationObjects($request->actorId, $request->page, $limit, $request->eventTypeId);

        $pager = new QubitSearchPager($resultSet);
        $pager->setMaxPerPage($limit);
        $pager->setPage($request->page);
        $pager->init();

        sfContext::getInstance()->getConfiguration()->loadHelpers(['Qubit', 'Url']);

        $results = [];
        foreach ($pager->getResults() as $item) {
            $doc = $item->getData();
            $results[] = [
                'url' => url_for(['module' => 'informationobject', 'slug' => $doc['slug']]),
                'title' => render_value_inline(get_search_i18n($doc, 'title', ['allowEmpty' => false, 'culture' => $culture, 'cultureFallback' => true])),
            ];
        }

        $data = [
            'results' => $results,
            'start' => $pager->getFirstIndice(),
            'end' => $pager->getLastIndice(),
            'currentPage' => $pager->getPage(),
            'lastPage' => $pager->getLastPage(),
        ];

        return $this->renderText(json_encode($data));
    }

    /**
     * Get related IOs by event type. If no event type id
     * is provided, 'Subject of' IOs are returned.
     *
     * @param mixed      $actorId
     * @param mixed      $page
     * @param mixed      $limit
     * @param null|mixed $eventTypeId
     */
    public static function getRelatedInformationObjects($actorId, $page, $limit, $eventTypeId = null)
    {
        $query = new \Elastica\Query();
        $queryBool = new \Elastica\Query\BoolQuery();

        if (!isset($eventTypeId)) {
            // Get subject of IOs (name access points)
            $queryTerm = new \Elastica\Query\Term(['names.id' => $actorId]);

            $queryBool->addMust($queryTerm);

            foreach (QubitTerm::getEventTypes() as $eventType) {
                $queryNested = self::nestedActorAndEventTypeQuery($actorId, $eventType->id);
                $queryBool->addMustNot($queryNested);
            }
        } else {
            $queryNested = self::nestedActorAndEventTypeQuery($actorId, $eventTypeId);
            $queryBool->addMust($queryNested);
        }

        QubitAclSearch::filterDrafts($queryBool);
        $title = sprintf('i18n.%s.title.alphasort', sfContext::getInstance()->user->getCulture());

        $query->setQuery($queryBool);
        $query->setSort([$title => 'asc']);
        $query->setSize($limit);
        $query->setFrom($limit * ($page - 1));

        return QubitSearch::getInstance()->index->getIndex('QubitInformationObject')->search($query);
    }

    public static function nestedActorAndEventTypeQuery($actorId, $eventTypeId)
    {
        // Get related by event IOs
        $queryBoolDates = new \Elastica\Query\BoolQuery();
        $queryBoolDates->addMust(new \Elastica\Query\Term(['dates.actorId' => $actorId]));
        $queryBoolDates->addMust(new \Elastica\Query\Term(['dates.typeId' => $eventTypeId]));

        // Use nested query and mapping object to allow querying
        // over the actor and event ids from the same event
        $queryNested = new \Elastica\Query\Nested();
        $queryNested->setPath('dates');
        $queryNested->setQuery($queryBoolDates);

        return $queryNested;
    }
}
