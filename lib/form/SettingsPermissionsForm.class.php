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
 * Settings module - "Permissions" form definition
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Andy Koch <koch.andy@gmail.com>
 */
class SettingsPermissionsForm extends sfForm
{
  public function configure()
  {
    $this->getValidatorSchema()->setOption('allow_extra_fields', true);

    //
    // PREMIS act
    //

    $premisAccessRight = QubitSetting::getByName('premisAccessRight');
    if (null === $premisAccessRight)
    {
      throw new sfException('Setting premisAccessRight cannot be found');
    }

    $choices = array();
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_ACT_ID) as $item)
    {
      $choices[$item->slug] = $item->__toString();
    }
    $this->setWidget('granted_right', new sfWidgetFormSelect(array('choices' => $choices)));
    $this->setDefault('granted_right', $premisAccessRight->getValue(array('sourceCulture' => true)));
    $this->setValidator('granted_right', new sfValidatorChoice(array('choices' => array_keys($choices))));

    //
    // PREMIS permissionss
    //

    $this->embedForm('permissions', $this->getPermissionsForm());
  }

  protected function getPermissionsForm()
  {
    $premisAccessRightValues = QubitSetting::getByName('premisAccessRightValues');
    if (null === $premisAccessRightValues)
    {
      throw new sfException('Setting premisAccessRightValues cannot be found');
    }

    $premisAccessRightValues = unserialize($premisAccessRightValues->getValue(array('sourceCulture' => true)));
    $defaults = QubitSetting::$premisAccessRightValueDefaults;

    $form = new sfForm;
    $form->getValidatorSchema()->setOption('allow_extra_fields', true);

    // Each basis has its own set of permissions (allow_master,
    // allow_reference, etc...). We are embedding a new sfForm ($formBasis) for
    // each basis and indexed by its slug.
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      $formBasis = new sfForm;
      $formBasis->getValidatorSchema()->setOption('allow_extra_fields', true);

      // Permissions are represented with sfWidgetFormInputCheckbox
      foreach ($defaults as $key => $value)
      {
        $formBasis->setWidget($key, new sfWidgetFormInputCheckbox);
        $formBasis->setValidator($key, new sfValidatorBoolean(array('empty_value' => false)));

        // The default value is obtained from the existing QubitSetting
        // premisAccessRightValues
        if (!empty($premisAccessRightValues[$item->slug]) && !empty($premisAccessRightValues[$item->slug][$key]))
        {
          $v = $premisAccessRightValues[$item->slug][$key];
          $formBasis->setDefault($key, $v);
        }
      }

      $form->embedForm($item->slug, $formBasis);
    }

    return $form;
  }
}
