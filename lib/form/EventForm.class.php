<?php

/**
 * Events form.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class EventForm extends QubitForm
{
    public function configureId()
    {
        $this->setValidator(
            'id',
            new sfValidatorString(
                ['max_length' => 255],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('id', new sfWidgetFormInputHidden());
    }

    public function configureDate()
    {
        $this->setValidator(
            'date',
            new sfValidatorString(
                ['max_length' => 255],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('date', new sfWidgetFormInput());
    }

    public function configureStartDate()
    {
        // Don't check date format because we can't show validation errors
        // in the events modal dialog.
        $this->setValidator(
            'startDate',
            new sfValidatorString(
                ['max_length' => 255],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('startDate', new sfWidgetFormInput());
    }

    public function configureEndDate()
    {
        // Don't check date format because we can't show validation errors
        // in the events modal dialog.
        $this->setValidator(
            'endDate',
            new sfValidatorString(
                ['max_length' => 255],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('endDate', new sfWidgetFormInput());
    }

    public function configureType()
    {
        $choices = [];
        $eventTypes = sfIsadPlugin::eventTypes();

        foreach ($eventTypes as $item) {
            $route = sfContext::getInstance()->routing->generate(
                null,
                [$item, 'module' => 'term']
            );
            $choices += [$route => $item->__toString()];
        }

        $this->setValidator('type', new sfValidatorChoice(
            ['choices' => array_keys($choices)]
        ));
        $this->setWidget('type', new sfWidgetFormSelect(
            ['choices' => $choices]
        ));
    }

    public function configureActor()
    {
        $this->setValidator(
            'actor',
            new sfValidatorString(
                ['max_length' => 1024],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('actor', new sfWidgetFormSelect(['choices' => []]));
    }

    public function configureDescription()
    {
        $this->setValidator(
            'description',
            new sfValidatorString(
                ['max_length' => 10000],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('description', new sfWidgetFormInput());
    }

    public function configurePlace()
    {
        $this->setValidator(
            'place',
            new sfValidatorString(
                ['max_length' => 1024],
                ['max_length' => 'Too long (Max. %max_length% characters)']
            )
        );
        $this->setWidget('place', new sfWidgetFormSelect(['choices' => []]));
    }

    /**
     * Configure event form.
     */
    public function configure()
    {
        $this->getWidgetSchema()->setNameFormat('event[%s]');
        $this->configureFields();
    }

    /**
     * Configure general event form fields.
     */
    public function configureFields()
    {
        $this->configureId();
        $this->configureActor();
        $this->configureType();
        $this->configurePlace();
        $this->configureDate();
        $this->configureStartDate();
        $this->configureEndDate();
        $this->configureDescription();
    }
}
