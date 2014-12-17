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
 * Interface labels
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsInterfaceLabelAction extends sfAction
{
  public function execute($request)
  {
    $this->uiLabelForm = new SettingsGenericForm(array(), array(
      'settings' => QubitSetting::getByScope('ui_label'), 'scope'=>'ui_label', 'fieldsRequired' => false));

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      if ($this->context->getViewCacheManager() !== null)
      {
        $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_browseMenu&sf_cache_key=*');
        $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_mainMenu&sf_cache_key=*');
      }

      // Handle UI label form submission
      if (null !== $request->ui_label)
      {
        $this->uiLabelForm->bind($request->ui_label);
        if ($this->uiLabelForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateUiLabelSettings($this->uiLabelForm);
          $this->redirect('settings/interfaceLabel');
        }
      }
    }

    $this->populateUiLabelForm($this->uiLabelForm);
  }

  /**
   * Populate the ui_label form with database values (localized)
   */
  protected function populateUiLabelForm($form)
  {
    foreach ($form->getSettings() as $setting)
    {
      $form->setDefault($setting->getName(), $setting->getValue());
    }
  }

  /**
   * Update ui_label db values with form values (localized)
   *
   * @return $this
   */
  protected function updateUiLabelSettings($form)
  {
    foreach ($form->getSettings() as $setting)
    {
      if (null !== $value = $form->getValue($setting->getName()))
      {
        $setting->setValue($value);
        $setting->save();
      }
    }

    // Add a new ui_label
    if (null !== ($newName = $form->getValue('new_setting_name')) && strlen($newValue = $form->getValue('new_setting_value')))
    {
      $setting = QubitSetting::createNewSetting($newName, $newValue, array('scope'=>$form->getScope()));
      $setting->save();
    }

    return $this;
  }
}
