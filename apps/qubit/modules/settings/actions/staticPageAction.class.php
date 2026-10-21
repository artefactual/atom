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

class SettingsStaticPageAction extends SettingsEditAction
{
    public $staticDir;
    public $imgPath;
    public $staticImgs;

    public static $NAMES = [
        'staticImgUpload',
    ];

    public function earlyExecute()
    {
        parent::earlyExecute();

        if (str_starts_with(sfConfig::get('app_static_path'), 'uploads')) {
            $this->staticDir = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.sfConfig::get('app_static_path');
        } else {
            $this->staticDir = sfConfig::get('app_static_path');
        }

        $this->setVar('staticImgs', glob($this->staticDir.DIRECTORY_SEPARATOR.'*.*'));
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'staticImgUpload':
                $this->form->setWidget($name, new sfWidgetFormInputFile([], ['accept' => '.png,.jpg,.jpeg,.svg,.webp']));
                $this->form->setValidator($name, new sfValidatorFile(['mime_types' => ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp']]));

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'staticImgUpload':
                $staticImgFile = $this->form->getValue('staticImgUpload');

                if (null !== $staticImgFile) {
                    $originalName = pathinfo($staticImgFile->getOriginalName(), PATHINFO_FILENAME);
                    $extension = pathinfo($staticImgFile->getOriginalName(), PATHINFO_EXTENSION);
                    $checkName = $originalName.'.'.$extension;
                    $dupCounter = 0;

                    while (file_exists($this->staticDir.DIRECTORY_SEPARATOR.$checkName)) {
                        ++$dupCounter;
                        $checkName = $originalName.'_'.$dupCounter.'.'.$extension;
                    }

                    0 == $dupCounter ? $fileName = $originalName.'.'.$extension : $fileName = $originalName.'_'.$dupCounter.'.'.$extension;

                    $imgPath = $this->staticDir.DIRECTORY_SEPARATOR.$fileName;
                    $staticImgFile->save($imgPath);
                    $this->imgPath = $imgPath;
                    $this->updateMessage = $this->i18n->__('Static page image uploaded to: %1%', ['%1%' => $this->imgPath]);
                }

                break;
        }
    }
}
