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
 * arMetadataExtractionPlugin configuration.
 *
 * @author     Johan Pieterse The Archive and Heritage Group <johan@theahg.co.za>
 */
class arMetadataExtractionPluginConfiguration extends sfPluginConfiguration
{
    protected static $settingsInitialized = false;

    public function initialize()
    {
        // Register event listeners
        $this->dispatcher->connect(
            'digital_object.post_create',
            [$this, 'extractMetadata']
        );

        // Defer settings initialization to when database is ready
        $this->dispatcher->connect(
            'context.load_factories',
            [$this, 'initializeSettings']
        );
    }

    public function initializeSettings(sfEvent $event)
    {
        if (self::$settingsInitialized) {
            return;
        }

        try {
            if (class_exists('QubitSetting') && sfContext::hasInstance()) {
                $this->addPluginSettings();
                self::$settingsInitialized = true;
            }
        } catch (Exception $e) {
            // Silent; defaults in arMetadataExtractor will apply
        }
    }

    public function extractMetadata(sfEvent $event)
    {
        try {
            $digitalObject = $event->getSubject();

            if ($digitalObject instanceof QubitDigitalObject) {
                // Determine target field for technical metadata
                $targetField = 'physicalCharacteristics';

                try {
                    if (class_exists('QubitSetting')) {
                        if ($s = QubitSetting::getByName('technical_metadata_target_field')) {
                            $v = $s->getValue(['sourceCulture' => true]);
                            if (is_string($v) && '' !== trim($v)) {
                                $targetField = trim($v);
                            }
                        }
                    }
                } catch (Exception $e) {
                    // fall back to default
                }

                // NOTE: here we **initiate the function with a variable for the field to save to**
                $extractor = new arMetadataExtractor($targetField);
                $extractor->processDigitalObject($digitalObject);
            }
        } catch (Exception $e) {
            if (sfContext::hasInstance()) {
                sfContext::getInstance()
                    ->getLogger()
                    ->err('Metadata extraction failed: '.$e->getMessage());
            }
        }
    }

    protected function addPluginSettings()
    {
        try {
            $settings = [
                'metadata_extraction_enabled' => true,
                'extract_exif' => true,
                'extract_iptc' => true,
                'extract_xmp' => true,
                'overwrite_title' => false,
                'overwrite_description' => false,
                'auto_generate_keywords' => true,
                'extract_gps_coordinates' => true,
                'add_technical_metadata' => true,
                // NEW: field where technical metadata summary is stored
                'technical_metadata_target_field' => 'physicalCharacteristics',
            ];

            foreach ($settings as $name => $default) {
                try {
                    if (null === QubitSetting::getByName($name)) {
                        $setting = new QubitSetting();
                        $setting->name = $name;
                        $setting->value = $default;
                        $setting->save();
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        } catch (Exception $e) {
            // DB not ready, ignore
        }
    }
}
