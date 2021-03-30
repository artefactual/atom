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

/*
 * Add element visibility settings for DO metadata improvements
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0187
{
    public const VERSION = 187;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        $SCOPE = 'element_visibility';
        $VALUE = 1;
        $settingNames = [
            // Master file
            'digital_object_permissions',

            // Reference copy
            'digital_object_reference_file_name',
            'digital_object_reference_media_type',
            'digital_object_reference_mime_type',
            'digital_object_reference_file_size',
            'digital_object_reference_uploaded',
            'digital_object_reference_permissions',

            // Thumbnail copy
            'digital_object_thumbnail_file_name',
            'digital_object_thumbnail_media_type',
            'digital_object_thumbnail_mime_type',
            'digital_object_thumbnail_file_size',
            'digital_object_thumbnail_uploaded',
            'digital_object_thumbnail_permissions',

            // Original file in preservation system
            'digital_object_preservation_system_original_file_name',
            'digital_object_preservation_system_original_format_name',
            'digital_object_preservation_system_original_format_version',
            'digital_object_preservation_system_original_format_registry_key',
            'digital_object_preservation_system_original_format_registry_name',
            'digital_object_preservation_system_original_file_size',
            'digital_object_preservation_system_original_ingested',
            'digital_object_preservation_system_original_permissions',

            // Preservation copy in preservation system
            'digital_object_preservation_system_preservation_file_name',
            'digital_object_preservation_system_preservation_file_size',
            'digital_object_preservation_system_preservation_normalized',
            'digital_object_preservation_system_preservation_permissions',
        ];

        foreach ($settingNames as $settingName) {
            if (null === $setting = QubitSetting::getByNameAndScope($settingName, $SCOPE)) {
                $setting = new QubitSetting();
                $setting->name = $settingName;
                $setting->setValue($VALUE, ['sourceCulture' => true]);
                $setting->scope = $SCOPE;
                $setting->culture = 'en';
                $setting->save();
            }
        }

        return true;
    }
}
