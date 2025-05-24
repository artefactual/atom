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

class SettingsDiacriticsAction extends SettingsEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'diacritics',
        'mappings',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Diacritics settings saved.');

        $this->settingDefaults = [
            'diacritics' => 0,
        ];
    }

    public function processForm()
    {
        foreach ($this->form as $field) {
            $this->processField($field);
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'diacritics':
                $this->form->setDefault($name, $this->settingDefaults[$name]);
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => [0 => $this->i18n->__('Disabled'), 1 => $this->i18n->__('Enabled')]], ['class' => 'radio']));
                $this->form->setValidator($name, new sfValidatorChoice(['choices' => [1, 0]]));

                break;

            case 'mappings':
                $this->form->setWidget($name, new sfWidgetFormInputFile([], ['accept' => '.yml,.yaml']));
                $this->form->setValidator($name, new sfValidatorFile(['mime_types' => ['text/plain']]));

                break;
        }
    }

    protected function processField($field)
    {
        switch ($name = $field->getName()) {
            case 'diacritics':
                parent::processField($field);

                break;

            case 'mappings':
                $file = $this->form->getValue('mappings');

                $diacriticsMappingPath = sfConfig::get('sf_upload_dir').DIRECTORY_SEPARATOR.'diacritics_mapping.yml';

                if (null !== $file) {
                    try {
                        sfYaml::load($file->getTempName());

                        if (!move_uploaded_file($file->getTempName(), $diacriticsMappingPath)) {
                            $this->getUser()->setFlash('error', $this->context->i18n->__('Unable to upload diacritics mapping yaml file.'));
                            unset($this->updateMessage);

                            return;
                        }
                    } catch (Exception $e) {
                        QubitSetting::findAndSave('diacritics', 0, ['sourceCulture' => true]);
                        unlink($diacriticsMappingPath);
                        $this->getUser()->setFlash('error', $this->context->i18n->__('Unable to upload diacritics mapping yaml file.'));
                        unset($this->updateMessage);
                    }
                } else {
                    // Reset diacritics settings when uploading yaml fails
                    QubitSetting::findAndSave('diacritics', 0, ['sourceCulture' => true]);
                    unlink($diacriticsMappingPath);
                    $this->getUser()->setFlash('error', $this->context->i18n->__('Unable to upload diacritics mapping yaml file.'));
                    unset($this->updateMessage);
                }

                break;
        }
    }
}
