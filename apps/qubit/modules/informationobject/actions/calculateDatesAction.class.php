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
      'eventId');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'eventId':
        if (count($this->events) == 1)
        {
          // If there's only one event with data range data, display its description
          // via the form label and relay its ID via a hidden field
          $this->form->setDefault($name, key($this->events));
          $this->form->setWidget($name, new sfWidgetFormInputHidden);
          $label = current($this->events);
        }
        else
        {
          // Otherwise, display the events using a SELECT element
          $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $this->events)));
          $label = $this->i18n->__('Event');
        }

        $this->form->setValidator($name, new sfValidatorInteger);
        $this->form->getWidgetSchema()->$name->setLabel($label);

        break;
    }
  }

  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'eventId':
        $this->eventId = $field->getValue();

        break;
    }
  }

  protected function earlyExecute()
  {
    $this->i18n = $this->context->i18n;
    $this->resource = $this->getRoute()->resource;
    $this->events = self::getResourceEventsWithDateRangeSet($this->resource);
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
    if (!count($this->resource->descendants))
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
    }

    $message = $this->i18n->__('Warning: Selected date range for the specified event will be overwritten.');
    $this->getUser()->setFlash('notice', $message);
  }

  protected function beginDateCalculation()
  {
    // Specify parameters for job
    $params = array(
      'objectId' => $this->resource->id,
      'eventId' => $this->eventId
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

  static public function getResourceEventsWithDateRangeSet($resource)
  {
    $events = array();

    $criteria = new Criteria;
    $criteria->add(QubitEvent::OBJECT_ID, $resource->id);

    // Assemble array of descriptions for any events containing date information
    foreach(QubitEvent::get($criteria) as $event)
    {
      if ((!empty($event->date) || !empty($event->startDate) || !empty($event->endDate)) && null !== $event->typeId)
      {
        $eventTypeName = $event->type->getName(array('cultureFallback' => true));
        $eventRange = Qubit::renderDateStartEnd($event->getDate(array('cultureFallback' => true)), $event->startDate, $event->endDate);
        $events[$event->id] = sprintf('%s [%s]', $eventRange, $eventTypeName);
      }
    }

    return $events;
  }
}
