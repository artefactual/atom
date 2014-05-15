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
 * Create AIP table, add AIP types taxonomy and terms
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0109
{
  const
    VERSION = 109, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Create AIP table
    $sql = <<<sql

CREATE TABLE `granted_right`
(
	`rights_id` INTEGER  NOT NULL,
	`act_id` INTEGER,
	`restriction` TINYINT default 1,
	`start_date` DATE,
	`end_date` DATE,
	`notes` TEXT,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `granted_right_FI_1` (`rights_id`),
	CONSTRAINT `granted_right_FK_1`
		FOREIGN KEY (`rights_id`)
		REFERENCES `rights` (`id`)
		ON DELETE CASCADE,
	INDEX `granted_right_FI_2` (`act_id`),
	CONSTRAINT `granted_right_FK_2`
		FOREIGN KEY (`act_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

INSERT IGNORE INTO granted_right (rights_id, act_id, restriction) SELECT id, act_id, restriction from rights;

ALTER TABLE rights DROP restriction;
ALTER TABLE rights DROP FOREIGN KEY rights_FK_3;
DROP INDEX rights_FI_3 ON rights;
ALTER TABLE rights DROP act_id;

sql;

  /*
    CHANGES SUMMARY:
    add `granted_right` table

    move act_id and restriction column values from `rights` to `granted_right`
    drop act_id and restriction from `rights` (drop `rights`.act_id index???)

   */

    QubitPdo::modify($sql);

    return true;
  }
}
