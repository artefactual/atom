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
 * Add new term to the levels of description taxonomy
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0169
{
  const
    VERSION = 169, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Add object_id to digital_object with fk relationship
    $sql = <<<sql

ALTER TABLE `atom`.`digital_object`
DROP FOREIGN KEY `digital_object_FK_2`;
ALTER TABLE `atom`.`digital_object`
CHANGE COLUMN `information_object_id` `object_id` INT(11) NULL DEFAULT NULL ;
ALTER TABLE `atom`.`digital_object`
ADD CONSTRAINT `digital_object_FK_2`
  FOREIGN KEY (`object_id`)
  REFERENCES `atom`.`information_object` (`id`);

sql;

    QubitPdo::modify($sql);

    return true;
  }
}
