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

ProjectConfiguration::getActive()->loadHelpers('I18N');

/**
 * Global form definition for settings module - with validation.
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class SettingsOaiRepositoryForm extends sfForm
{
  protected static $resumptionTokenMinLimit = 10;
  protected static $resumptionTokenMaxLimit = 1000;

  public function configure()
  {
    // Build widgets
    $this->setWidgets(array(
      'oai_enabled' => new sfWidgetFormSelectRadio(array('choices'=>array(1=>'yes', 0=>'no')), array('class'=>'radio')),
      'oai_repository_code' => new sfWidgetFormInput,
      'oai_repository_identifier' => new sfWidgetFormInput(array(), array('class'=>'disabled', 'disabled'=>true)),
      'sample_oai_identifier' => new sfWidgetFormInput(array(), array('class'=>'disabled', 'disabled'=>true)),
      'resumption_token_limit' => new sfWidgetFormInput
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'oai_enabled' => __('Enable OAI'),
      'oai_repository_code' => __('OAI repository code'),
      'oai_repository_identifier' => __('OAI repository identifier'),
      'sample_oai_identifier' => __('Sample OAI identifier'),
      'resumption_token_limit' => __('Resumption token limit')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'oai_enabled' => __('When set to &quot;yes&quot;, this system will act as an OAI repository and respond to OAI harvesting requests'),
      'oai_repository_code' => __('Add an alpha-numeric code to uniquely identify this particular OAI repository within its network domain to create a unique, OAI-compliant identifier, e.g. oai:foo.org:repositorycode_10001'),
      'oai_repository_identifier' => ('this is an auto-generated setting that produces an OAI compliant repository identifier, which includes the OAI repository code value if it is set'),
      'sample_oai_identifier' => ('this is an example of the auto-generated, OAI compliant identifier which is created for each item in this particular OAI repository'),
      'resumption_token_limit' => __('The number of entities to include in a single OAI response list before inserting a resumption token')
    ));

    // Reference image max. width validator
    $this->validatorSchema['resumption_token_limit'] = new sfValidatorInteger(
      array(
        'required' => true,
        'min' => self::$resumptionTokenMinLimit,
        'max' => self::$resumptionTokenMaxLimit
      ),
      array(
        'required' => __('This field is required'),
        'min' => __('This value must be at least %min%'),
        'max' => __('This value cannot be more than %max%')
      )
    );

    $this->validatorSchema['oai_enabled'] = new sfValidatorInteger(array('required' => false));
    $this->validatorSchema['oai_repository_code'] = new sfValidatorRegex(array('required' => false, 'pattern' => '/^[a-zA-Z0-9]+$/'), array('invalid' => __('The code can only contain letters and numbers')));
    $this->validatorSchema['oai_repository_identifier'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['sample_oai_identifier'] = new sfValidatorString(array('required' => false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for OAI Harvesting form settings
    $this->widgetSchema->setNameFormat('oai_repository[%s]');
  }
}
