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

class SettingsCsvValidatorAction extends SettingsEditAction
{
    public const VALIDATOR_OFF = 0;
    public const VALIDATOR_PERMISSIVE = 1;
    public const VALIDATOR_STRICT = 2;

    public static $NAMES = [
        'csv_validator_default_import_behaviour',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('CSV Validator settings saved.');

        $this->settingDefaults = [
            'csv_validator_default_import_behaviour' => 0,
        ];
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'csv_validator_default_import_behaviour':
                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(
                    ['choices' => [
                        self::VALIDATOR_OFF => $this->i18n->__('Off - validation is not run before CSV imports'),
                        self::VALIDATOR_PERMISSIVE => $this->i18n->__('Permissive - validation is run; warnings are ignored'),
                        self::VALIDATOR_STRICT => $this->i18n->__('Strict - validation is run; warnings will halt import from running'),
                    ]],
                    ['class' => 'radio']
                ));

                break;
        }
    }
}
