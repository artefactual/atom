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

class SettingsEditAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $I18N = [];

    public function execute($request)
    {
        parent::execute($request);

        // Handle posted data
        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters(), $request->getFiles());

            if ($this->form->isValid()) {
                $this->processForm();

                QubitCache::getInstance()->removePattern('settings:i18n:*');

                if (!empty($this->updateMessage)) {
                    $this->getUser()->setFlash('notice', $this->updateMessage);
                }

                $this->redirect([
                    'module' => $this->getContext()->getModuleName(),
                    'action' => $this->getContext()->getActionName(),
                ]);
            }
        }

        // Set form field defaults
        foreach ($this::$NAMES as $name) {
            $this->setFormFieldDefault($name);
        }
    }

    protected function earlyExecute()
    {
        $this->settings = [];
        $this->culture = $this->context->user->getCulture();
        $this->i18n = sfContext::getInstance()->i18n;

        // Load setting for each field name
        foreach ($this::$NAMES as $name) {
            $this->settings[$name] = (null !== ${$name} = QubitSetting::getByName($name)) ? ${$name} : new QubitSetting();
        }
    }

    protected function setFormFieldDefault($name)
    {
        // If there's no settings default set, use blank string as default
        $settingDefault = (isset($this->settingDefaults[$name])) ? $this->settingDefaults[$name] : '';

        // Default setting value in form will be current setting value or, if none exists, settings default
        $settingGetOptions = (in_array($name, $this::$I18N)) ? [] : ['sourceCulture' => true];

        // Use setting default if setting hasn't been saved yet
        $settingValue = (null !== $this->settings[$name]->id) ? $this->settings[$name]->getValue($settingGetOptions) : $settingDefault;

        // Turn empty values to false for checkboxes
        $settingValue = ($this->form[$name]->getWidget() instanceof sfWidgetFormInputCheckbox && empty($settingValue)) ? false : $settingValue;

        $this->form->setDefault($name, $settingValue);
    }

    protected function processField($field)
    {
        $name = $field->getName();

        if (in_array($name, $this::$NAMES)) {
            if (null === $this->settings[$name]->id) {
                $this->settings[$name]->name = $name;
                $this->settings[$name]->sourceCulture = $this->culture;
                $this->settings[$name]->culture = $this->culture;
            }

            $settingSetOptions = (in_array($name, $this::$I18N)) ? ['culture' => $this->culture] : ['sourceCulture' => true];

            // Checkbox submissions get handled differently
            if ($field->getWidget() instanceof sfWidgetFormInputCheckbox) {
                $value = isset($this->request[$name]) ? $field->getValue() : '';
            } else {
                $value = $field->getValue();
            }

            $this->settings[$name]->setValue($value, $settingSetOptions);

            $this->settings[$name]->save();
        }
    }
}
