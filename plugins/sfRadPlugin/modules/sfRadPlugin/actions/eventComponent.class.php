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

class sfRadPluginEventComponent extends InformationObjectEventComponent
{
    public function setHelps($form)
    {
        $this->context->getConfiguration()->loadHelpers(['I18N']);

        $form->getWidgetSchema()->setHelps([
            'date' => __(
<<<'EOL'
"Give the date(s) of creation of the unit being described either as a single
date, or range of dates (for inclusive dates and/or predominant dates). Always
give the inclusive dates. When providing predominant dates, specify them as
such, preceded by the word predominant..." (RAD 1.4B2) Record probable and
uncertain dates in square brackets, using the conventions described in 1.4B5.
EOL
            ),
            'description' => __(
<<<'EOL'
"Make notes on dates and any details pertaining to the dates of creation,
publication, or distribution, of the unit being described that are not included
in the Date(s) of creation, including publication, distribution, etc., area and
that are considered to be important." (RAD 1.8B8) "Make notes on the date(s) of
accumulation or collection of the unit being described." (RAD 1.8B8a)
EOL
            ),
            'place' => __(
<<<'EOL'
"For an item, transcribe a place of publication, distribution, etc., in the form
and the grammatical case in which it appears." (RAD 1.4C1)
EOL
            ),
            'type' => __(
<<<'EOL'
Select the type of activity that established the relation between the authority
record and the archival description (e.g. creation, accumulation, collection,
publication, etc.)
EOL
            ),
        ]);
    }

    /**
     * Add event sub-forms to $this->events form.
     *
     * Add one event sub-form for each event linked to $resource
     */
    protected function addEventForms()
    {
        $i = 0;

        // Add one event sub-form for each event related to this resource, to
        // allow editing the existing events
        foreach ($this->getEvents() as $event) {
            // Embed the event sub-form into the $this->events form
            $form = new sfRadEventForm($this->getFormDefaults($event));
            $this->events->embedForm($i++, $form);
        }
    }
}
