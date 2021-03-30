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
 * Language settings.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsLanguageAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form->setValidator('languageCode', new sfValidatorI18nChoiceLanguage(['required' => true]));
        $this->form->setWidget('languageCode', new sfWidgetFormI18nChoiceLanguage(['add_empty' => true, 'culture' => $this->context->user->getCulture()]));

        // Handle POST data (form submit)
        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                try {
                    ProjectConfiguration::getActive()->loadHelpers('I18N');

                    format_language($request->languageCode, $request->languageCode);
                } catch (Exception $e) {
                    $this->redirect(['module' => 'settings', 'action' => 'language']);
                }

                QubitCache::getInstance()->removePattern('settings:i18n:*');

                $setting = new QubitSetting();
                $setting->name = $request->languageCode;
                $setting->scope = 'i18n_languages';
                $setting->value = $request->languageCode;
                $setting->deleteable = true;
                $setting->editable = true;
                $setting->getCurrentSettingI18n()->setCulture('en');
                $setting->sourceCulture = 'en';
                $setting->save();

                $notice = sfContext::getInstance()->i18n->__('Language saved.');
                $this->getUser()->setFlash('notice', $notice);
            }
        }

        $this->i18nLanguages = QubitSetting::getByScope('i18n_languages');
    }
}
