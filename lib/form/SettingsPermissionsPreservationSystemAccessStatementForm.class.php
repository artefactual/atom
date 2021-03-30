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

class SettingsPermissionsPreservationSystemAccessStatementForm extends sfForm
{
    public function configure()
    {
        $this->widgetSchema->setNameFormat('preservationSystemAccessStatement[%s]');
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->setWidget('preservationSystemAccessStatementEnabled', new sfWidgetFormSelectRadio(['choices' => [1 => 'yes', 0 => 'no']], ['class' => 'radio']));
        $this->setValidator('preservationSystemAccessStatementEnabled', new sfValidatorInteger(['required' => false]));

        $default = false;
        if (null !== $setting = QubitSetting::getByName('digitalobject_preservation_system_access_statement_enabled')) {
            $value = $setting->getValue(['sourceCulture' => true]);
            if (!empty($value)) {
                $default = $value;
            }
        }
        $this->setDefault('preservationSystemAccessStatementEnabled', $default);

        $this->setWidget('preservationSystemAccessStatement', new sfWidgetFormTextArea([], ['rows' => 4]));
        $this->setValidator('preservationSystemAccessStatement', new sfValidatorString());
        if (null !== $setting = QubitSetting::getByName('digitalobject_preservation_system_access_statement')) {
            $this->setDefault('preservationSystemAccessStatement', $setting->getValue());
        }
    }
}
