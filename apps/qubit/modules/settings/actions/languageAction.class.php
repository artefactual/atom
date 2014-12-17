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
 * Language settings
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsLanguageAction extends sfAction
{
  public function execute($request)
  {
    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      if (null !== $languageCode = $request->languageCode)
      {
        try
        {
          ProjectConfiguration::getActive()->loadHelpers('I18N');

          format_language($languageCode, $languageCode);
        }
        catch (Exception $e)
        {
          $this->redirect(array('module' => 'settings', 'action' => 'language'));
        }

        $setting = new QubitSetting;
        $setting->name = $languageCode;
        $setting->scope = 'i18n_languages';
        $setting->value = $languageCode;
        $setting->deleteable = true;
        $setting->editable = true;
        $setting->getCurrentSettingI18n()->setCulture('en');
        $setting->sourceCulture = 'en';

        $setting->save();
      }
    }

    $this->form = new sfForm;
    $this->form->setValidator('languageCode', new sfValidatorI18nChoiceLanguage);
    $this->form->setWidget('languageCode', new sfWidgetFormI18nChoiceLanguage(array('add_empty' => true, 'culture' => $this->context->user->getCulture())));

    $this->i18nLanguages = QubitSetting::getByScope('i18n_languages');
  }
}
