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
 * Create premis object table
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0143
{
  const
    VERSION = 143, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $sql = <<<sql
CREATE TABLE IF NOT EXISTS `premis_object`
(
  `id` INTEGER  NOT NULL,
  `information_object_id` INTEGER,
  `puid` VARCHAR(255),
  `filename` VARCHAR(1024),
  `last_modified` DATETIME,
  `date_ingested` DATE,
  `size` INTEGER,
  `mime_type` VARCHAR(255),
  PRIMARY KEY (`id`),
  CONSTRAINT `premis_object_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `object` (`id`)
    ON DELETE CASCADE,
  INDEX `premis_object_FI_2` (`information_object_id`),
  CONSTRAINT `premis_object_FK_2`
    FOREIGN KEY (`information_object_id`)
    REFERENCES `information_object` (`id`)
)Engine=InnoDB;
sql;

    QubitPdo::modify($sql);

    return true;
  }
}
