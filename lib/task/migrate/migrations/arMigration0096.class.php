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
 * Activate Dominion theme
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0096
{
  const
    VERSION = 96, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Add table keymap
    $sql = <<<sql

CREATE TABLE `access_log`
(
        `id` INTEGER  NOT NULL AUTO_INCREMENT,
        `object_id` INTEGER  NOT NULL,
        `access_date` DATETIME,
        PRIMARY KEY (`id`),
        KEY `1`(`access_date`, `object_id`),
        INDEX `access_log_FI_1` (`object_id`),
        CONSTRAINT `access_log_FK_1`
                FOREIGN KEY (`object_id`)
                REFERENCES `object` (`id`)
                ON DELETE CASCADE
)Engine=InnoDB;

sql;
    QubitPdo::modify($sql);

    return true;
  }
}
