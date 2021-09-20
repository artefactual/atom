<?php

/**
 * ISAD(G) dates event form.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class sfIsadEventForm extends EventForm
{
    public const DATE_REGEX = '/^\d{4}-?\d{0,2}-?\d{0,2}$/';

    public function configureStartDate()
    {
        $this->setValidator('startDate', new sfValidatorRegex(
            ['max_length' => 10, 'pattern' => self::DATE_REGEX],
            [
                'invalid' => 'Start date must be in the format YYYY-MM-DD or
                    YYYYMMDD',
            ],
        ));
        $this->setWidget('startDate', new sfWidgetFormInput());
    }

    public function configureEndDate()
    {
        $this->setValidator('endDate', new sfValidatorRegex(
            ['max_length' => 10, 'pattern' => self::DATE_REGEX],
            [
                'invalid' => 'End date must be in the format YYYY-MM-DD or
                    YYYYMMDD',
            ],
        ));
        $this->setWidget('endDate', new sfWidgetFormInput());
    }

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
