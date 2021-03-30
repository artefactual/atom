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

class AccessionEventsComponent extends sfComponent
{
    public function execute($request)
    {
        // Cache accession event types (used in each event's type select form field)
        $criteria = new Criteria();
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::ACCESSION_EVENT_TYPE_ID);

        $this->eventTypes = [];
        foreach (QubitTerm::get($criteria) as $term) {
            $this->eventTypes[$term->id] = $term->getName(['cultureFallback' => true]);
        }

        // Define form used to add/edit events
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->addField('eventType');
        $this->addField('date');
        $this->addField('agent');
        $this->addField('note');

        // Summarize/cache existing accession event data
        $this->eventData = [];

        foreach ($this->resource->accessionEvents as $event) {
            $note = $event->getNote();

            $this->eventData[] = [
                'id' => $event->id,
                'typeId' => $event->typeId,
                'date' => $event->getDate(['sourceCulture' => true]),
                'agent' => $event->getAgent(),
                'object' => $event,
                'note' => $note,
                'accessionId' => $this->resource->id,
            ];
        }
    }

    public function processForm()
    {
        $finalEvents = [];

        if (is_array($this->request->events)) {
            foreach ($this->request->events as $item) {
                // Continue only if event type is populated
                if (empty($item['eventType']) || empty($item['date'])) {
                    continue;
                }

                // Fetch or create new accession event object
                if (!empty($item['id'])) {
                    $finalEvents[] = $item['id'];

                    $event = QubitAccessionEvent::getById($item['id']);
                } else {
                    $event = new QubitAccessionEvent();
                }

                $event->accessionId = $this->resource->id;
                $event->typeId = $item['eventType'];
                $event->date = empty($item['date']) ? null : $item['date'];
                $event->agent = $item['agent'];
                $event->save();

                // Store note
                if (null === $note = $event->getNote()) {
                    $note = new QubitNote();
                    $note->objectId = $this->resource->id;
                    $note->typeId = QubitTerm::ACCESSION_EVENT_NOTE_ID;
                }

                $note->objectId = $event->id;
                $note->content = $item['note'];
                $note->save();
            }
        }

        // Delete the old accession events if they don't appear in the table (removed by multiRow.js)
        foreach ($this->eventData as $item) {
            if (false === array_search($item['id'], $finalEvents)) {
                $event = QubitAccessionEvent::getById($item['id']);
                $event->delete();
            }
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'eventType':
                $this->form->setValidator($name, new sfValidatorInteger());
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $this->eventTypes]));

                break;

            case 'date':
                $this->form->setValidator($name, new sfValidatorString());
                $widget = new sfWidgetFormInput(['label' => false]);
                $this->form->setWidget($name, $widget);

                break;

            case 'agent':
                $this->form->setValidator($name, new sfValidatorString());
                $widget = new sfWidgetFormInput(['label' => false]);
                $this->form->setWidget($name, $widget);

                break;

            case 'note':
                $this->form->setValidator($name, new sfValidatorString());
                $widget = new sfWidgetFormTextarea(['label' => false]);
                $widget->setAttribute('placeholder', $this->context->i18n->__('Notes'));
                $this->form->setWidget($name, $widget);

                break;
        }
    }
}
