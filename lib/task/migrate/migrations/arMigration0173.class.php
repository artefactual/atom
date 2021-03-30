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
 * Add ability to set whether headers, and physical access field, are shown on
 * DACS template.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0173
{
    public const VERSION = 173;
    public const MIN_MILESTONE = 2;

    /**
     * Upgrade.
     *
     * @param mixed $configuration
     *
     * @return bool True if the upgrade succeeded, False otherwise
     */
    public function up($configuration)
    {
        $dacsVisibilitySettingNames = [
            'dacs_physical_access',
            'dacs_identity_area',
            'dacs_content_area',
            'dacs_conditions_of_access_area',
            'dacs_acquisition_area',
            'dacs_materials_area',
            'dacs_notes_area',
            'dacs_control_area',
            'dacs_access_points_area',
        ];

        foreach ($dacsVisibilitySettingNames as $settingName) {
            if (null === QubitSetting::getByName($settingName)) {
                $setting = new QubitSetting();

                $setting->setName($settingName);
                $setting->setScope('element_visibility');
                $setting->setEditable(1);
                $setting->setDeleteable(0);
                $setting->setSourceCulture('en');
                $setting->setCulture('en');
                $setting->setValue('1');

                $setting->save();
            }
        }

        return true;
    }
}
