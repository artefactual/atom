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
 * Remove nested set from some models.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0181
{
    public const VERSION = 181;
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
        // Drop parent_id, lft and rgt columns
        QubitMigrate::dropColumn(QubitFunctionObject::TABLE_NAME, 'parent_id');
        QubitMigrate::dropColumn(QubitFunctionObject::TABLE_NAME, 'lft');
        QubitMigrate::dropColumn(QubitFunctionObject::TABLE_NAME, 'rgt');
        QubitMigrate::dropColumn(QubitPhysicalObject::TABLE_NAME, 'parent_id');
        QubitMigrate::dropColumn(QubitPhysicalObject::TABLE_NAME, 'lft');
        QubitMigrate::dropColumn(QubitPhysicalObject::TABLE_NAME, 'rgt');

        // Drop lft and rgt columns
        QubitMigrate::dropColumn(QubitActor::TABLE_NAME, 'lft');
        QubitMigrate::dropColumn(QubitActor::TABLE_NAME, 'rgt');
        QubitMigrate::dropColumn(QubitTaxonomy::TABLE_NAME, 'lft');
        QubitMigrate::dropColumn(QubitTaxonomy::TABLE_NAME, 'rgt');
        QubitMigrate::dropColumn(QubitAclGroup::TABLE_NAME, 'lft');
        QubitMigrate::dropColumn(QubitAclGroup::TABLE_NAME, 'rgt');

        // Fix function_object table indexes and foreign keys.
        // Needed after removing the parent_id column to keep them
        // in sync. with the ones from a new install.
        QubitMigrate::updateIndexes([
            [
                'table' => 'function_object',
                'column' => 'type_id',
                'index' => 'function_object_FI_2',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_status_id',
                'index' => 'function_object_FI_3',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_detail_id',
                'index' => 'function_object_FI_4',
            ],
        ]);

        QubitMigrate::updateForeignKeys([
            [
                'table' => 'function_object',
                'column' => 'id',
                'refTable' => 'object',
                'constraint' => 'function_object_FK_1',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'function_object',
                'column' => 'type_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_2',
                'onDelete' => '',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_status_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_3',
                'onDelete' => '',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_detail_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_4',
                'onDelete' => '',
            ],
        ]);

        return true;
    }
}
