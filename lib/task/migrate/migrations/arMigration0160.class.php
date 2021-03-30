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
 * Change menu paths for browsing places and subjects so a redirect isn't required.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0160
{
    public const VERSION = 160;
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
        // Update path of places menu item
        $criteria = new Criteria();
        $criteria->add(QubitMenu::NAME, 'browsePlaces');

        $menu = QubitMenu::getOne($criteria);

        if (null !== $menu) {
            $menu->path = 'taxonomy/index?id=42';
            $menu->save();
        }

        // Update path of subjects menu item
        $criteria = new Criteria();
        $criteria->add(QubitMenu::NAME, 'browseSubjects');

        $menu = QubitMenu::getOne($criteria);

        if (null !== $menu) {
            $menu->path = 'taxonomy/index?id=35';
            $menu->save();
        }

        return true;
    }
}
