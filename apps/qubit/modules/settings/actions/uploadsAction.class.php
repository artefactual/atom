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

class SettingsUploadsAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'enable_repository_quotas',
        'explode_multipage_files',
        'repository_quota',
        'upload_quota',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Uploads settings saved.');

        $this->settingDefaults = [
            'enable_repository_quotas' => 1,
            'explode_multipage_files' => 0,
            'repository_quota' => 0,
            'upload_quota' => -1,
        ];

        // Set form decorator
        if (!sfConfig::get('app_b5_theme', false)) {
            $this->form->getWidgetSchema()->addFormFormatter(
                'list',
                new QubitWidgetFormSchemaFormatterList($this->form->getWidgetSchema())
            );
            $this->form->getWidgetSchema()->setFormFormatterName('list');
        }
    }

    protected function addField($name)
    {
        // Set form field format
        switch ($name) {
            case 'enable_repository_quotas':
                $this->form->setValidator($name, new sfValidatorBoolean(
                    ['required' => true]
                ));
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(
                    ['choices' => [
                        0 => $this->i18n->__('Disabled'),
                        1 => $this->i18n->__('Enabled'),
                    ]],
                    ['class' => 'radio']
                ));

                break;

            case 'explode_multipage_files':
                $this->form->setValidator($name, new sfValidatorBoolean(
                    ['required' => true]
                ));
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(
                    ['choices' => [
                        0 => $this->i18n->__('No'),
                        1 => $this->i18n->__('Yes'),
                    ]],
                    ['class' => 'radio']
                ));

                break;

            case 'repository_quota':
                $this->form->setValidator($name, new sfValidatorNumber(
                    ['required' => true, 'min' => -1],
                    ['min' => $this->i18n->__('Minimum value is "%min%"')]
                ));
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'upload_quota':
                // No validator, because the global quota is set via config file
                // and can't be changed in the UI
                $this->form->setWidget($name, new arWidgetFormUploadQuota());

                break;
        }
    }
}
