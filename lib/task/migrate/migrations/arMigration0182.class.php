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
 * Add missing on delete to foreign keys.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0182
{
    public const VERSION = 182;
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
        QubitMigrate::updateForeignKeys([
            [
                'table' => 'actor',
                'column' => 'parent_id',
                'refTable' => 'actor',
                'constraint' => 'actor_FK_5',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'digital_object',
                'column' => 'parent_id',
                'refTable' => 'digital_object',
                'constraint' => 'digital_object_FK_5',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'event',
                'column' => 'actor_id',
                'refTable' => 'actor',
                'constraint' => 'event_FK_4',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'function_object',
                'column' => 'type_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_2',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_status_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_3',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'function_object',
                'column' => 'description_detail_id',
                'refTable' => 'term',
                'constraint' => 'function_object_FK_4',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'information_object',
                'column' => 'collection_type_id',
                'refTable' => 'term',
                'constraint' => 'information_object_FK_3',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'information_object',
                'column' => 'repository_id',
                'refTable' => 'repository',
                'constraint' => 'information_object_FK_4',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'information_object',
                'column' => 'parent_id',
                'refTable' => 'information_object',
                'constraint' => 'information_object_FK_5',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'note',
                'column' => 'user_id',
                'refTable' => 'user',
                'constraint' => 'note_FK_3',
                'onDelete' => 'ON DELETE SET NULL',
            ],
            [
                'table' => 'premis_object',
                'column' => 'information_object_id',
                'refTable' => 'information_object',
                'constraint' => 'premis_object_FK_2',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'relation',
                'column' => 'subject_id',
                'refTable' => 'object',
                'constraint' => 'relation_FK_2',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'relation',
                'column' => 'object_id',
                'refTable' => 'object',
                'constraint' => 'relation_FK_3',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'relation',
                'column' => 'type_id',
                'refTable' => 'term',
                'constraint' => 'relation_FK_4',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'taxonomy',
                'column' => 'parent_id',
                'refTable' => 'taxonomy',
                'constraint' => 'taxonomy_FK_2',
                'onDelete' => 'ON DELETE CASCADE',
            ],
            [
                'table' => 'term',
                'column' => 'parent_id',
                'refTable' => 'term',
                'constraint' => 'term_FK_3',
                'onDelete' => 'ON DELETE CASCADE',
            ],
        ]);

        return true;
    }
}
