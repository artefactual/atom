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

class SettingsDipUploadAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'stripExtensions',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('DIP upload settings saved.');

        $this->settingDefaults = [
            'stripExtensions' => '0',
        ];
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'stripExtensions':
                $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));
                $this->form->setValidator($name, new sfValidatorInteger(['required' => false]));

                break;
        }
    }
}
