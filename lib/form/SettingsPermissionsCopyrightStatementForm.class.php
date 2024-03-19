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

class SettingsPermissionsCopyrightStatementForm extends sfForm
{
    public function configure()
    {
        $this->widgetSchema->setNameFormat('copyrightStatement[%s]');
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->i18n = sfContext::getInstance()->i18n;
        $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];

        $this->setWidget('copyrightStatementEnabled', new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));
        $this->setValidator('copyrightStatementEnabled', new sfValidatorInteger(['required' => false]));

        $default = false;
        if (null !== $setting = QubitSetting::getByName('digitalobject_copyright_statement_enabled')) {
            $value = $setting->getValue(['sourceCulture' => true]);
            if (!empty($value)) {
                $default = $value;
            }
        }
        $this->setDefault('copyrightStatementEnabled', $default);

        $this->setWidget('copyrightStatement', new sfWidgetFormTextArea([], ['rows' => 4]));
        $this->setValidator('copyrightStatement', new sfValidatorString());
        if (null !== $setting = QubitSetting::getByName('digitalobject_copyright_statement')) {
            $this->setDefault('copyrightStatement', $setting->getValue());
        }

        $this->setWidget('copyrightStatementApplyGlobally', new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));
        $this->setValidator('copyrightStatementApplyGlobally', new sfValidatorInteger(['required' => false]));

        $default = false;
        if (null !== $setting = QubitSetting::getByName('digitalobject_copyright_statement_apply_globally')) {
            $value = $setting->getValue(['sourceCulture' => true]);
            if (!empty($value)) {
                $default = $value;
            }
        }
        $this->setDefault('copyrightStatementApplyGlobally', $default);
    }
}
