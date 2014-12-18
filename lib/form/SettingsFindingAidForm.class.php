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
 * Finding Aid form definition for settings module - with validation.
 *
 * @package    AccesstoMemory
 * @subpackage settings
 */
class SettingsFindingAidForm extends sfForm
{
  public function configure()
  {
    $i18n = sfContext::getInstance()->i18n;

    // Build widgets
    $this->setWidgets(array(
      'finding_aid_format' =>  new sfWidgetFormSelect(array('choices'=>array('pdf' => 'PDF', 'rtf' => 'RTF', 'txt' => 'TXT')))
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'finding_aid_format' => $i18n->__('Finding Aid format')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'finding_aid_format' => ''
    ));

    $this->validatorSchema['finding_aid_format'] = new sfValidatorString(array('required' => false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for Finding Aid settings
    $this->widgetSchema->setNameFormat('finding_aid[%s]');
  }
}
