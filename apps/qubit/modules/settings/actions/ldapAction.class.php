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

class SettingsLdapAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'ldapHost',
        'ldapPort',
        'ldapBaseDn',
        'ldapBindAttribute',
    ];

    public function execute($request)
    {
        parent::execute($request);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();

                QubitCache::getInstance()->removePattern('settings:i18n:*');

                $this->redirect(['module' => 'settings', 'action' => 'ldap']);
            }
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'ldapHost':
            case 'ldapPort':
            case 'ldapBaseDn':
            case 'ldapBindAttribute':
                // Determine and set field default value
                if (null !== $this->{$name} = QubitSetting::getByName($name)) {
                    $default = $this->{$name}->getValue(['sourceCulture' => true]);
                } else {
                    $defaults = [
                        'ldapPort' => '389',
                        'ldapBindAttribute' => 'uid',
                    ];

                    $default = (isset($defaults[$name])) ? $defaults[$name] : '';
                }

                $this->form->setDefault($name, $default);

                // Set validator and widget
                $validator = ('ldapPort' == $name) ? new sfValidatorInteger(['min' => 1, 'max' => 65535]) : new sfValidatorPass();
                $this->form->setValidator($name, $validator);
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;
        }
    }

    protected function processField($field)
    {
        switch ($name = $field->getName()) {
            case 'ldapHost':
            case 'ldapPort':
            case 'ldapBaseDn':
            case 'ldapBindAttribute':
                if (null === $this->{$name}) {
                    $this->{$name} = new QubitSetting();
                    $this->{$name}->name = $name;
                    $this->{$name}->scope = 'ldap';
                }
                $this->{$name}->setValue($field->getValue(), ['sourceCulture' => true]);
                $this->{$name}->save();

                break;
        }
    }
}
