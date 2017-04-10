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
 * Add relation between job status IDs and terms.
*
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0152
{
  const
    VERSION = 152, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Change job column so NULL is allowed
    $sql = "ALTER TABLE `job` MODIFY `status_id` INT(11)";
    QubitPdo::modify($sql);

    // Add constraint so status_id must be associated with a term ID
    $sql = "ALTER TABLE `job` ADD CONSTRAINT `job_FK_4` FOREIGN KEY (`status_id`) REFERENCES `term` (`id`) ON DELETE SET NULL;";
    QubitPdo::modify($sql);

    // Add index to status_id
    $sql = "CREATE INDEX `job_FI_4` ON `job` (`status_id`)";
    QubitPdo::modify($sql);

    return true;
  }
}
