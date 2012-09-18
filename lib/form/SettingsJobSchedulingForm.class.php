<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

ProjectConfiguration::getActive()->loadHelpers('I18N');

/**
 * Global form definition for settings module - with validation.
 *
 * @package    qubit
 * @subpackage settings
 */
class SettingsJobSchedulingForm extends sfForm
{
  public function configure()
  {
    // Build widgets
    $this->setWidgets(array(
      'use_job_scheduler' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio'))
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'use_job_scheduler' => __('Enable job scheduler')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'use_job_scheduler' => __('')
    ));

    $this->validatorSchema['use_job_scheduler'] = new sfValidatorInteger(array('required' => false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for job scheduling settings
    $this->widgetSchema->setNameFormat('job_scheduling[%s]');
  }
}
