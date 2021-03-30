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
 * Remove unneeded q_* tables from database.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0158
{
    public const VERSION = 158;
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
        $tables = [
            'q_acl_action',
            'q_acl_action_i18n',
            'q_acl_group',
            'q_acl_group_i18n',
            'q_acl_permission',
            'q_acl_user_group',
            'q_actor',
            'q_actor_i18n',
            'q_actor_name',
            'q_actor_name_i18n',
            'q_contact_information',
            'q_contact_information_i18n',
            'q_digital_object',
            'q_event',
            'q_event_i18n',
            'q_function',
            'q_function_i18n',
            'q_historical_event',
            'q_information_object',
            'q_information_object_i18n',
            'q_map',
            'q_map_i18n',
            'q_menu',
            'q_menu_i18n',
            'q_note',
            'q_note_i18n',
            'q_oai_harvest',
            'q_oai_repository',
            'q_object',
            'q_object_term_relation',
            'q_other_name',
            'q_other_name_i18n',
            'q_permission',
            'q_permission_scope',
            'q_physical_object',
            'q_physical_object_i18n',
            'q_place',
            'q_place_i18n',
            'q_place_map_relation',
            'q_property',
            'q_property_i18n',
            'q_relation',
            'q_repository',
            'q_repository_i18n',
            'q_rights',
            'q_rights_actor_relation',
            'q_rights_i18n',
            'q_rights_term_relation',
            'q_role',
            'q_role_permission_relation',
            'q_setting',
            'q_setting_i18n',
            'q_static_page',
            'q_static_page_i18n',
            'q_status',
            'q_system_event',
            'q_taxonomy',
            'q_taxonomy_i18n',
            'q_term',
            'q_term_i18n',
            'q_user',
            'q_user_role_relation',
        ];

        try {
            QubitPdo::prepareAndExecute('SET foreign_key_checks = 0');
            QubitPdo::prepareAndExecute('DROP TABLE '.implode(', ', $tables));
            QubitPdo::prepareAndExecute('SET foreign_key_checks = 1');
        } catch (PDOException $e) {
            // Ignore SQL error if tables don't exist, otherwise re-throw exception.
            if ('42S02' !== $e->getCode()) {
                throw $e;
            }
        }

        return true;
    }
}
