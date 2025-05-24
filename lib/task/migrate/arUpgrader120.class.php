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

/**
 * Upgrade Qubit data from Release 1.2.
 */
class arUpgrader120
{
    public const MILESTONE = '1.2';
    public const INIT_VERSION = 75;

    public function up($version, $configuration, $options)
    {
        if ($options['verbose']) {
            echo "up({$version})\n";
        }

        switch ($version) {
            // Add setting for job_scheduling
            case 75:
                $setting = new QubitSetting();
                $setting->name = 'use_job_scheduler';
                $setting->value = '0';
                $setting->culture = 'en';

                $setting->save();

                break;

            // Add SWORD deposit directory setting
            case 76:
                $setting = new QubitSetting();
                $setting->name = 'sword_deposit_dir';
                $setting->value = '/tmp';
                $setting->culture = 'en';

                $setting->save();

                break;

            // Add security settings
            case 77:
                $setting = new QubitSetting();
                $setting->name = 'require_ssl_admin';
                $setting->value = '0';
                $setting->culture = 'en';
                $setting->save();

                $setting = new QubitSetting();
                $setting->name = 'require_strong_passwords';
                $setting->value = '0';
                $setting->culture = 'en';
                $setting->save();

                $setting = new QubitSetting();
                $setting->name = 'limit_admin_ip';
                $setting->value = '0';
                $setting->culture = 'en';
                $setting->save();

                break;

            // Add browse sort settings
            case 78:
                $setting = new QubitSetting();
                $setting->name = 'sort_browser_user';
                $setting->value = 'lastUpdated';
                $setting->culture = 'en';
                $setting->save();

                $setting = new QubitSetting();
                $setting->name = 'sort_browser_anonymous';
                $setting->value = 'alphabetic';
                $setting->culture = 'en';
                $setting->save();

                break;

            // Add 'Language note' term
            case 79:
                QubitMigrate::bumpTerm(QubitTerm::LANGUAGE_NOTE_ID, $configuration);
                $term = new QubitTerm();
                $term->id = QubitTerm::LANGUAGE_NOTE_ID;
                $term->parentId = QubitTerm::ROOT_ID;
                $term->taxonomyId = QubitTaxonomy::NOTE_TYPE_ID;
                $term->name = 'Language note';
                $term->culture = 'en';
                $term->save();

                break;

            // Add 'active' column to user table
            case 80:
                // NOTE: ALTER automatically sets `active`=1 (the DEFAULT) for existing rows
                QubitMigrate::addColumn(QubitUser::TABLE_NAME, 'active TINYINT DEFAULT 1', ['after' => 'salt']);

                break;

            // Use repository slug instead of id in acl_permission conditionals,
            // (r10996)
            case 81:
                $sql = sprintf(
                    'SELECT
                    id,
                    conditional,
                    constants
                    FROM %s
                    WHERE constants IS NOT NULL',
                    QubitAclPermission::TABLE_NAME
                );

                foreach (QubitPdo::fetchAll($sql) as $item) {
                    if ('%p[repositoryId] == %k[repositoryId]' == $item->conditional) {
                        $name = 'repository';
                    } elseif ('%p[taxonomyId] == %k[taxonomyId]' == $item->conditional) {
                        $name = 'taxonomy';
                    } else {
                        continue;
                    }

                    $arr = unserialize($item->constants);

                    // Get slug
                    $sql = 'SELECT slug FROM slug WHERE object_id = ?';
                    $slug = QubitPdo::fetchOne($sql, [$arr[$name.'Id']]);

                    // Update acl_permission values
                    if ($slug) {
                        $sql = sprintf(
                            'UPDATE %s SET
                            conditional = ?,
                            constants = ?
                            WHERE id = ?;',
                            QubitAclPermission::TABLE_NAME
                        );

                        QubitPdo::modify($sql, [
                            "%p[{$name}] == %k[{$name}]",
                            serialize([$name => $slug->slug]),
                            $item->id,
                        ]);
                    }
                }

                break;

            // Increase VARCHAR column limit to 1024 to avoid truncating long strings
            // Issue 1628
            case 82:
                // TEXT
                $textColumns = [
                    'actor' => [
                        'corporate_body_identifiers', 'description_identifier', 'source_standard',
                    ],
                    'actor_i18n' => [
                        'authorized_form_of_name', 'dates_of_existence', 'institution_responsible_identifier',
                    ],
                    'contact_information' => [
                        'contact_person', 'website',
                    ],
                    'contact_information_i18n' => [
                        'contact_type', 'city', 'region',
                    ],
                    'event_i18n' => [
                        'name', 'date',
                    ],
                    'function' => [
                        'description_identifier', 'source_standard',
                    ],
                    'function_i18n' => [
                        'authorized_form_of_name', 'classification', 'dates',
                    ],
                    'information_object' => [
                        'identifier', 'description_identifier', 'source_standard',
                    ],
                    'information_object_i18n' => [
                        'title', 'alternate_title', 'edition', 'institution_responsible_identifier',
                    ],
                    'note' => [
                        'scope',
                    ],
                    'oai_harvest' => [
                        'set',
                    ],
                    'oai_repository' => [
                        'name', 'uri',
                    ],
                    'other_name_i18n' => [
                        'name', 'note',
                    ],
                    'physical_object_i18n' => [
                        'name',
                    ],
                    'property' => [
                        'scope', 'name',
                    ],
                    'property_i18n' => [
                        'value',
                    ],
                    'relation_i18n' => [
                        'date',
                    ],
                    'repository' => [
                        'identifier', 'desc_identifier',
                    ],
                    'repository_i18n' => [
                        'desc_institution_identifier',
                    ],
                    'rights' => [
                        'copyright_jurisdiction',
                    ],
                    'static_page_i18n' => [
                        'title',
                    ],
                    'taxonomy' => [
                        'usage',
                    ],
                    'taxonomy_i18n' => [
                        'name',
                    ],
                    'term' => [
                        'code',
                    ],
                    'term_i18n' => [
                        'name',
                    ],
                ];

                // Convert varchar columns to text
                foreach ($textColumns as $tablename => $cols) {
                    foreach ($cols as $col) {
                        $sql = sprintf('ALTER TABLE `%s` MODIFY `%s` VARCHAR(1024);', $tablename, $col);
                        QubitPdo::modify($sql);
                    }
                }

                // TEXT NOT NULL
                $textNotNullColumns = [
                    'digital_object' => [
                        'name', 'path',
                    ],
                ];

                foreach ($textNotNullColumns as $tablename => $cols) {
                    foreach ($cols as $col) {
                        $sql = sprintf('ALTER TABLE `%s` MODIFY `%s` VARCHAR(1024) NOT NULL;', $tablename, $col);
                        QubitPdo::modify($sql);
                    }
                }

                break;

            // Increase width of digital_object.mime_type to 255 chars
            case 83:
                $sql = 'ALTER TABLE '.QubitDigitalObject::TABLE_NAME.' MODIFY `mime_type` VARCHAR(255);';
                QubitPdo::modify($sql);

                break;

            // Add accrual constant to term table
            case 84:
                QubitMigrate::bumpTerm(QubitTerm::ACCRUAL_ID, $configuration);
                $term = new QubitTerm();
                $term->id = QubitTerm::ACCRUAL_ID;
                $term->parentId = QubitTerm::ROOT_ID;
                $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
                $term->culture = 'en';
                $term->name = 'Accrual';
                $term->save();

                break;

            // Fix typo: r11890
            case 85:
                $sql = 'UPDATE '.QubitTermI18n::TABLE_NAME;
                $sql .= ' SET '.QubitTermI18n::NAME.' = "Disseminate"';
                $sql .= ' WHERE '.QubitTermI18n::CULTURE.' = "en"';
                $sql .= ' AND '.QubitTermI18n::NAME.' = "Disemanite"';
                QubitPdo::modify($sql);

                break;

            // Fix issue 2344
            case 86:
                // Type of relation: right
                // Check first wether it exists
                $criteria = new Criteria();
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::RELATION_TYPE_ID);
                $criteria->add(QubitTermI18n::CULTURE, 'en');
                $criteria->add(QubitTermI18n::NAME, 'Right');

                if (null === QubitTerm::getOne($criteria)) {
                    $term = new QubitTerm();
                    $term->id = QubitTerm::RIGHT_ID;
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
                    $term->name = 'Right';
                    $term->culture = 'en';
                    $term->save();
                }

                // Type of relation: donor
                // Check first wether it exists
                $criteria = new Criteria();
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::RELATION_TYPE_ID);
                $criteria->add(QubitTermI18n::CULTURE, 'en');
                $criteria->add(QubitTermI18n::NAME, 'Donor');

                if (null === QubitTerm::getOne($criteria)) {
                    $term = new QubitTerm();
                    $term->id = QubitTerm::DONOR_ID;
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
                    $term->name = 'Donor';
                    $term->culture = 'en';
                    $term->save();
                }

                break;

            // Change property name from dipUUID to aipUUID
            case 87:
                $sql = 'UPDATE '.QubitProperty::TABLE_NAME.' SET name = "aipUUID" WHERE name = "dipUUID"';
                QubitPdo::modify($sql);

                break;

            // According to r10747, keymap.source_id changed from INTEGER to TEXT
            case 88:
                $sql = 'ALTER TABLE '.QubitKeymap::TABLE_NAME.' CHANGE source_id source_id TEXT';
                QubitPdo::modify($sql);

                break;

            // Add "Visible elements" assets
            case 89:
                $elements = [
                    'isad_immediate_source',
                    'isad_appraisal_destruction',
                    'isad_notes',
                    'isad_physical_condition',
                    'isad_control_description_identifier',
                    'isad_control_institution_identifier',
                    'isad_control_rules_conventions',
                    'isad_control_status',
                    'isad_control_level_of_detail',
                    'isad_control_dates',
                    'isad_control_languages',
                    'isad_control_scripts',
                    'isad_control_sources',
                    'isad_control_archivists_notes',
                    'rad_physical_condition',
                    'rad_immediate_source',
                    'rad_general_notes',
                    'rad_conservation_notes',
                    'rad_control_description_identifier',
                    'rad_control_institution_identifier',
                    'rad_control_rules_conventions',
                    'rad_control_status',
                    'rad_control_level_of_detail',
                    'rad_control_dates',
                    'rad_control_language',
                    'rad_control_script',
                    'rad_control_sources',
                    'digital_object_url',
                    'digital_object_file_name',
                    'digital_object_media_type',
                    'digital_object_mime_type',
                    'digital_object_file_size',
                    'digital_object_uploaded',
                    'physical_storage',
                ];

                // Add visibility settings
                foreach ($elements as $item) {
                    $setting = new QubitSetting();
                    $setting->name = $item;
                    $setting->scope = 'element_visibility';
                    $setting->value = 1;
                    $setting->culture = 'en';
                    $setting->save();
                }

                // Add "Visible elements" menu
                $node = new QubitMenu();
                $node->parentId = QubitMenu::ADMIN_ID;
                $node->name = 'visibleElements';
                $node->path = 'settings/visibleElements';
                $node->label = 'Visible elements';
                $node->save();

                // Introduce it after "globalReplace"
                if (null !== $target = QubitMenu::getByName('globalReplace')) {
                    $node->moveToNextSiblingOf($target);
                }

                break;

            // Make property_i18n.value field TEXT to accommodate pdf text (r12026)
            case 90:
                $sql = 'ALTER TABLE `property_i18n` MODIFY `value` TEXT;';
                QubitPdo::modify($sql);

                break;

            // Update translations from fixtures
            case 91:
                QubitMigrate::addNewFixtureI18ns();

                break;

            // Return false if no upgrade available
            default:
                return false;
        }

        return true;
    }
}
