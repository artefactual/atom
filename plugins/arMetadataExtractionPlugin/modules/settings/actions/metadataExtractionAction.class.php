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
 * Metadata extraction settings action
 *
 * @package    arMetadataExtractionPlugin
 * @subpackage admin
 * @author     Johan Pieterse <pieterse.johan3@gmail.com>
 */
class MetadataExtractionSettingsAction extends DefaultEditAction
{
    public static function getTitle($context)
    {
        return __('Metadata extraction settings');
    }

    public static function getGroup()
    {
        return __('Admin');
    }

    public static function getDescription($context)
    {
        return __('Configure automatic metadata extraction from uploaded digital objects');
    }

    public function earlyExecute()
    {
        parent::earlyExecute();
        
        $this->updateMessage = __('Settings saved successfully');
    }

    public function execute($request)
    {
        parent::execute($request);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();
                
                $this->getUser()->setFlash('notice', $this->updateMessage);
                $this->redirect(['module' => 'settings', 'action' => 'metadataExtraction']);
            }
        }
    }

    protected function buildForm()
    {
        $form = new sfForm();

        // Main enable/disable switch
        $form->setWidget('metadata_extraction_enabled', new sfWidgetFormSelectRadio(
            ['choices' => [
                1 => __('Enabled'),
                0 => __('Disabled')
            ]],
            ['class' => 'radio']
        ));
        $form->setValidator('metadata_extraction_enabled', new sfValidatorChoice(
            ['choices' => [0, 1]]
        ));
        $form->setDefault('metadata_extraction_enabled', 
            QubitSetting::getByName('metadata_extraction_enabled') ? 
            QubitSetting::getByName('metadata_extraction_enabled')->getValue(['sourceCulture' => true]) : 1
        );

        // Metadata types to extract
        $form->setWidget('extract_exif', new sfWidgetFormInputCheckbox());
        $form->setValidator('extract_exif', new sfValidatorBoolean());
        $form->setDefault('extract_exif',
            QubitSetting::getByName('extract_exif') ?
            QubitSetting::getByName('extract_exif')->getValue(['sourceCulture' => true]) : 1
        );

        $form->setWidget('extract_iptc', new sfWidgetFormInputCheckbox());
        $form->setValidator('extract_iptc', new sfValidatorBoolean());
        $form->setDefault('extract_iptc',
            QubitSetting::getByName('extract_iptc') ?
            QubitSetting::getByName('extract_iptc')->getValue(['sourceCulture' => true]) : 1
        );

        $form->setWidget('extract_xmp', new sfWidgetFormInputCheckbox());
        $form->setValidator('extract_xmp', new sfValidatorBoolean());
        $form->setDefault('extract_xmp',
            QubitSetting::getByName('extract_xmp') ?
            QubitSetting::getByName('extract_xmp')->getValue(['sourceCulture' => true]) : 1
        );

        // Overwrite settings
        $form->setWidget('overwrite_title', new sfWidgetFormInputCheckbox());
        $form->setValidator('overwrite_title', new sfValidatorBoolean());
        $form->setDefault('overwrite_title',
            QubitSetting::getByName('overwrite_title') ?
            QubitSetting::getByName('overwrite_title')->getValue(['sourceCulture' => true]) : 0
        );

        $form->setWidget('overwrite_description', new sfWidgetFormInputCheckbox());
        $form->setValidator('overwrite_description', new sfValidatorBoolean());
        $form->setDefault('overwrite_description',
            QubitSetting::getByName('overwrite_description') ?
            QubitSetting::getByName('overwrite_description')->getValue(['sourceCulture' => true]) : 0
        );

        // Additional features
        $form->setWidget('auto_generate_keywords', new sfWidgetFormInputCheckbox());
        $form->setValidator('auto_generate_keywords', new sfValidatorBoolean());
        $form->setDefault('auto_generate_keywords',
            QubitSetting::getByName('auto_generate_keywords') ?
            QubitSetting::getByName('auto_generate_keywords')->getValue(['sourceCulture' => true]) : 1
        );

        $form->setWidget('extract_gps_coordinates', new sfWidgetFormInputCheckbox());
        $form->setValidator('extract_gps_coordinates', new sfValidatorBoolean());
        $form->setDefault('extract_gps_coordinates',
            QubitSetting::getByName('extract_gps_coordinates') ?
            QubitSetting::getByName('extract_gps_coordinates')->getValue(['sourceCulture' => true]) : 1
        );

        $form->setWidget('add_technical_metadata', new sfWidgetFormInputCheckbox());
        $form->setValidator('add_technical_metadata', new sfValidatorBoolean());
        $form->setDefault('add_technical_metadata',
            QubitSetting::getByName('add_technical_metadata') ?
            QubitSetting::getByName('add_technical_metadata')->getValue(['sourceCulture' => true]) : 1
        );

        return $form;
    }

    protected function processForm()
    {
        $settings = [
            'metadata_extraction_enabled',
            'extract_exif',
            'extract_iptc',
            'extract_xmp',
            'overwrite_title',
            'overwrite_description',
            'auto_generate_keywords',
            'extract_gps_coordinates',
            'add_technical_metadata'
        ];

        foreach ($settings as $name) {
            if (null !== $setting = QubitSetting::getByName($name)) {
                $setting->setValue($this->form->getValue($name), ['sourceCulture' => true]);
            } else {
                $setting = new QubitSetting();
                $setting->name = $name;
                $setting->value = $this->form->getValue($name);
            }
            $setting->save();
        }
    }
}
