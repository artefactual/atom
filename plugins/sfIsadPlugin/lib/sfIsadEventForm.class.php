<?php

/**
 * ISAD(G) dates event form.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class sfIsadEventForm extends EventForm
{
    public function configureFields()
    {
        // Configure ISAD dates event form fields
        $this->configureId();
        $this->configureType();
        $this->configureDate();
        $this->configureStartDate();
        $this->configureEndDate();
    }
}
