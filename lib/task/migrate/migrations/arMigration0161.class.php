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
 * Add index to the lft column in the information object and term tables
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0161
{
    public const VERSION = 161;
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
        // Add RGT index to the information object table
        // if it's not already added
        $sql = <<<'sql'
SHOW INDEX FROM information_object WHERE Column_name = 'rgt';
sql;

        if (0 == count(QubitPdo::fetchAll($sql))) {
            $sql = <<<'sql'
CREATE INDEX rgt ON information_object(rgt);
sql;

            QubitPdo::modify($sql);
        }

        // Add PATH index to the digital object table
        // if it's not already added
        $sql = <<<'sql'
SHOW INDEX FROM digital_object WHERE Column_name = 'path';
sql;

        if (0 == count(QubitPdo::fetchAll($sql))) {
            $sql = <<<'sql'
CREATE INDEX path ON digital_object (path);
sql;

            QubitPdo::modify($sql);
        }

        return true;
    }
}
