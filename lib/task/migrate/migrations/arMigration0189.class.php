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
 * Allow deletion of privacy menu item from quick links.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0189
{
    public const VERSION = 189;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Get menu locking configuration
        $setting = QubitSetting::getByName('menu_locking_info');

        if (!empty($setting)) {
            $lockedMenus = unserialize($setting->value);

            // Unset privacy menu if it's set
            if (($key = array_search('privacy', $lockedMenus['byName'])) !== false) {
                unset($lockedMenus['byName'][$key]);
            }

            $setting->value = serialize($lockedMenus);
            $setting->save();
        }

        return true;
    }
}
