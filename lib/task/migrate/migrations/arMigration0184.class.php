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
 * Update info related to menu locking
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0184
{
    public const VERSION = 184;
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
        $lockedMenus = [
            'byName' => [
                'accessions',
                'browseInstitution',
                'clipboard',
                'globalReplace',
                'groups',
                'importSkos',
                'jobs',
                'login',
                'logout',
                'myProfile',
                'plugins',
                'privacy',
                'settings',
                'staticPagesMenu',
                'taxonomies',
                'users',
            ],
            'byId' => [
                QubitMenu::ROOT_ID,
                QubitMenu::BROWSE_ID,
                QubitMenu::IMPORT_ID,
                QubitMenu::MAIN_MENU_ID,
                QubitMenu::MANAGE_ID,
                QubitMenu::QUICK_LINKS_ID,
                QubitMenu::ADD_EDIT_ID,
                QubitMenu::ADMIN_ID,
            ],
        ];

        $setting = QubitSetting::getByName('menu_locking_info');
        if (isset($setting)) {
            $setting->value = serialize($lockedMenus);
            $setting->save();
        }

        return true;
    }
}
