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
 * Information Object module - rename modal form definition
 *
 * @package    AccesstoMemory
 * @subpackage informationobject
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class InformationObjectRenameForm extends sfForm
{
  public function configure()
  {
    $i18n = sfContext::getInstance()->i18n;

    // Build widgets
    $this->setWidgets(array(
      'title' => new sfWidgetFormInput,
      'slug' => new sfWidgetFormInput,
      'filename' => new sfWidgetFormInput
    ));

    // Add labels
    $this->widgetSchema->setLabels(array(
      'title' => $i18n->__('Description title'),
      'slug' => $i18n->__('Slug'),
      'filename' => $i18n->__('File name')
    ));

    // Add helper text
    $this->widgetSchema->setHelps(array(
      'title' => $i18n->__('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.'),
      'slug' => $i18n->__('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.'),
      'filename' => $i18n->__('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.')
    ));

    // Validators
    $this->validatorSchema['title'] = new sfValidatorString(array('required'=>false));
    $this->validatorSchema['slug'] = new sfValidatorString(array('required'=>false));
    $this->validatorSchema['filename'] = new sfValidatorString(array('required'=>false));

    // Set wrapper text for rename form
    $this->widgetSchema->setNameFormat('rename[%s]');
  }
}
