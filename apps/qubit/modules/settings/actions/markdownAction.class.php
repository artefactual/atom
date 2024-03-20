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

class SettingsMarkdownAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = ['enabled'];

    public function execute($request)
    {
        parent::execute($request);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();

                QubitCache::getInstance()->removePattern('settings:i18n:*');

                $this->getUser()->setFlash('notice', $this->i18n->__('Markdown settings saved.'));

                $this->redirect(['module' => 'settings', 'action' => 'markdown']);
            }
        }
    }

    protected function earlyExecute()
    {
        $this->i18n = sfContext::getInstance()->i18n;
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'enabled':
                $default = 1;
                if (null !== $this->settingEnabled = QubitSetting::getByName('markdown_enabled')) {
                    $default = $this->settingEnabled->getValue(['sourceCulture' => true]);
                }

                $this->form->setDefault($name, $default);
                $this->form->setValidator($name, new sfValidatorInteger(['required' => false]));
                $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'enabled':
                if (null === $this->settingEnabled) {
                    $this->settingEnabled = new QubitSetting();
                    $this->settingEnabled->name = 'markdown_enabled';
                    $this->settingEnabled->sourceCulture = 'en';
                }

                $this->settingEnabled->setValue($field->getValue(), ['culture' => 'en']);
                $this->settingEnabled->save();

                break;
        }
    }
}
