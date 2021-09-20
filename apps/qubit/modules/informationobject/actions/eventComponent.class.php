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
 * Form for adding and editing related events.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectEventComponent extends EventEditComponent
{
    // Don't update the search index when saving an event object
    public $indexOnSave = false;

    // Arrays not allowed in class constants
    public static $NAMES = [
        'id',
        'actor',
        'date',
        'endDate',
        'startDate',
        'description',
        'place',
        'type',
    ];

    /**
     * Get related events.
     */
    public function getRelatedEvents()
    {
        return $this->resource->eventsRelatedByobjectId;
    }

    /**
     * Add event to QubitInformationObject::eventsRelatedByobjectId[] list
     * to ensure the event object is create after the QubitInformatinObject.
     */
    public function addEvent(QubitEvent $event): QubitEvent
    {
        $this->resource->eventsRelatedByobjectId[] = $event;

        return $event;
    }
}
