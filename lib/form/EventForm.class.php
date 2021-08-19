<?php

/**
 * Events form.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class EventForm extends QubitForm
{
    public const DATE_REGEX = '/^\d{4}-?\d{0,2}-?\d{0,2}$/';

    public function configureId()
    {
        $this->setValidator('id', new sfValidatorString(
            ['max_length' => 255],
            ['max_length' => 'Id must be less than 255 characters']
        ));
        $this->setWidget('id', new sfWidgetFormInputHidden());
    }

    public function configureDate()
    {
        $this->setValidator('date', new sfValidatorString(
            ['max_length' => 255],
            ['max_length' => 'Date must be less than 255 characters']
        ));
        $this->setWidget('date', new sfWidgetFormInput());
    }

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
        $this->setValidator('actor', new sfValidatorString());
        $this->setWidget('actor', new sfWidgetFormSelect(['choices' => []]));
    }

    public function configureDescription()
    {
        $this->setValidator('description', new sfValidatorString());
        $this->setWidget('description', new sfWidgetFormInput());
    }

    public function configurePlace()
    {
        $this->setValidator('place', new sfValidatorString());
        $this->setWidget('place', new sfWidgetFormSelect(['choices' => []]));
    }

    public function configure()
    {
        $this->getWidgetSchema()->setNameFormat('event[%s]');

        // Configure fields included in all event forms
        $this->configureId();
        $this->configureDate();
        $this->configureStartDate();
        $this->configureEndDate();
        $this->configureType();
    }
}
