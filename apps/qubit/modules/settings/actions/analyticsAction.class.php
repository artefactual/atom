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
 * Web analytics.
 */
class SettingsAnalyticsAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'google_analytics',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Web analytics settings saved.');

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
            case 'google_analytics':
                $this->form->setWidget($name, new sfWidgetFormInput());
                $this->form->setValidator($name, new sfValidatorString(['required' => false]));
                $labelText = $this->i18n->__('Google Analytics tracking ID');
                $this->form->getWidgetSchema()->{$name}->setLabel($labelText);

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'google_analytics':
                $gaTrackingId = htmlspecialchars($this->form->getValue('google_analytics'));

                if (null !== $gaTrackingId) {
                    QubitSetting::findAndSave('google_analytics', $gaTrackingId, ['sourceCulture' => true]);
                }

                break;
        }
    }
}
