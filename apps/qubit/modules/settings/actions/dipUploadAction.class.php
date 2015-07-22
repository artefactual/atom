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
 * DIP Upload settings
 *
 * @package    AccesstoMemory
 * @subpackage settings
 */

class SettingsDipUploadAction extends sfAction
{
  public function execute($request)
  {
    $this->dipUploadForm = new SettingsDipUploadForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Handle DIP Upload form submission
      if (null !== $request->dip_upload)
      {
        $this->dipUploadForm->bind($request->dip_upload);
        if ($this->dipUploadForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateDipUploadSettings();
          $this->redirect('settings/dipUpload');
        }
      }
    }

    $this->populateDipUploadForm();
  }

  /**
   * Populate the DIP Upload form
   */
  protected function populateDipUploadForm()
  {
    $stripExtensions = QubitSetting::getByName('stripExtensions');

    $this->dipUploadForm->setDefaults(array(
      'strip_extensions' => (isset($stripExtensions)) ? $stripExtensions->getValue(array('sourceCulture'=>true)) : 1
    ));
  }

  /**
   * Update the DIP upload settings
   */
  protected function updateDipUploadSettings()
  {
    $thisForm = $this->dipUploadForm;

    if (null !== $stripExtensions = $thisForm->getValue('strip_extensions'))
    {
      $setting = QubitSetting::getByName('stripExtensions');
      $setting->setValue($stripExtensions, array('sourceCulture' => true));
      $setting->save();
    }

    return $this;
  }
}
