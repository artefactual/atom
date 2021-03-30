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
 * Transform database tables and columns character set to `utf8mb4`
 * and collation to `utf8mb4_0900_ai_ci` for MySQL 8.0 and higher.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0178
{
    public const VERSION = 178;
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
        // Change charset and collation:
        // - Each statement has to be run independently, otherwise errors are
        //   ignored and the migration task continues.
        // - Use full SQL instead of generate it from the database to avoid issues
        //   with unknown tables/columns.
        // - Change default charset and collation per table.
        // - Use MODIFY over each column instead of a single CONVERT because the
        //   later transforms TEXT columns to MEDIUMTEXT.
        // - Reduce DO path index size for the extra byte.
        $alterTables = [
            'ALTER TABLE `access_log` %1$s;',

            'ALTER TABLE `actor` %1$s, '
            .'MODIFY `corporate_body_identifiers` VARCHAR(1024) %1$s, '
            .'MODIFY `description_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `source_standard` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `actor_i18n` %1$s, '
            .'MODIFY `authorized_form_of_name` VARCHAR(1024) %1$s, '
            .'MODIFY `dates_of_existence` VARCHAR(1024) %1$s, '
            .'MODIFY `history` TEXT %1$s, '
            .'MODIFY `places` TEXT %1$s, '
            .'MODIFY `legal_status` TEXT %1$s, '
            .'MODIFY `functions` TEXT %1$s, '
            .'MODIFY `mandates` TEXT %1$s, '
            .'MODIFY `internal_structures` TEXT %1$s, '
            .'MODIFY `general_context` TEXT %1$s, '
            .'MODIFY `institution_responsible_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `rules` TEXT %1$s, '
            .'MODIFY `sources` TEXT %1$s, '
            .'MODIFY `revision_history` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `aip` %1$s, '
            .'MODIFY `uuid` VARCHAR(36) %1$s, '
            .'MODIFY `filename` VARCHAR(1024) %1$s;',

            'ALTER TABLE `audit_log` %1$s, '
            .'MODIFY `user_name` VARCHAR(255) %1$s;',

            'ALTER TABLE `job` %1$s, '
            .'MODIFY `name` VARCHAR(255) %1$s, '
            .'MODIFY `download_path` TEXT %1$s, '
            .'MODIFY `output` TEXT %1$s;',

            'ALTER TABLE `clipboard_save` %1$s, '
            .'MODIFY `password` VARCHAR(255) %1$s;',

            'ALTER TABLE `clipboard_save_item` %1$s, '
            .'MODIFY `item_class_name` VARCHAR(255) %1$s, '
            .'MODIFY `slug` VARCHAR(255) %1$s;',

            'ALTER TABLE `contact_information` %1$s, '
            .'MODIFY `contact_person` VARCHAR(1024) %1$s, '
            .'MODIFY `street_address` TEXT %1$s, '
            .'MODIFY `website` VARCHAR(1024) %1$s, '
            .'MODIFY `email` VARCHAR(255) %1$s, '
            .'MODIFY `telephone` VARCHAR(255) %1$s, '
            .'MODIFY `fax` VARCHAR(255) %1$s, '
            .'MODIFY `postal_code` VARCHAR(255) %1$s, '
            .'MODIFY `country_code` VARCHAR(255) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `contact_information_i18n` %1$s, '
            .'MODIFY `contact_type` VARCHAR(1024) %1$s, '
            .'MODIFY `city` VARCHAR(1024) %1$s, '
            .'MODIFY `region` VARCHAR(1024) %1$s, '
            .'MODIFY `note` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `digital_object` %1$s, '
            .'DROP INDEX `path`, ADD INDEX `path`(`path`(768)), '
            .'MODIFY `mime_type` VARCHAR(255) %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s NOT NULL, '
            .'MODIFY `path` VARCHAR(1024) %1$s NOT NULL, '
            .'MODIFY `checksum` VARCHAR(255) %1$s, '
            .'MODIFY `checksum_type` VARCHAR(50) %1$s;',

            'ALTER TABLE `event` %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `event_i18n` %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `date` VARCHAR(1024) %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `function_object` %1$s, '
            .'MODIFY `description_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `source_standard` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `function_object_i18n` %1$s, '
            .'MODIFY `authorized_form_of_name` VARCHAR(1024) %1$s, '
            .'MODIFY `classification` VARCHAR(1024) %1$s, '
            .'MODIFY `dates` VARCHAR(1024) %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `history` TEXT %1$s, '
            .'MODIFY `legislation` TEXT %1$s, '
            .'MODIFY `institution_identifier` TEXT %1$s, '
            .'MODIFY `revision_history` TEXT %1$s, '
            .'MODIFY `rules` TEXT %1$s, '
            .'MODIFY `sources` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `information_object` %1$s, '
            .'MODIFY `identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `description_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `source_standard` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `information_object_i18n` %1$s, '
            .'MODIFY `title` VARCHAR(1024) %1$s, '
            .'MODIFY `alternate_title` VARCHAR(1024) %1$s, '
            .'MODIFY `edition` VARCHAR(1024) %1$s, '
            .'MODIFY `extent_and_medium` TEXT %1$s, '
            .'MODIFY `archival_history` TEXT %1$s, '
            .'MODIFY `acquisition` TEXT %1$s, '
            .'MODIFY `scope_and_content` TEXT %1$s, '
            .'MODIFY `appraisal` TEXT %1$s, '
            .'MODIFY `accruals` TEXT %1$s, '
            .'MODIFY `arrangement` TEXT %1$s, '
            .'MODIFY `access_conditions` TEXT %1$s, '
            .'MODIFY `reproduction_conditions` TEXT %1$s, '
            .'MODIFY `physical_characteristics` TEXT %1$s, '
            .'MODIFY `finding_aids` TEXT %1$s, '
            .'MODIFY `location_of_originals` TEXT %1$s, '
            .'MODIFY `location_of_copies` TEXT %1$s, '
            .'MODIFY `related_units_of_description` TEXT %1$s, '
            .'MODIFY `institution_responsible_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `rules` TEXT %1$s, '
            .'MODIFY `sources` TEXT %1$s, '
            .'MODIFY `revision_history` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `keymap` %1$s, '
            .'MODIFY `source_id` TEXT %1$s, '
            .'MODIFY `source_name` TEXT %1$s, '
            .'MODIFY `target_name` TEXT %1$s;',

            'ALTER TABLE `menu` %1$s, '
            .'MODIFY `name` VARCHAR(255) %1$s, '
            .'MODIFY `path` VARCHAR(255) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `menu_i18n` %1$s, '
            .'MODIFY `label` VARCHAR(255) %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `note` %1$s, '
            .'MODIFY `scope` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `note_i18n` %1$s, '
            .'MODIFY `content` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `oai_harvest` %1$s, '
            .'MODIFY `metadataPrefix` VARCHAR(255) %1$s, '
            .'MODIFY `set` VARCHAR(1024) %1$s;',

            'ALTER TABLE `oai_repository` %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `uri` VARCHAR(1024) %1$s, '
            .'MODIFY `admin_email` VARCHAR(255) %1$s;',

            'ALTER TABLE `object` %1$s, '
            .'MODIFY `class_name` VARCHAR(255) %1$s;',

            'ALTER TABLE `object_term_relation` %1$s;',

            'ALTER TABLE `other_name` %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `other_name_i18n` %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `note` VARCHAR(1024) %1$s, '
            .'MODIFY `dates` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `physical_object` %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `physical_object_i18n` %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `location` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `premis_object` %1$s, '
            .'MODIFY `puid` VARCHAR(255) %1$s, '
            .'MODIFY `filename` VARCHAR(1024) %1$s, '
            .'MODIFY `mime_type` VARCHAR(255) %1$s;',

            'ALTER TABLE `property` %1$s, '
            .'MODIFY `scope` VARCHAR(1024) %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `property_i18n` %1$s, '
            .'MODIFY `value` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `relation` %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `relation_i18n` %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `date` VARCHAR(1024) %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `repository` %1$s, '
            .'MODIFY `identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `desc_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `repository_i18n` %1$s, '
            .'MODIFY `geocultural_context` TEXT %1$s, '
            .'MODIFY `collecting_policies` TEXT %1$s, '
            .'MODIFY `buildings` TEXT %1$s, '
            .'MODIFY `holdings` TEXT %1$s, '
            .'MODIFY `finding_aids` TEXT %1$s, '
            .'MODIFY `opening_times` TEXT %1$s, '
            .'MODIFY `access_conditions` TEXT %1$s, '
            .'MODIFY `disabled_access` TEXT %1$s, '
            .'MODIFY `research_services` TEXT %1$s, '
            .'MODIFY `reproduction_services` TEXT %1$s, '
            .'MODIFY `public_facilities` TEXT %1$s, '
            .'MODIFY `desc_institution_identifier` VARCHAR(1024) %1$s, '
            .'MODIFY `desc_rules` TEXT %1$s, '
            .'MODIFY `desc_sources` TEXT %1$s, '
            .'MODIFY `desc_revision_history` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `rights` %1$s, '
            .'MODIFY `copyright_jurisdiction` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `granted_right` %1$s, '
            .'MODIFY `notes` TEXT %1$s;',

            'ALTER TABLE `rights_i18n` %1$s, '
            .'MODIFY `rights_note` TEXT %1$s, '
            .'MODIFY `copyright_note` TEXT %1$s, '
            .'MODIFY `identifier_value` TEXT %1$s, '
            .'MODIFY `identifier_type` TEXT %1$s, '
            .'MODIFY `identifier_role` TEXT %1$s, '
            .'MODIFY `license_terms` TEXT %1$s, '
            .'MODIFY `license_note` TEXT %1$s, '
            .'MODIFY `statute_jurisdiction` TEXT %1$s, '
            .'MODIFY `statute_note` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `rights_holder` %1$s;',

            'ALTER TABLE `setting` %1$s, '
            .'MODIFY `name` VARCHAR(255) %1$s, '
            .'MODIFY `scope` VARCHAR(255) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `setting_i18n` %1$s, '
            .'MODIFY `value` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `slug` %1$s, '
            .'MODIFY `slug` VARCHAR(255) '
            .'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;',

            'ALTER TABLE `static_page` %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `static_page_i18n` %1$s, '
            .'MODIFY `title` VARCHAR(1024) %1$s, '
            .'MODIFY `content` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `status` %1$s;',

            'ALTER TABLE `taxonomy` %1$s, '
            .'MODIFY `usage` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `taxonomy_i18n` %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `note` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `term` %1$s, '
            .'MODIFY `code` VARCHAR(1024) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `term_i18n` %1$s, '
            .'MODIFY `name` VARCHAR(1024) %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `user` %1$s, '
            .'MODIFY `username` VARCHAR(255) %1$s, '
            .'MODIFY `email` VARCHAR(255) %1$s, '
            .'MODIFY `password_hash` VARCHAR(255) %1$s, '
            .'MODIFY `salt` VARCHAR(255) %1$s;',

            'ALTER TABLE `acl_group` %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `acl_group_i18n` %1$s, '
            .'MODIFY `name` VARCHAR(255) %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `acl_permission` %1$s, '
            .'MODIFY `action` VARCHAR(255) %1$s, '
            .'MODIFY `conditional` TEXT %1$s, '
            .'MODIFY `constants` TEXT %1$s;',

            'ALTER TABLE `acl_user_group` %1$s;',

            'ALTER TABLE `accession` %1$s, '
            .'MODIFY `identifier` VARCHAR(255) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `accession_i18n` %1$s, '
            .'MODIFY `appraisal` TEXT %1$s, '
            .'MODIFY `archival_history` TEXT %1$s, '
            .'MODIFY `location_information` TEXT %1$s, '
            .'MODIFY `physical_characteristics` TEXT %1$s, '
            .'MODIFY `processing_notes` TEXT %1$s, '
            .'MODIFY `received_extent_units` TEXT %1$s, '
            .'MODIFY `scope_and_content` TEXT %1$s, '
            .'MODIFY `source_of_acquisition` TEXT %1$s, '
            .'MODIFY `title` VARCHAR(255) %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `deaccession` %1$s, '
            .'MODIFY `identifier` VARCHAR(255) %1$s, '
            .'MODIFY `source_culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `deaccession_i18n` %1$s, '
            .'MODIFY `description` TEXT %1$s, '
            .'MODIFY `extent` TEXT %1$s, '
            .'MODIFY `reason` TEXT %1$s, '
            .'MODIFY `culture` VARCHAR(16) %1$s NOT NULL;',

            'ALTER TABLE `donor` %1$s;',
        ];

        foreach ($alterTables as $sql) {
            QubitPdo::modify(sprintf(
                $sql,
                'CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci'
            ));
        }

        return true;
    }
}
