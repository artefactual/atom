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

class InformationObjectCalculateDatesAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'eventIdOrTypeId');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'eventIdOrTypeId':
        if (count($this->events) || count($this->descendantEventTypes))
        {
          $eventIdChoices = $this->events + $this->descendantEventTypes;
          $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $eventIdChoices)));
          $this->form->setValidator($name, new sfValidatorInteger(array('required' => true)));
        }

        break;
    }
  }

  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'eventIdOrTypeId':
        $this->eventIdOrTypeId = $field->getValue();

        // Determine whether ID belongs to an event or a type (term)
        $criteria = new Criteria;
        $criteria->add(QubitObject::ID, $this->eventIdOrTypeId);

        if (null !== $object = QubitObject::getOne($criteria))
        {
          if ($object->className == 'QubitEvent')
          {
            $this->eventId = $object->id;
          }
          else
          {
            $this->eventTypeId = $object->id;
          }
        }

        break;
    }
  }

  protected function earlyExecute()
  {
    $this->i18n = $this->context->i18n;
    $this->resource = $this->getRoute()->resource;
    $this->descendantEventTypes = self::getDescendantDateTypes($this->resource);
    $this->events = $this->getResourceEventsWithDateRangeSet($this->resource, $this->descendantEventTypes);
  }

  public function execute($request)
  {
    parent::execute($request);

    // Redirect if unauthorized
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Set response to 403 forbidden if attempting to calculate dates using
    // non-existant descendants
    if ($this->resource->rgt - $this->resource->lft == 1)
    {
      $this->getResponse()->setStatusCode(403);
      return sfView::NONE;
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();
        $this->beginDateCalculation();
        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
      else
      {
        $message = $this->i18n->__('Please make a selection.');
        $this->context->user->setFlash('error', $message);
      }
    }
  }

  protected function beginDateCalculation()
  {
    // Specify parameters for job
    $params = array(
      'objectId' => $this->resource->id,
      'eventId' => $this->eventId,
      'eventTypeId' => $this->eventTypeId
    );

    // Catch no Gearman worker available exception
    // and others to show alert with exception message
    try
    {
      QubitJob::runJob('arCalculateDescendantDatesJob', $params);

      $message = $this->i18n->__('Date calculation started.');
      $this->context->user->setFlash('info', $message);
    }
    catch (Exception $e)
    {
      $message = $this->i18n->__('Calculation failed') .': '. $this->i18n->__($e->getMessage());
      $this->context->user->setFlash('error', $message);
    }
  }

  static public function getDescendantDateTypes($resource)
  {
    $eventTypes = array();

    $sql = "SELECT
      DISTINCT e.type_id
      FROM
        information_object i
        INNER JOIN event e ON i.id=e.object_id
      WHERE
        i.lft > :lft
        AND i.rgt < :rgt";

    $params = array(
      ':lft' => $resource->lft,
      ':rgt' => $resource->rgt
    );

    $eventData = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));

    foreach($eventData as $event)
    {
      $eventTypeTerm = QubitTerm::getById($event['type_id']);
      $eventTypes[($event['type_id'])] = $eventTypeTerm->getName(array('cultureFallback' => true));
    }

    return $eventTypes;
  }

  protected function getResourceEventsWithDateRangeSet($resource, $validEventTypes = null)
  {
    $validEventTypes = (is_null($validEventTypes)) ? self::getDescendentDateTypes($resource) : $validEventTypes;

    $events = array();

    $criteria = new Criteria;
    $criteria->add(QubitEvent::OBJECT_ID, $resource->id);

    // Assemble array of descriptions for any events containing date information
    foreach(QubitEvent::get($criteria) as $event)
    {
      if ($this->eventHasDateAndDateRangeSet($event) && null !== $event->typeId && isset($validEventTypes[$event->typeId]))
      {
        $eventTypeName = $event->type->getName(array('cultureFallback' => true));
        $eventRange = Qubit::renderDateStartEnd($event->getDate(array('cultureFallback' => true)), $event->startDate, $event->endDate);
        $events[$event->id] = sprintf('%s [%s]', $eventRange, $eventTypeName);
      }
    }

    return $events;
  }

  protected function eventHasDateAndDateRangeSet($event)
  {
    return !empty($event->date) || !empty($event->startDate) || !empty($event->endDate);
  }
}
