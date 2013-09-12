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
 * Update foreing key in access_log table
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0101
{
  const
    VERSION = 101, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $sql = <<<sql

ALTER TABLE `access_log`
DROP FOREIGN KEY `access_log_FK_1`;

sql;

    QubitPdo::modify($sql);

        $sql = <<<sql

ALTER TABLE `access_log`
ADD CONSTRAINT `access_log_FK_1`
FOREIGN KEY (`object_id`)
REFERENCES `object` (`id`)
ON DELETE CASCADE
ON UPDATE RESTRICT;

sql;

    QubitPdo::modify($sql);

    return true;
  }
}
