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
class arMigration0177
{
  const
    VERSION = 177, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
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
    // - Restore the NOT NULL constraint on slug and culture columns (removed
    //   on migrations 159 and 172).
    $sql = <<<sql
ALTER TABLE `access_log`
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `actor`
MODIFY `corporate_body_identifiers` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_standard` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `actor_i18n`
MODIFY `authorized_form_of_name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `dates_of_existence` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `places` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `legal_status` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `functions` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `mandates` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `internal_structures` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `general_context` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `institution_responsible_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `rules` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `sources` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `revision_history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `aip`
MODIFY `uuid` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `filename` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `audit_log`
MODIFY `user_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `job`
MODIFY `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `download_path` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `output` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `clipboard_save`
MODIFY `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `clipboard_save_item`
MODIFY `item_class_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `slug` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `contact_information`
MODIFY `contact_person` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `street_address` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `website` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `telephone` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `fax` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `postal_code` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `country_code` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `contact_information_i18n`
MODIFY `contact_type` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `city` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `region` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `digital_object`
DROP INDEX `path`, ADD INDEX `path`(`path`(768)),
MODIFY `mime_type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
MODIFY `path` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
MODIFY `checksum` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `checksum_type` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `event`
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `event_i18n`
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `date` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `function_object`
MODIFY `description_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_standard` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `function_object_i18n`
MODIFY `authorized_form_of_name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `classification` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `dates` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `legislation` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `institution_identifier` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `revision_history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `rules` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `sources` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `information_object`
MODIFY `identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_standard` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `information_object_i18n`
MODIFY `title` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `alternate_title` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `edition` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `extent_and_medium` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `archival_history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `acquisition` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `scope_and_content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `appraisal` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `accruals` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `arrangement` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `access_conditions` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `reproduction_conditions` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `physical_characteristics` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `finding_aids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `location_of_originals` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `location_of_copies` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `related_units_of_description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `institution_responsible_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `rules` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `sources` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `revision_history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `keymap`
MODIFY `source_id` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `target_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `menu`
MODIFY `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `path` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `menu_i18n`
MODIFY `label` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `note`
MODIFY `scope` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `note_i18n`
MODIFY `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `oai_harvest`
MODIFY `metadataPrefix` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `set` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `oai_repository`
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `uri` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `admin_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `object`
MODIFY `class_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `object_term_relation`
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `other_name`
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `other_name_i18n`
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `note` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `dates` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `physical_object`
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `physical_object_i18n`
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `location` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `premis_object`
MODIFY `puid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `filename` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `mime_type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `property`
MODIFY `scope` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `property_i18n`
MODIFY `value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `relation`
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `relation_i18n`
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `date` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `repository`
MODIFY `identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `desc_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `repository_i18n`
MODIFY `geocultural_context` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `collecting_policies` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `buildings` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `holdings` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `finding_aids` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `opening_times` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `access_conditions` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `disabled_access` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `research_services` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `reproduction_services` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `public_facilities` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `desc_institution_identifier` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `desc_rules` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `desc_sources` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `desc_revision_history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `rights`
MODIFY `copyright_jurisdiction` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `granted_right`
MODIFY `notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `rights_i18n`
MODIFY `rights_note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `copyright_note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `identifier_value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `identifier_type` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `identifier_role` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `license_terms` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `license_note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `statute_jurisdiction` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `statute_note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `rights_holder`
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `setting`
MODIFY `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `scope` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `setting_i18n`
MODIFY `value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `slug`
MODIFY `slug` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `static_page`
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `static_page_i18n`
MODIFY `title` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `status`
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `taxonomy`
MODIFY `usage` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `taxonomy_i18n`
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `term`
MODIFY `code` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `term_i18n`
MODIFY `name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `user`
MODIFY `username` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `password_hash` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `salt` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `acl_group`
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `acl_group_i18n`
MODIFY `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `acl_permission`
MODIFY `action` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `conditional` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `constants` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `acl_user_group`
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `accession`
MODIFY `identifier` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `accession_i18n`
MODIFY `appraisal` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `archival_history` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `location_information` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `physical_characteristics` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `processing_notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `received_extent_units` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `scope_and_content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_of_acquisition` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `deaccession`
MODIFY `identifier` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `source_culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `deaccession_i18n`
MODIFY `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `extent` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `reason` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY `culture` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `donor`
CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
sql;
    QubitPdo::modify($sql);

    return true;
  }
}
