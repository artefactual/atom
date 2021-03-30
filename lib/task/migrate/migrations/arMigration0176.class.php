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
 * Rename QubitFunction to QubitFunctionObject to avoid the `function`
 * table name, as that word became a reserved word in MySQL 8.0.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0176
{
    public const VERSION = 176;
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
        // Update `class_name` in `object` table
        $sql = 'UPDATE object SET class_name=:new WHERE class_name=:old;';
        QubitPdo::modify($sql, [
            ':new' => 'QubitFunctionObject',
            ':old' => 'QubitFunction',
        ]);

        // Rename tables (backquotes needed to pass the reserved word issue)
        $sql = 'RENAME TABLE `function` TO function_object, ';
        $sql .= 'function_i18n TO function_object_i18n;';
        QubitPdo::modify($sql);

        // Rename indexes to match a new install
        $indexes = [
            'type_id' => 'function_object_FI_2',
            'parent_id' => 'function_object_FI_3',
            'description_status_id' => 'function_object_FI_4',
            'description_detail_id' => 'function_object_FI_5',
        ];

        foreach ($indexes as $columnName => $indexName) {
            // Get actual index name
            $sql = 'SHOW INDEX FROM function_object WHERE Column_name=:column_name;';
            $result = QubitPdo::fetchOne($sql, [':column_name' => $columnName]);

            // Stop if the index is missing
            if (!$result || !$result->Key_name) {
                throw new Exception(sprintf(
                    "Could not find index for '%s' column on 'function_object' table.",
                    $columnName
                ));
            }

            // Skip if the index already has the expected name
            if ($result->Key_name == $indexName) {
                continue;
            }

            $sql = 'ALTER TABLE function_object RENAME INDEX %s TO %s;';
            QubitPdo::modify(sprintf($sql, $result->Key_name, $indexName));
        }

        // Recreate foreign keys to match a new install
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
                'column' => 'parent_id',
                'refTable' => 'function_object',
                'constraint' => 'function_object_FK_3',
                'onDelete' => '',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_status_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_4',
                'onDelete' => '',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_detail_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_5',
                'onDelete' => '',
            ],
            [
                'table' => 'function_object_i18n',
                'column' => 'id',
                'refTable' => 'function_object',
                'constraint' => 'function_object_i18n_FK_1',
                'onDelete' => 'ON DELETE CASCADE',
            ],
        ]);

        return true;
    }
}
