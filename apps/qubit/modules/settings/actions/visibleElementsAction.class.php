<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
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
 * List of qubit settings.
 *
 * @author     Wu liu <wu.liu@usask.ca>
 */
class SettingsVisibleElementsAction extends sfAction
{
    public function execute($request)
    {
        $title = $this->context->i18n->__('Visible elements');
        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        $this->form = new sfForm();

        foreach (QubitSetting::getByScope('element_visibility') as $item) {
            $this->addField($item);
        }

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if (!$this->form->isValid()) {
                return;
            }

            $this->processForm();

            QubitCache::getInstance()->removePattern('settings:i18n:*');

            $this->getUser()->setFlash('notice', sfContext::getInstance()->i18n->__('Visible elements configuration saved.'));

            $this->redirect('settings/visibleElements');
        }
    }

    protected function addField(QubitSetting $setting)
    {
        $name = $setting->name;

        $this->form->setDefault($name, (bool) $setting->getValue(['sourceCulture' => true]));
        $this->form->setValidator($name, new sfValidatorBoolean());
        $this->form->setWidget($name, new sfWidgetFormInputCheckbox());
    }

    protected function processForm()
    {
        foreach ($this->form as $field) {
            // We do not check if the field is isset() in the request object
            // because checkboxes won't be sent by the browser when they
            // are not selected
            $this->processField($field);
        }
    }

    // It would be nice to hack this method to query the db just once
    // But this action is only executed but admins once in a while, not
    // a big deal
    protected function processField($field)
    {
        $name = $field->getName();

        // Search by name and scope (='element_visibility')
        // Create if it does not exist
        if (null === $setting = QubitSetting::getByNameAndScope($name, 'element_visibility')) {
            $setting = new QubitSetting();
            $setting->name = $name;
            $setting->scope = 'element_visibility';
            $setting->culture = 'en';
        }

        // It may be better to use $this->form->getValue($name)
        $value = isset($this->request[$name]) ? 1 : 0;

        $setting->setValue($value, ['sourceCulture' => true]);

        $setting->save();
    }
}
