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
 * Modify database for large PDF text support
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0193
{
    public const VERSION = 193;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        $sql = 'ALTER TABLE `property_i18n` MODIFY `value` MEDIUMTEXT;';
        QubitPdo::modify($sql);

        return true;
    }
}
