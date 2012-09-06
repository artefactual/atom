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
 * Settings module - "site information" form definition
 *
 * @package    qubit
 * @subpackage settings
 * @version    svn: $Id: SettingsSiteInformationForm.class.php 10288 2011-11-08 21:25:05Z mj $
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsSiteInformationForm extends sfForm
{
  public function configure()
  {
    // Build widgets
    $this->setWidgets(array(
      'site_title' => new sfWidgetFormInput,
      'site_description' => new sfWidgetFormInput
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'site_title' => __('Site title'),
      'site_description' => __('Site description')
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

    // Set decorator
    $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
    $this->widgetSchema->addFormFormatter('list', $decorator);
    $this->widgetSchema->setFormFormatterName('list');

    // Set wrapper text for global form settings
    $this->widgetSchema->setNameFormat('site_information[%s]');
  }
}
