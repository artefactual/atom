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
 * Actor - show event data as JSON
 *
 * @package    AccesstoMemory
 * @subpackage Actor - initialize an editISAAR template for updating an actor
 */
class sfIsaarPluginActorEventsAction extends sfAction
{
  public function execute($request)
  {
    $actor = QubitActor::getBySlug($request->slug);

    $criteria = new Criteria;
    $criteria->add(QubitEvent::ACTOR_ID, $actor->id);

    $data = array();
    $data['total'] = count(QubitEvent::get($criteria));

    $criteria->setOffset($request->skip);
    $criteria->setLimit($request->limit);

    $data['data'] = $this->assembleEventData($criteria);

    $this->getResponse()->setHttpHeader('Content-type','application/json');
    return $this->renderText(json_encode($data));
  }

  private function assembleEventData($criteria)
  {
    $events = array();

    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
    sfContext::getInstance()->getConfiguration()->loadHelpers('Qubit');

    foreach(QubitEvent::get($criteria) as $event)
    {
      $eventData = array(
        'url' => url_for(array($event, 'module' => 'event')),
        'title' => render_title($event->object),
        'type' => render_value_inline($event->type),
        'date' => render_value_inline(Qubit::renderDateStartEnd($event->date, $event->startDate, $event->endDate))
      );

      array_push($events, $eventData);
    }

    return $events;
  }
}
