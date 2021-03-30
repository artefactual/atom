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

class SettingsPrivacyNotificationAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'privacy_notification_enabled',
        'privacy_notification',
    ];
    public static $I18N = [
        'privacy_notification',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Privacy notification settings saved.');
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'privacy_notification_enabled':
                $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];

                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));

                break;

            case 'privacy_notification':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;
        }
    }
}
