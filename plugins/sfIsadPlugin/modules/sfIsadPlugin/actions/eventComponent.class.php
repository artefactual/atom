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

class sfIsadPluginEventComponent extends InformationObjectEventComponent
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'id',
        'date',
        'endDate',
        'startDate',
        'type',
    ];

    /**
     * Get only events that have a date for the ISAD template.
     */
    public function getEvents()
    {
        return $this->resource->getDates();
    }

    public function hasRequiredData($event)
    {
        if (
            empty($event['date']->getValue())
            && empty($event['endDate']->getValue())
            && empty($event['startDate']->getValue())
        ) {
            // Skip this row if there is no date data
            return false;
        }

        return true;
    }

    /**
     * Add event sub-forms to $this->events form.
     *
     * Add one event sub-form for each event linked to $resource, plus one blank
     * event sub-form for adding a new linked event.
     */
    protected function addEventForms()
    {
        $i = 0;

        // Add one event sub-form for each event related to this resource, to
        // allow editing the existing events
        foreach ($this->getEvents() as $event) {
            // Embed the event sub-form into the $this->events form
            $form = new EventForm($this->getFormDefaults($event));
            $this->events->embedForm($i++, $form);
        }

        // Add a blank event sub-form to allow adding a new event
        $form = new EventForm(['type' => $this->getEventTypeDefault()]);
        $this->setHelps($form);
        $this->events->embedForm('', $form);
    }

    protected function deleteDeletedEvents()
    {
        // Delete the old events that were removed from the form by multiRow.js.
        foreach ($this->getEvents() as $event) {
            if (
                isset($event->id)
                && false === array_search($event->id, $this->finalEventIds)
            ) {
                // Will be indexed when description is saved
                $event->indexOnSave = false;

                if (!isset($item->actor)) {
                    // Only delete event if it has no associated actor
                    $event->delete();
                } else {
                    // ISAD events never have an actor, so keep this event but
                    // clear the date fields.
                    $event->startDate = null;
                    $event->endDate = null;
                    $event->date = null;
                    $event->save();
                }
            }
        }
    }
}
