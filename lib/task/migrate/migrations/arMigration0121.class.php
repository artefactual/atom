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
 * Change event column to relate to object table, not information_object table
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0121
{
  const
    VERSION = 121, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Remove existing constraint and index
    $sql = "ALTER TABLE `event` DROP FOREIGN KEY `event_FK_3`";
    QubitPdo::modify($sql);

    $sql = "DROP INDEX `event_FI_3` ON `event`";
    QubitPdo::modify($sql);

    try {
      // Rename column
      $sql = "ALTER TABLE `event` CHANGE `information_object_id` `object_id` INT(11) DEFAULT NULL";
      QubitPdo::modify($sql);
    }
    catch (Exception $e)
    {
    }

    // Add new index
    $sql = "CREATE INDEX `event_FI_3` ON `event`(`object_id`)";
    QubitPdo::modify($sql);

    // Add new constraint
    $sql = <<<sql

ALTER TABLE `event`
  ADD CONSTRAINT `event_FK_3`
  FOREIGN KEY (`object_id`)
  REFERENCES `object` (`id`)
  ON DELETE CASCADE;

sql;

    QubitPdo::modify($sql);

    return true;
  }
}
