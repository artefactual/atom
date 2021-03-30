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
 * Increase size of culture and source culture columns.
 *
 * Increase size of culture and source culture columns in order to accommodate
 * non-standard country codes.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0172
{
    public const VERSION = 172;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        $i18nTables = [
            'accession_i18n',
            'acl_group_i18n',
            'actor_i18n',
            'contact_information_i18n',
            'deaccession_i18n',
            'event_i18n',
            'function_i18n',
            'information_object_i18n',
            'menu_i18n',
            'note_i18n',
            'other_name_i18n',
            'physical_object_i18n',
            'property_i18n',
            'relation_i18n',
            'repository_i18n',
            'rights_i18n',
            'setting_i18n',
            'static_page_i18n',
            'taxonomy_i18n',
            'term_i18n',
        ];

        // Increase size of culture-related columns
        foreach ($i18nTables as $table) {
            // Increase size of i18n table's culture column
            $sql = 'ALTER TABLE `%s` CHANGE `culture` `culture` VARCHAR(16)';
            QubitPdo::modify(sprintf($sql, $table));

            // Increase size of base table's source_culture column
            $baseTable = str_replace('_i18n', '', $table);
            $sql = 'ALTER TABLE `%s` CHANGE `source_culture` `source_culture` VARCHAR(16)';
            QubitPdo::modify(sprintf($sql, $baseTable));
        }

        return true;
    }
}
