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
 * Page elements.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsPageElementsAction extends sfAction
{
    public static $NAMES = [
        'toggleDescription',
        'toggleLogo',
        'toggleTitle',
        'toggleLanguageMenu',
        'toggleIoSlider',
        'toggleDigitalObjectMap',
        'toggleCopyrightFilter',
        'toggleMaterialFilter',
    ];

    public function execute($request)
    {
        $settings = [];
        $this->form = new sfForm();

        // Take note if a Google Maps API key has been set
        $googleMapsApiKeySetting = QubitSetting::getByName('google_maps_api_key');

        $this->googleMapsApiKeySet = isset($googleMapsApiKeySetting)
            && !empty(
                $googleMapsApiKeySetting->getValue(['sourceCulture' => true])
            );

        // Take note of whether digital object map is enabled
        $toggleDigitalObjectMapSetting = QubitSetting::getByName('toggleDigitalObjectMap');

        foreach ($this::$NAMES as $name) {
            // Disable checkbox to show digital object maps if it's not currently enabled and no Google Maps API key is defined
            if (
                'toggleDigitalObjectMap' == $name
                && isset($toggleDigitalObjectMapSetting)
                && empty(
                    $toggleDigitalObjectMapSetting->getValue(
                        ['sourceCulture' => true]
                    )
                )
                && !$this->googleMapsApiKeySet
            ) {
                $this->form->setWidget(
                    $name,
                    new sfWidgetFormInputCheckbox(
                        [],
                        ['class' => 'disabled', 'disabled' => true],
                    )
                );
            } else {
                $this->form->setWidget($name, new sfWidgetFormInputCheckbox());
            }

            $this->form->setValidator($name, new sfValidatorBoolean());

            if (null !== $settings[$name] = QubitSetting::getByName($name)) {
                $this->form->setDefault($name, filter_var($settings[$name]->__get('value', ['sourceCulture' => true]), FILTER_VALIDATE_BOOLEAN));
            }
        }

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if (!$this->form->isValid()) {
                return;
            }

            foreach ($this::$NAMES as $name) {
                if (null === $settings[$name]) {
                    $settings[$name] = new QubitSetting();
                    $settings[$name]->name = $name;
                }

                $settings[$name]->__set('value', filter_var($this->form->getValue($name), FILTER_VALIDATE_BOOLEAN), ['sourceCulture' => true]);
                $settings[$name]->save();
            }

            QubitCache::getInstance()->removePattern('settings:i18n:*');

            $notice = sfContext::getInstance()->i18n->__('Default page elements saved.');
            $this->getUser()->setFlash('notice', $notice);

            // Redirect to display changes in the interface
            // because the settings are added to sfConfig in a filter
            $this->redirect(['module' => 'settings', 'action' => 'pageElements']);
        }
    }
}
