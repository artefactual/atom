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
 * Settings module - "site information" form definition
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Andy Koch <koch.andy@gmail.com>
 */
class SettingsPermissionsForm extends sfForm
{

  protected function getGrantedRightChoices()
  {
    $choices = null;
    $routing  = sfContext::getInstance()->getRouting();

    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_ACT_ID) as $gr)
    {
      $choices[$gr->slug] = $gr->__toString();
    }

    return $choices;
  }

  protected function newSubForm()
  {
    $choices = array(
      '0' => __('Disallowed'),
      '1' => __('Allowed')
    );

    $form = new sfForm;

    $form->setWidgets(array(
      'allow_master'           => new sfWidgetFormSelect(array('choices' => $choices)),
      'allow_reference'        => new sfWidgetFormSelect(array('choices' => $choices)),
      'allow_thumb'            => new sfWidgetFormSelect(array('choices' => $choices)),
      'conditional_master'     => new sfWidgetFormSelect(array('choices' => $choices)),
      'conditional_reference'  => new sfWidgetFormSelect(array('choices' => $choices)),
      'conditional_thumb'      => new sfWidgetFormSelect(array('choices' => $choices)),
      'disallow_master'        => new sfWidgetFormSelect(array('choices' => $choices)),
      'disallow_reference'     => new sfWidgetFormSelect(array('choices' => $choices)),
      'disallow_thumb'         => new sfWidgetFormSelect(array('choices' => $choices))
    ));

    $form->widgetSchema->setLabels(array(
      'allow_master'           => __('View / download master representation'),
      'allow_reference'        => __('View / download reference representation'),
      'allow_thumb'            => __('View / download thumbnail representation'),
      'conditional_master'     => __('View / download master representation'),
      'conditional_reference'  => __('View / download reference representation'),
      'conditional_thumb'      => __('View / download thumbnail representation'),
      'disallow_master'        => __('View / download master representation'),
      'disallow_reference'     => __('View / download reference representation'),
      'disallow_thumb'         => __('View / download thumbnail representation')
    ));

    $form->setDefaults(unserialize($this->premisAccessRightValues->value));

    return $form;
  }

  public function configure()
  {
    $this->premisAccessRight = QubitSetting::getByName('premisAccessRight');
    $this->premisAccessRightValues = QubitSetting::getByName('premisAccessRightValues');

    if (null === $this->premisAccessRightValues)
    {
      $this->premisAccessRightValues = QubitSetting::createNewSetting(
        'premisAccessRightValues', serialize(QubitSetting::$premisAccessRightValueDefaults), array('deleteable' => false)
      );

      $this->premisAccessRightValues->save();
    }

    if (null === $this->premisAccessRight)
    {
      $this->premisAccessRight = QubitSetting::createNewSetting('premisAccessRight', 'delete', array('deleteable' => false));
      $this->premisAccessRight->save();
    }

    $this->setWidget(
      'granted_right', new sfWidgetFormSelect(array('choices' => $this->getGrantedRightChoices()))
    );

    $this->getWidget('granted_right')->setDefault($this->premisAccessRight->value);
    $this->embedForm('permissions', $this->newSubForm());
  }
}
