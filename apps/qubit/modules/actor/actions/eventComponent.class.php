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
 * Add, edit and delete actor events.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class ActorEventComponent extends EventEditComponent
{
    // Update the search index when saving an event object
    public $indexOnSave = true;

    // Arrays not allowed in class constants
    public static $NAMES = [
        'id',
        'object',
        'date',
        'endDate',
        'startDate',
        'description',
        'place',
        'type',
    ];

    /**
     * Add event to QubitActor::events[] list to ensure the event object is
     * create after the QubitActor object.
     */
    public function addEvent(QubitEvent $event): QubitEvent
    {
        $this->resource->events[] = $event;

        return $event;
    }

    public function processEventForm($form, $data)
    {
        parent::processEventForm($form, $data);
    }

    public function processField($field)
    {
        switch ($field->getName()) {
            case 'object':
                unset($this->event->object);

                $value = $this->form->getValue('object');
                if (isset($value)) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($value));
                    $this->event->object = $params['_sf_route']->resource;
                }

                break;

            default:
                return parent::processField($field);
        }
    }
}
