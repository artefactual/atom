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
 * Settings module - "site information" form definition
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsSiteInformationForm extends sfForm
{
  public function configure()
  {
    $i18n = sfContext::getInstance()->i18n;

    // Build widgets
    $this->setWidgets(array(
      'site_title' => new sfWidgetFormInput,
      'site_description' => new sfWidgetFormInput,
      'site_base_url' => new sfWidgetFormInput
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'site_title' => $i18n->__('Site title'),
      'site_description' => $i18n->__('Site description'),
      'site_base_url' => $i18n->__('Site base URL (used in MODS and EAD exports)')
    ));

    // Add helper text
    // NOTE: This is implemented in the template because it was too much
    // trouble to integrate the helper text without rendering the whole form
    // row due to the lack of a renderHelp() method in sfFormField.class.php
    //
    // $this->widgetSchema->setHelps();

    // Validators
    $this->validatorSchema['site_title'] = new sfValidatorString(array('required'=>false));
    $this->validatorSchema['site_description'] = new sfValidatorString(array('required'=>false));
    $this->validatorSchema['site_base_url'] = new sfValidatorString(array('required'=>false));

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for global form settings
    $this->widgetSchema->setNameFormat('site_information[%s]');
  }
}
