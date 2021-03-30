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
 * Change Digital Object fk relationship.
 *
 * Historically, digital objects were related to the information object table
 * directly. This change alters the FK relationship such that DO's can be
 * linked to any object type via fk linking to the object table.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0169
{
    public const VERSION = 169;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add object_id to digital_object with fk relationship.
        // Use separated statements, otherwise errors are not raised.
        $sql = 'ALTER TABLE digital_object DROP FOREIGN KEY digital_object_FK_2;';
        QubitPdo::modify($sql);

        $sql = 'ALTER TABLE digital_object CHANGE COLUMN information_object_id ';
        $sql .= 'object_id INT(11) NULL DEFAULT NULL;';
        QubitPdo::modify($sql);

        $sql = 'ALTER TABLE digital_object ADD CONSTRAINT digital_object_FK_2 ';
        $sql .= 'FOREIGN KEY (object_id) REFERENCES object (id);';
        QubitPdo::modify($sql);

        return true;
    }
}
