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

class InformationObjectCalculateDatesAction extends sfAction
{
    // Arrays not allowed in class constants
    public static $NAMES = ['eventIdOrTypeId'];

    public function execute($request)
    {
        $this->form = new sfForm();
        $this->resource = $this->getRoute()->resource;
        $this->i18n = $this->context->i18n;

        // Redirect if unauthorized
        if (!QubitAcl::check($this->resource, 'update')) {
            QubitAcl::forwardUnauthorized();
        }

        // Return error page if attempting to calculate dates for a description that
        // has no descendants
        if (1 == $this->resource->rgt - $this->resource->lft) {
            return sfView::ERROR;
        }

        // The descendantDateTypes query can be slow for large hierarchies, so delay
        // calling it until just before we need it
        $this->descendantEventTypes = self::getDescendantDateTypes($this->resource);
        $this->events = $this->getResourceEventsWithDateRangeSet(
            $this->resource,
            $this->descendantEventTypes
        );

        if (0 == count($this->descendantEventTypes)) {
            return sfView::ERROR;
        }

        // Add form fields
        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();
                $this->beginDateCalculation();
                $this->redirect([$this->resource, 'module' => 'informationobject']);
            } else {
                $message = $this->i18n->__('Please make a selection.');
                $this->context->user->setFlash('error', $message);
            }
        }
    }

    public static function getDescendantDateTypes($resource)
    {
        $eventTypes = [];

        $sql = 'SELECT
            DISTINCT e.type_id
            FROM
                information_object i
                INNER JOIN event e ON i.id=e.object_id
            WHERE
                i.lft > :lft
                AND i.lft < :rgt';

        $params = [
            ':lft' => $resource->lft,
            ':rgt' => $resource->rgt,
        ];

        $eventData = QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_ASSOC]);

        foreach ($eventData as $event) {
            $eventTypeTerm = QubitTerm::getById($event['type_id']);
            $eventTypes[$event['type_id']] = $eventTypeTerm->getName(['cultureFallback' => true]);
        }

        return $eventTypes;
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'eventIdOrTypeId':
                if (count($this->events) || count($this->descendantEventTypes)) {
                    $eventIdChoices = $this->events + $this->descendantEventTypes;
                    $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $eventIdChoices]));
                    $this->form->setValidator($name, new sfValidatorInteger(['required' => true]));
                }

                break;
        }
    }

    protected function processField($field)
    {
        switch ($name = $field->getName()) {
            case 'eventIdOrTypeId':
                $this->eventIdOrTypeId = $field->getValue();

                // Determine whether ID belongs to an event or a type (term)
                $criteria = new Criteria();
                $criteria->add(QubitObject::ID, $this->eventIdOrTypeId);

                if (null !== $object = QubitObject::getOne($criteria)) {
                    if ('QubitEvent' == $object->className) {
                        $this->eventId = $object->id;
                    } else {
                        $this->eventTypeId = $object->id;
                    }
                }

                break;
        }
    }

    protected function processForm()
    {
        foreach ($this->form as $field) {
            $this->processField($field);
        }
    }

    protected function beginDateCalculation()
    {
        // Specify parameters for job
        $params = [
            'objectId' => $this->resource->id,
            'eventId' => $this->eventId,
            'eventTypeId' => $this->eventTypeId,
        ];

        // Catch no Gearman worker available exception
        // and others to show alert with exception message
        try {
            QubitJob::runJob('arCalculateDescendantDatesJob', $params);

            $message = $this->i18n->__('Date calculation started.');
            $this->context->user->setFlash('info', $message);
        } catch (Exception $e) {
            $message = $this->i18n->__('Calculation failed').': '.$this->i18n->__($e->getMessage());
            $this->context->user->setFlash('error', $message);
        }
    }

    protected function getResourceEventsWithDateRangeSet($resource, $validEventTypes = null)
    {
        $validEventTypes = (is_null($validEventTypes)) ? self::getDescendentDateTypes($resource) : $validEventTypes;

        $events = [];

        $criteria = new Criteria();
        $criteria->add(QubitEvent::OBJECT_ID, $resource->id);

        // Assemble array of descriptions for any events containing date information
        foreach (QubitEvent::get($criteria) as $event) {
            if ($this->eventHasDateAndDateRangeSet($event) && null !== $event->typeId && isset($validEventTypes[$event->typeId])) {
                $eventTypeName = $event->type->getName(['cultureFallback' => true]);
                $eventRange = Qubit::renderDateStartEnd($event->getDate(['cultureFallback' => true]), $event->startDate, $event->endDate);
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
