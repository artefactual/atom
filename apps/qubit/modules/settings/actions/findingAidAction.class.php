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
 * Finding Aid settings
 *
 * @package    AccesstoMemory
 * @subpackage settings
 */

class SettingsFindingAidAction extends sfAction
{
  public function execute($request)
  {
    $this->findingAidForm = new SettingsFindingAidForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Handle Finding Aid form submission
      if (null !== $request->finding_aid)
      {
        $this->findingAidForm->bind($request->finding_aid);
        if ($this->findingAidForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateFindingAidSettings();
          $this->redirect('settings/findingAid');
        }
      }
    }

    $this->populateFindingAidForm();
  }

  /**
   * Populate the Finding Aid form
   */
  protected function populateFindingAidForm()
  {
    $findingAidFormat = QubitSetting::getByName('findingAidFormat');
    $findingAidModel = QubitSetting::getByName('findingAidModel');
    $publicFindingAid = QubitSetting::getByName('publicFindingAid');

    $this->findingAidForm->setDefaults(array(
      'finding_aid_format' => (isset($findingAidFormat)) ? $findingAidFormat->getValue(array('sourceCulture'=>true)) : 'pdf',
      'finding_aid_model' => (isset($findingAidModel)) ? $findingAidModel->getValue(array('sourceCulture'=>true)) : 'inventory-summary',
      'public_finding_aid' => (isset($publicFindingAid)) ? $publicFindingAid->getValue(array('sourceCulture'=>true)) : 1
    ));
  }

  /**
   * Update the Finding Aid settings
   */
  protected function updateFindingAidSettings()
  {
    $thisForm = $this->findingAidForm;

    if (null !== $findingAidFormat = $thisForm->getValue('finding_aid_format'))
    {
      $setting = QubitSetting::getByName('findingAidFormat');
      $setting->setValue($findingAidFormat, array('sourceCulture' => true));
      $setting->save();
    }

    if (null !== $findingAidModel = $thisForm->getValue('finding_aid_model'))
    {
      $setting = QubitSetting::getByName('findingAidModel');
      $setting->setValue($findingAidModel, array('sourceCulture' => true));
      $setting->save();
    }

    if (null !== $publicFindingAid = $thisForm->getValue('public_finding_aid'))
    {
      $setting = QubitSetting::getByName('publicFindingAid');
      $setting->setValue($publicFindingAid, array('sourceCulture' => true));
      $setting->save();
    }

    return $this;
  }
}
