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

class EventEditComponent extends sfComponent
{
    protected $finalEventIds = [];

    /**
     * Create forms to add and edit events.
     *
     * @param sfWebRequest $request
     */
    public function execute($request)
    {
        $this->addEventForms();
    }

    /**
     * Check if $form has data in the required fields.
     *
     * @param sfFormFieldSchema $form event form data
     */
    public function hasRequiredData(sfFormFieldSchema $form): bool
    {
        foreach ($form as $field) {
            if (!empty($field->getValue())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process event form data.
     *
     * @param sfFormFieldSchema $form event form data
     */
    public function processEventForm(sfFormFieldSchema $form)
    {
        // Continue only if the form has the required field data
        if (!$this->hasRequiredData($form)) {
            return;
        }

        if (isset($this->request->sourceId) || empty($form['id']->getValue())) {
            // When duplicating an information object (i.e. sourceId is set)
            // always create a brand new event to link to the new object.
            //
            // Create a new event if this row doesn't have an id (i.e. it's a
            // new event)
            $event = new QubitEvent();

            // Add this event to the list of events related to $this->resource.
            // See the actor and informationObject subclasses for the addEvent()
            // method (the event list is named differently)
            $this->addEvent($event);
        } else {
            // Get the existing QubitEvent object
            $params = $this->context->routing->parse(
                Qubit::pathInfo($form['id']->getValue())
            );
            $event = $params['_sf_route']->resource;

            // If the event doesn't exist in the database, skip this form
            if (!isset($event)) {
                return;
            }

            $this->finalEventIds[] = $event->id;
        }

        foreach ($form as $i => $field) {
            $this->processField($field, $event);
        }

        // Index on save for actors, but not informationObjects
        $event->indexOnSave = $this->indexOnSave;
    }

    /**
     * Process event forms.
     */
    public function processForm()
    {
        $this->events->bind($this->request->getPostParameter('events'));

        foreach ($this->events as $i => $form) {
            $this->processEventForm($form);
        }

        // Stop here if duplicating a QubitInformationObject
        if (isset($this->request->sourceId)) {
            return;
        }

        // Delete events marked for deletion
        $this->deleteDeletedEvents();
    }

    /**
     * Set form help text.
     *
     * @param mixed $form
     */
    public function setHelps($form)
    {
        sfApplicationConfiguration::getActive()->loadHelpers(['I18N']);
        $form->getWidgetSchema()->setHelps([
            'actor' => __(
<<<'EOL'
Use the actor name field to link an authority record to this description. Search
for an existing name in the authority records by typing the first few characters
of the name. Alternatively, type a new name to create and link to a new
authority record.
EOL
            ),
            'date' => __(
<<<'EOL'
Enter free-text information, including qualifiers or typographical symbols to
express uncertainty, to change the way the date displays. If this field is not
used, the default will be the start and end years only.
EOL
            ),
            'place' => __(
<<<'EOL'
Search for an existing term in the places taxonomy by typing the first few
characters of the term name. Alternatively, type a new term to create and link
to a new place term.
EOL
            ),
            'type' => __(
<<<'EOL'
Select the type of activity that established the relation between the authority
record and the archival description (e.g. creation, accumulation, collection,
publication, etc.)
EOL
            ),
            'startDate' => __(
<<<'EOL'
Enter the start year. Do not use any qualifiers or typographical symbols to
express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM,
YYYY.
EOL
            ),
            'endDate' => __(
<<<'EOL'
Enter the end year. Do not use any qualifiers or typographical symbols to
express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM,
YYYY.
EOL
            ),
            'description' => __(
<<<'EOL'
Make notes on dates and any details pertaining to the dates of creation,
publication, or distribution, of the unit being described that are not included
in the Date(s) of creation, including publication, distribution, etc., area and
that are considered to be important." (RAD 1.8B8) "Make notes on the date(s) of
accumulation or collection of the unit being described." (RAD 1.8B8a)
EOL
            ),
        ]);
    }

    protected function addPostEventForms()
    {
        $i = 0;
        $events = $this->request->getPostParameter('events');

        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            $form = new EventForm();
            $this->events->embedForm($i++, $form);
        }
    }

    protected function addNewEventForm()
    {
        // Add a blank event form for the event modal, but don't embed the form
        // because we can't validate it, and there is no data to bind.
        $this->event = new EventForm(['type' => $this->getEventTypeDefault()]);
        $this->setHelps($this->event);
    }

    /**
     * Add event sub-forms to $this->events form.
     *
     * Add one event sub-form for each event linked to $resource, plus one blank
     * event sub-form for adding a new linked event.
     */
    protected function addEventForms()
    {
        if ($this->request->isMethod('post')) {
            // Create empty "events" form to hold "event" sub-forms
            $this->events = new QubitForm();
            $this->events->getValidatorSchema()->setOption(
                'allow_extra_fields', true
            );

            // CSRF check is already done by main page form
            $this->events->disableCSRFProtection();

            // Add one EventForm per POST "event"
            $this->addPostEventForms();
        }

        // Always include the new event form
        $this->addNewEventForm();
    }

    /**
     * Delete events indicated by "deleteEvent" form fields.
     */
    protected function deleteDeletedEvents()
    {
        if (!isset($this->request->deleteEvents)) {
            return;
        }

        foreach ($this->request->deleteEvents as $eventUri) {
            $params = $this->context->routing->parse(
                Qubit::pathInfo($eventUri)
            );
            $event = $params['_sf_route']->resource;

            if (isset($event) && QubitEvent::class === get_class($event)) {
                $event->indexOnSave = $this->indexOnSave;
                $event->delete();
            }
        }
    }

    /**
     * Get pre-selected event type value.
     *
     * @param null|QubitEvent $event
     */
    protected function getEventTypeDefault($event = null)
    {
        if (isset($event, $event->type)) {
            $term = $event->type;
        } else {
            // Default event type is creation
            $term = QubitTerm::getById(QubitTerm::CREATION_ID);
        }

        if (!isset($term)) {
            return null;
        }

        return $this->context->routing->generate(
            null, [$term, 'module' => 'term']
        );
    }

    /**
     * Process form field data and map to ORM columns.
     *
     * @param sfFormField $field object
     * @param QubitEvent  $event ORM object
     */
    protected function processField($field, $event)
    {
        $value = $field->getValue();

        switch ($field->getName()) {
            case 'actor':
                if (isset($value)) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($value));
                    $event->actor = $params['_sf_route']->resource;
                } else {
                    unset($event->actor);
                }

                break;

            case 'id':
                // The event id is already set by this point
                break;

            case 'endDate':
            case 'startDate':
                if (empty($value)) {
                    $event[$field->getName()] = null;

                    return;
                }

                // Parse YYYYMMDD format
                if (preg_match('/^\d{8}\z/', trim($value), $matches)) {
                    $value = substr($matches[0], 0, 4).'-'.
                        substr($matches[0], 4, 2).'-'.
                        substr($matches[0], 6, 2);
                } elseif (preg_match('/^\d{6}\z/', trim($value), $matches)) {
                    // Parse YYYYMM format
                    $value = substr($matches[0], 0, 4).'-'.
                        substr($matches[0], 4, 2);
                }

                $event[$field->getName()] = $value;

                break;

            case 'place':
                // Get related term id
                if (!empty($value)) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($value));
                    $termId = $params['_sf_route']->resource->id;
                }

                // Get term relation
                if (isset($event->id)) {
                    $relation = QubitObjectTermRelation::getOneByObjectId($event->id);
                }

                // Nothing to do
                if (!isset($termId) && !isset($relation)) {
                    break;
                }

                // The relation needs to be deleted/updated independently
                // if the event exits, otherwise when deleting, it will try to
                // save it again from the objectTermRelationsRelatedByobjectId array.
                // If the event is new, the relation needs to be created and attached
                // to the event in the objectTermRelationsRelatedByobjectId array.
                if (!isset($termId) && isset($relation)) {
                    $relation->delete();

                    break;
                }

                if (isset($termId, $relation)) {
                    $relation->termId = $termId;
                    $relation->save();

                    break;
                }

                $relation = new QubitObjectTermRelation();
                $relation->termId = $termId;

                $event->objectTermRelationsRelatedByobjectId[] = $relation;

                break;

            case 'resourceType':
            case 'type':
                if (!empty($value)) {
                    $route = $this->context->routing->parse(
                        Qubit::pathInfo($value)
                    );
                    $term = $route['_sf_route']->resource;
                    $event->type = $term;
                } else {
                    unset($event->type);
                }

                break;

            default:
                $event[$field->getName()] = $value;
        }
    }
}
