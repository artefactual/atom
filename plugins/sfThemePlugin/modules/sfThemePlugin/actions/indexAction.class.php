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

class sfThemePluginIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->form->setWidgets(array(
      'toggleDescription' => new sfWidgetFormInputCheckbox,
      'toggleLogo' => new sfWidgetFormInputCheckbox,
      'toggleTitle' => new sfWidgetFormInputCheckbox));

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleDescription');
    if (1 == count($toggleDescriptionQuery = QubitSetting::get($criteria)))
    {
      $toggleDescriptionSetting = $toggleDescriptionQuery[0];

      $this->form->setDefault('toggleDescription', $toggleDescriptionSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleLogo');
    if (1 == count($toggleLogoQuery = QubitSetting::get($criteria)))
    {
      $toggleLogoSetting = $toggleLogoQuery[0];

      $this->form->setDefault('toggleLogo', $toggleLogoSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleTitle');
    if (1 == count($toggleTitleQuery = QubitSetting::get($criteria)))
    {
      $toggleTitleSetting = $toggleTitleQuery[0];

      $this->form->setDefault('toggleTitle', $toggleTitleSetting->__get('value', array('sourceCulture' => true)));
    }

    if ($request->isMethod('post'))
    {
      $this->form->setValidators(array(
        'toggleDescription' => new sfValidatorBoolean,
        'toggleLogo' => new sfValidatorBoolean,
        'toggleTitle' => new sfValidatorBoolean));

      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        if (1 != count($toggleDescriptionQuery))
        {
          $toggleDescriptionSetting = new QubitSetting;
          $toggleDescriptionSetting->name = 'toggleDescription';
        }

        $toggleDescriptionSetting->__set('value', $this->form->getValue('toggleDescription'), array('sourceCulture' => true));
        $toggleDescriptionSetting->save();

        if (1 != count($toggleLogoQuery))
        {
          $toggleLogoSetting = new QubitSetting;
          $toggleLogoSetting->name = 'toggleLogo';
        }

        $toggleLogoSetting->__set('value', $this->form->getValue('toggleLogo'), array('sourceCulture' => true));
        $toggleLogoSetting->save();

        if (1 != count($toggleTitleQuery))
        {
          $toggleTitleSetting = new QubitSetting;
          $toggleTitleSetting->name = 'toggleTitle';
        }

        $toggleTitleSetting->__set('value', $this->form->getValue('toggleTitle'), array('sourceCulture' => true));
        $toggleTitleSetting->save();

        $this->redirect(array('module' => 'settings', 'action' => 'list'));
      }
    }
  }
}
