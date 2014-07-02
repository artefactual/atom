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
 * Add columns to fixity_recovery to allow it to be used to track recover progress
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0112
{
  const
    VERSION = 112, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    /**
     * Add type_id column, index and constraint
     */

    // Add column in which to store storage service event ID
    $sql = "ALTER TABLE fixity_recovery ADD COLUMN storage_service_event_id INTEGER NOT NULL";
    QubitPdo::modify($sql);

    // Remove existing foreign key constraint
    $sql = "ALTER TABLE fixity_recovery DROP FOREIGN KEY fixity_recovery_FK_2";
    QubitPdo::modify($sql);

    // Replace report ID foreign key with AIP ID
    $sql = "ALTER TABLE fixity_recovery DROP COLUMN fixity_report_id";
    QubitPdo::modify($sql);

    // Add AIP ID column
    $sql = "ALTER TABLE fixity_recovery ADD COLUMN aip_id INTEGER NOT NULL";
    QubitPdo::modify($sql);

    // Add new index then foreign key constraint
    $sql = "CREATE INDEX fixity_recovery_FI_2 ON fixity_recovery (aip_id)";
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `fixity_recovery`
ADD CONSTRAINT `fixity_recovery_FK_2`
FOREIGN KEY (`aip_id`)
REFERENCES `aip` (`id`);
sql;
    QubitPdo::modify($sql);

    return true;
  }
}
