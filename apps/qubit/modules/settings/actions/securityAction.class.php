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
 * Security.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class SettingsSecurityAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'limit_admin_ip',
        'require_ssl_admin',
        'require_strong_passwords',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Security settings saved.');

        // Set form decorator
        if (!sfConfig::get('app_b5_theme', false)) {
            $decorator = new QubitWidgetFormSchemaFormatterList($this->form->getWidgetSchema());
            $this->form->getWidgetSchema()->addFormFormatter('list', $decorator);
            $this->form->getWidgetSchema()->setFormFormatterName('list');
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'limit_admin_ip':
                $this->form->setWidget($name, new sfWidgetFormInput());
                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $labelText = $this->i18n->__('Limit authenticated functionality to one or more IP addresses, separated by semicolons.');
                $this->form->getWidgetSchema()->{$name}->setLabel($labelText);

                break;

            case 'require_ssl_admin':
                $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));
                $this->form->setValidator($name, new sfValidatorInteger(['required' => false]));
                $labelText = $this->i18n->__('Require SSL for all administrator functionality');
                $this->form->getWidgetSchema()->{$name}->setLabel($labelText);

                break;

            case 'require_strong_passwords':
                $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));
                $this->form->setValidator($name, new sfValidatorInteger(['required' => false]));
                $labelText = $this->i18n->__('Require strong passwords');
                $this->form->getWidgetSchema()->{$name}->setLabel($labelText);

                break;
        }
    }
}
