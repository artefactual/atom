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
 * Update clipboard menus path.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0186
{
    public const VERSION = 186;
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
        $menuPaths = [
            'user/clipboardClear' => '#',
            'user/clipboardLoad' => 'clipboard/load',
            'user/clipboardSave' => 'clipboard/save',
            'user/clipboard' => 'clipboard/view',
        ];

        foreach ($menuPaths as $oldPath => $newPath) {
            $criteria = new Criteria();
            $criteria->add(QubitMenu::PATH, "{$oldPath}%", Criteria::LIKE);

            foreach (QubitMenu::get($criteria) as $menu) {
                $menu->path = str_replace($oldPath, $newPath, $menu->path);
                $menu->save();
            }
        }

        return true;
    }
}
