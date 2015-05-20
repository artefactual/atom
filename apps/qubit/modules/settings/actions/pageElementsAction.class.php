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
 * Page elements
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsPageElementsAction extends sfAction
{
  public function execute($request)
  {
    $this->defaultPageElementsForm = new sfForm;
    $this->defaultPageElementsForm->setWidgets(array(
      'toggleDescription' => new sfWidgetFormInputCheckbox,
      'toggleLogo' => new sfWidgetFormInputCheckbox,
      'toggleTitle' => new sfWidgetFormInputCheckbox,
      'toggleLanguageMenu' => new sfWidgetFormInputCheckbox,
      'toggleIoSlider' => new sfWidgetFormInputCheckbox));

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleDescription');
    if (1 == count($toggleDescriptionQuery = QubitSetting::get($criteria)))
    {
      $toggleDescriptionSetting = $toggleDescriptionQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleDescription', $toggleDescriptionSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleLogo');
    if (1 == count($toggleLogoQuery = QubitSetting::get($criteria)))
    {
      $toggleLogoSetting = $toggleLogoQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleLogo', $toggleLogoSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleTitle');
    if (1 == count($toggleTitleQuery = QubitSetting::get($criteria)))
    {
      $toggleTitleSetting = $toggleTitleQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleTitle', $toggleTitleSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleLanguageMenu');
    if (1 == count($toggleLanguageMenuQuery = QubitSetting::get($criteria)))
    {
      $toggleLanguageMenuSetting = $toggleLanguageMenuQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleLanguageMenu', $toggleLanguageMenuSetting->__get('value', array('sourceCulture' => true)));
    }

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'toggleIoSlider');
    if (1 == count($toggleIoSliderQuery = QubitSetting::get($criteria)))
    {
      $toggleIoSliderSetting = $toggleIoSliderQuery[0];

      $this->defaultPageElementsForm->setDefault('toggleIoSlider', $toggleIoSliderSetting->__get('value', array('sourceCulture' => true)));
    }
  }
}
