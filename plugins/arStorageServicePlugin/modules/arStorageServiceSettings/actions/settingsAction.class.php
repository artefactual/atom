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

class arStorageServiceSettingsSettingsAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'storage_service_api_url',
        'storage_service_username',
        'storage_service_api_key',
        'download_aip_enabled',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Storage service settings saved.');

        $this->settingDefaults = [
            'download_aip_enabled' => '0',
        ];
    }

    protected function addField($name)
    {
        // Set form field format
        switch ($name) {
            case 'storage_service_api_url':
            case 'storage_service_username':
            case 'storage_service_api_key':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'download_aip_enabled':
                $options = [
                    '0' => $this->i18n->__('Disabled'),
                    '1' => $this->i18n->__('Enabled'),
                ];

                $this->form->setValidator($name, new sfValidatorString(
                    ['required' => false]
                ));
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(
                    ['choices' => $options],
                    ['class' => 'radio']
                ));

                break;
        }
    }
}
