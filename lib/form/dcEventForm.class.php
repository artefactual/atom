<?php

/**
 * Dublin Core event form.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class dcEventForm extends eventForm
{
    public function configureType()
    {
        $eventTypes = sfDcPlugin::eventTypes();

        parent::configureType();
    }
}
