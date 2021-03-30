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
 * Allow null values on lft and rgt columns.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0183
{
    public const VERSION = 183;
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
        $alterTables = [
            'ALTER TABLE `information_object` MODIFY `lft` INTEGER, MODIFY `rgt` INTEGER;',
            'ALTER TABLE `menu` MODIFY `lft` INTEGER, MODIFY `rgt` INTEGER;',
            'ALTER TABLE `term` MODIFY `lft` INTEGER, MODIFY `rgt` INTEGER;',
        ];

        foreach ($alterTables as $sql) {
            QubitPdo::modify($sql);
        }

        return true;
    }
}
