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
     * Get related events with dates.
     *
     * The ISAD(G) template separates "date" events from "actor" events
     */
    public function getRelatedEvents()
    {
        return $this->resource->getDates();
    }

    /**
     * Check if $form has data in the required fields.
     *
     * @param sfFormFieldSchema $form event form data
     */
    public function hasRequiredData(sfFormFieldSchema $form): bool
    {
        if (
            empty($form['date']->getValue())
            && empty($form['endDate']->getValue())
            && empty($form['startDate']->getValue())
        ) {
            // Skip this row if there is no date data
            return false;
        }

        return true;
    }

    /**
     * Get default form field values.
     */
    protected function getFormDefaults(QubitEvent $event)
    {
        return [
            'id' => $event->id,
            'date' => $event->date,
            'startDate' => Qubit::renderDate($event->startDate),
            'endDate' => Qubit::renderDate($event->endDate),
            'type' => $this->getEventTypeDefault($event),
        ];
    }

    /**
     * Add one event form for each event in POST.
     */
    protected function addPostEventForms()
    {
        $i = 0;
        $events = $this->request->getPostParameter('events');

        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            $form = new sfIsadEventForm();
            $this->events->embedForm($i++, $form);
        }
    }

    /**
     * Add forms for existing events related to this resource.
     */
    protected function addRelatedEventForms()
    {
        $i = 0;

        // Add one event sub-form for each event related to this resource, to
        // allow editing the existing events
        foreach ($this->getRelatedEvents() as $event) {
            // Embed the event sub-form into the $this->events form
            $form = new sfIsadEventForm($this->getFormDefaults($event));
            $this->events->embedForm($i++, $form);
        }
    }

    /**
     * Add an blank event form to allow creating a new event.
     */
    protected function addNewEventForm()
    {
        $form = new sfIsadEventForm(['type' => $this->getEventTypeDefault()]);
        $this->events->embedForm(count($this->events), $form);
    }

    /**
     * Add event sub-forms to $this->events form.
     */
    protected function addEventForms()
    {
        if ($this->request->isMethod('post')) {
            $this->addPostEventForms();
        } else {
            $this->addRelatedEventForms();
            $this->addNewEventForm();
        }
    }

    protected function deleteDeletedEvents()
    {
        // Delete the old events that were removed from the form by multiRow.js.
        foreach ($this->getRelatedEvents() as $event) {
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
