<?php

/**
 * Rules for Archival Description (RAD) Events form.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class sfRadEventForm extends EventForm
{
    public function configure()
    {
        $this->getWidgetSchema()->setNameFormat('event[%s]');

        // Configure fields included in all event forms
        $this->configureId();
        $this->configureType();
        $this->configureActor();
        $this->configureDate();
        $this->configureStartDate();
        $this->configureEndDate();
        $this->configureDescription();
    }
}
