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

/**
 * Job scheduler form definition for settings module
 *
 * @package    AccesstoMemory
 * @subpackage settings
 */
class SettingsJobSchedulingForm extends sfForm
{
  public function configure()
  {
    $i18n = sfContext::getInstance()->i18n;

    // Build widgets
    $this->setWidgets(array(
      'use_job_scheduler' => new sfWidgetFormSelectRadio(array('choices' => array(1 => 'yes', 0 => 'no')), array('class' => 'radio'))
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'use_job_scheduler' => $i18n->__('Enable job scheduler')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'use_job_scheduler' => ''
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
