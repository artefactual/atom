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

class SettingsHeaderAction extends SettingsEditAction
{
    public $uploadsDir;

    public static $NAMES = [
        'logo',
        'restore_logo',
        'favicon',
        'restore_favicon',
        'header_background_colour',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        $this->updateMessage = $this->i18n->__('Header customizations settings saved.');

        $this->settingDefaults = [
            'logo' => '0',
            'header_background_colour' => '#212529',
            'favicon' => '0',
            'restore_logo' => '0',
            'restore_favicon' => '0',
        ];

        $this->uploadsDir = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'uploads';

        if ($this->getRequest()->hasParameter('restore_logo')) {
            $this->restoreDefaultLogo();
        }

        if ($this->getRequest()->hasParameter('restore_favicon')) {
            $this->restoreDefaultFavicon();
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'logo':
                $this->form->setWidget($name, new sfWidgetFormInputFile([], ['accept' => '.png']));
                $this->form->setValidator($name, new sfValidatorFile(['mime_types' => ['image/png']]));

                break;

            case 'favicon':
                $this->form->setWidget($name, new sfWidgetFormInputFile([], ['accept' => '.ico']));
                $this->form->setValidator($name, new sfValidatorFile(['mime_types' => ['image/x-icon', 'image/vnd.microsoft.icon']]));

                break;

            case 'restore_logo':
            case 'restore_favicon':
                $options = [$this->i18n->__('No'), $this->i18n->__('Yes')];
                $this->form->setWidget($name, new sfWidgetFormSelectRadio(['choices' => $options], ['class' => 'radio']));
                $this->form->setValidator($name, new sfValidatorInteger(['required' => false]));

                break;

            case 'header_background_colour':
                $this->form->setWidget('header_background_colour', new sfWidgetFormInput(['type' => 'color']));
                $this->form->setValidator('header_background_colour', new sfValidatorRegex(
                    ['pattern' => '/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                    ['invalid' => $this->context->i18n->__('Only hexadecimal color value')]
                ));
                $this->form->getWidgetSchema()->{$name}->setLabel($this->i18n->__('Background colour'));

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'logo':
                $logoFile = $this->form->getValue('logo');

                $logoImgPath = $this->uploadsDir.DIRECTORY_SEPARATOR.'logo.png';

                if (null !== $logoFile) {
                    $logoFile->save($logoImgPath);
                }

                break;

            case 'favicon':
                $faviconFile = $this->form->getValue('favicon');

                $faviconImgPath = $this->uploadsDir.DIRECTORY_SEPARATOR.'favicon.ico';

                if (null !== $faviconFile) {
                    $faviconFile->save($faviconImgPath);
                }

                break;

            case 'header_background_colour':
                $colour = $this->form->getValue('header_background_colour');
                QubitSetting::findAndSave('header_background_colour', $colour, ['sourceCulture' => true]);

                break;

            case 'restore_logo':
                if (1 == $field->getValue()) {
                    $this->restoreDefaultLogo();
                }

                break;

            case 'restore_favicon':
                if (1 == $field->getValue()) {
                    $this->restoreDefaultFavicon();
                }

                break;
        }
    }

    protected function restoreDefaultLogo()
    {
        $logoImgPath = $this->uploadsDir.DIRECTORY_SEPARATOR.'logo.png';
        $defaultAtoMLogoPath = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'arDominionB5Plugin'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'default_atom_logo.png';

        if (file_exists($defaultAtoMLogoPath)) {
            copy($defaultAtoMLogoPath, $logoImgPath);
        } else {
            $this->updateMessage = $this->i18n->__('Default logo not found.');
        }
    }

    protected function restoreDefaultFavicon()
    {
        $faviconImgPath = $this->uploadsDir.DIRECTORY_SEPARATOR.'favicon.ico';
        $defaultAtoMFaviconPath = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'default_atom_favicon.ico';

        if (file_exists($defaultAtoMFaviconPath)) {
            copy($defaultAtoMFaviconPath, $faviconImgPath);
        } else {
            $this->updateMessage = $this->i18n->__('Default favicon not found.');
        }
    }
}
