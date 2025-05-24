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
 * Upgrade Qubit data from Release 1.1.
 */
class arUpgrader110
{
    public const MILESTONE = '1.1';
    public const INIT_VERSION = 62;

    public function up($version, $configuration, $options)
    {
        // This upgrader class is a port of QubitMigrate110
        // updateSchema() introduces the SQL schema changes that did not
        // fit in any existing db upgrade method
        if (self::INIT_VERSION == $version) {
            $this->updateSchema($configuration);
        }

        if ($options['verbose']) {
            echo "up({$version})\n";
        }

        switch ($version) {
            // Add accession module menu entry, internal taxonomies,
            // terms and settings
            case 62:
                // Add accession mask user setting
                $setting = new QubitSetting();
                $setting->name = 'accession_mask';
                $setting->value = '%Y-%m-%d/#i';
                $setting->culture = 'en';
                $setting->save();

                // Add accession counter setting
                $setting = new QubitSetting();
                $setting->name = 'accession_counter';
                $setting->value = '0';
                $setting->culture = 'en';
                $setting->save();

                // Update add button, accession is now the default action
                if (null !== $node = QubitMenu::getByName('add')) {
                    $node->path = 'accession/add';
                    $node->save();
                }

                // Create accession menu node
                $node = new QubitMenu();
                $node->parentId = QubitMenu::ADD_EDIT_ID;
                $node->name = 'addAccessionRecord';
                $node->path = 'accession/add';
                $node->label = 'Accession records';

                foreach (
                    [
                        'es' => 'Registros de adhesiones',
                        'fr' => 'Registre des entrées',
                        'pl' => 'Nabytki',
                        'sl' => 'Zapisi o prevzemu',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it before "addInformationObject"
                if (null !== $target = QubitMenu::getByName('addInformationObject')) {
                    $node->moveToPrevSiblingOf($target);
                }

                // Create manage menu node
                QubitMigrate::bumpMenu(QubitMenu::MANAGE_ID, $configuration);
                $node = new QubitMenu();
                $node->id = QubitMenu::MANAGE_ID;
                $node->parentId = QubitMenu::MAIN_MENU_ID;
                $node->name = 'manage';
                $node->path = 'accession/browse';
                $node->label = 'Manage';
                $node->culture = 'en';

                foreach (
                    [
                        'es' => 'Administrar',
                        'fr' => 'Gérer',
                        'pl' => 'Zarządzanie',
                        'sl' => 'Upravljaj',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it after "add"
                if (null !== $target = QubitMenu::getByName('add')) {
                    $node->moveToNextSiblingOf($target);
                }

                // Move taxonomies under "Manage"
                if (null !== $node = QubitMenu::getByName('taxonomies')) {
                    $node->parentId = QubitMenu::MANAGE_ID;
                    $node->save();
                }

                // Create manage accession menu node
                $node = new QubitMenu();
                $node->parentId = QubitMenu::MANAGE_ID;
                $node->name = 'accessions';
                $node->path = 'accession/browse';
                $node->label = 'Accession records';
                $node->culture = 'en';

                foreach (
                    [
                        'es' => 'Registros de adhesiones',
                        'fr' => 'Registre des entrées',
                        'pl' => 'Nabytki',
                        'sl' => 'Zapisi o prevzemu',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it before "taxonomies"
                if (null !== $target = QubitMenu::getByName('taxonomies')) {
                    $node->moveToPrevSiblingOf($target);
                }

                // Create manage donor menu node
                $node = new QubitMenu();
                $node->parentId = QubitMenu::MANAGE_ID;
                $node->name = 'donors';
                $node->path = 'donor/browse';
                $node->label = 'Donors';
                $node->culture = 'en';

                foreach (
                    [
                        'es' => 'Donantes',
                        'fr' => 'Donateurs',
                        'nl' => 'Schenkers',
                        'pl' => 'Przekazujący (materiały archiwalne)',
                        'sl' => 'Donatorji',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it after "accessions"
                if (null !== $target = QubitMenu::getByName('accessions')) {
                    $node->moveToNextSiblingOf($target);
                }

                // Create manage rightsholder menu node
                $node = new QubitMenu();
                $node->parentId = QubitMenu::MANAGE_ID;
                $node->name = 'rightsholders';
                $node->path = 'rightsholder/browse';
                $node->label = 'Rights holders';
                $node->culture = 'en';

                foreach (
                    [
                        'es' => 'Titulares de derechos',
                        'fr' => 'Détenteurs de droits',
                        'nl' => 'Houders van rechten',
                        'pl' => 'Posiadacze praw',
                        'sl' => 'Imetniki pravic',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it after "donors"
                if (null !== $target = QubitMenu::getByName('donors')) {
                    $node->moveToNextSiblingOf($target);
                }

                // New type of relation: accession
                QubitMigrate::bumpTerm(QubitTerm::ACCESSION_ID, $configuration);
                $term = new QubitTerm();
                $term->id = QubitTerm::ACCESSION_ID;
                $term->parentId = QubitTerm::ROOT_ID;
                $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
                $term->name = 'Accession';
                $term->culture = 'en';
                $term->save();

                // New type of relation: right
                QubitMigrate::bumpTerm(QubitTerm::RIGHT_ID, $configuration);
                $term = new QubitTerm();
                $term->id = QubitTerm::RIGHT_ID;
                $term->parentId = QubitTerm::ROOT_ID;
                $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
                $term->name = 'Right';
                $term->culture = 'en';
                $term->save();

                // New type of relation: donor
                QubitMigrate::bumpTerm(QubitTerm::DONOR_ID, $configuration);
                $term = new QubitTerm();
                $term->id = QubitTerm::DONOR_ID;
                $term->parentId = QubitTerm::ROOT_ID;
                $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
                $term->name = 'Donor';
                $term->culture = 'en';
                $term->save();

                // Accession resource type taxonomy and its terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID;
                $taxonomy->name = 'Accession resource type';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Public transfer',
                        'Private transfer',
                        'Acquisition type',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                // Accession acquisition type taxonomy and its terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID;
                $taxonomy->name = 'Accession acquisition type';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Deposit',
                        'Gift',
                        'Purchase',
                        'Transfer',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                // Processing priority taxonomy and terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID;
                $taxonomy->name = 'Processing priority';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'High',
                        'Medium',
                        'Low',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                // Processing status taxonomy and terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID;
                $taxonomy->name = 'Processing status';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Complete',
                        'Incomplete',
                        'In-Progress',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                // Deaccession scope taxonomy and terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::DEACCESSION_SCOPE_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::DEACCESSION_SCOPE_ID;
                $taxonomy->name = 'Deaccession scope';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Whole',
                        'Part',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::DEACCESSION_SCOPE_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    QubitMigrate::bumpTerm($term, $configuration);
                }

                // Right act taxonomy and terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::RIGHT_ACT_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::RIGHT_ACT_ID;
                $taxonomy->name = 'Rights act';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Delete',
                        'Discover',
                        'Display',
                        'Disseminate',
                        'Migrate',
                        'Modify',
                        'Replicate',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::RIGHT_ACT_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                // Right basis taxonomy and terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::RIGHT_BASIS_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::RIGHT_BASIS_ID;
                $taxonomy->name = 'Rights basis';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Copyright',
                        'License',
                        'Statute',
                        'Policy',
                        'Donor',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::RIGHT_BASIS_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                // Copyright status taxonomy and terms
                QubitMigrate::bumpTaxonomy(QubitTaxonomy::COPYRIGHT_STATUS_ID, $configuration);
                $taxonomy = new QubitTaxonomy();
                $taxonomy->id = QubitTaxonomy::COPYRIGHT_STATUS_ID;
                $taxonomy->name = 'Copyright status';
                $taxonomy->culture = 'en';
                $taxonomy->save();

                foreach (
                    [
                        'Under copyright',
                        'Public domain',
                        'Unknown',
                    ] as $item
                ) {
                    $term = new QubitTerm();
                    $term->parentId = QubitTerm::ROOT_ID;
                    $term->taxonomyId = QubitTaxonomy::COPYRIGHT_STATUS_ID;
                    $term->name = $item;
                    $term->culture = 'en';
                    $term->save();
                }

                break;

            // Migrate relation notes for date and description to relation_i18n table
            case 63:
                $sql = sprintf(
                    'SELECT id FROM note WHERE note.type_id IN (%s, %s)',
                    QubitTerm::RELATION_NOTE_DATE_ID,
                    QubitTerm::RELATION_NOTE_DESCRIPTION_ID
                );
                $stmt = QubitPdo::prepareAndExecute($sql);
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $note = QubitNote::getById($row[0]);

                    if (null === $relation = QubitRelation::getById($note->objectId)) {
                        continue;
                    }

                    $relation->sourceCulture = $note->sourceCulture;

                    switch ($note->typeId) {
                        case QubitTerm::RELATION_NOTE_DATE_ID:
                            $relation->date = $note->getContent(['sourceCulture' => true]);

                            break;

                        case QubitTerm::RELATION_NOTE_DESCRIPTION_ID:
                            $relation->description = $note->getContent(['sourceCulture' => true]);

                            break;
                    }

                    $relation->save();

                    $note->delete();

                    // Clear internal objects cache
                    QubitRelation::clearCache();
                    QubitNote::clearCache();
                }

                break;

            // Prior to r9340 all checksums were md5 and the algorithm was not
            // recorded, update checksum_type column
            case 64:
                $sql = 'UPDATE '.QubitDigitalObject::TABLE_NAME;
                $sql .= ' SET '.QubitDigitalObject::CHECKSUM_TYPE.' = "md5"';
                $sql .= ' WHERE CHAR_LENGTH(checksum) > 0';
                QubitPdo::modify($sql);

                break;

            // Add importCsv menu node, see also r9373
            case 65:
                $node = new QubitMenu();
                $node->parentId = QubitMenu::IMPORT_ID;
                $node->name = 'importCsv';
                $node->path = 'object/importSelect?type=csv';
                $node->label = 'CSV';
                $node->save();

                break;

            // Add global replace menu node
            case 66:
                $node = new QubitMenu();
                $node->parentId = QubitMenu::ADMIN_ID;
                $node->name = 'globalReplace';
                $node->path = 'search/globalReplace';
                $node->label = 'Global search/replace';
                $node->save();

                break;

            // Add setting for repository upload quota
            case 67:
                $setting = new QubitSetting();
                $setting->name = 'repository_quota';
                $setting->value = '-1';
                $setting->culture = 'en';
                $setting->save();

                break;

            // Add separator character setting
            case 68:
                $setting = new QubitSetting();
                $setting->name = 'separator_character';
                $setting->value = '-';
                $setting->culture = 'en';
                $setting->save();

                break;

            // Add themes menu and update plugins menu path
            case 69:
                $node = new QubitMenu();
                $node->parentId = QubitMenu::ADMIN_ID;
                $node->name = 'themes';
                $node->path = 'sfPluginAdminPlugin/themes';
                $node->label = 'Themes';

                foreach (
                    [
                        'es' => 'Temas',
                        'fr' => 'Thèmes',
                        'nl' => 'Thema\'s',
                        'pl' => 'Motywy',
                        'sl' => 'Teme',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it before "plugins"
                if (null !== $target = QubitMenu::getByName('plugins')) {
                    $node->moveToPrevSiblingOf($target);
                }

                // Update path of plugins node
                $node = QubitMenu::getByName('plugins');
                $node->path = 'sfPluginAdminPlugin/plugins';
                $node->save();

                break;

            // Move digital objects to repository specific paths like r9503
            case 70:
                if (!file_exists(sfConfig::get('sf_upload_dir').'/r')) {
                    mkdir(sfConfig::get('sf_upload_dir').'/r', 0775);
                }

                $sql = 'SELECT id, information_object_id
                    FROM digital_object
                    WHERE information_object_id IS NOT NULL';

                foreach (QubitPdo::fetchAll($sql) as $item) {
                    $io = QubitInformationObject::getById($item->information_object_id);

                    // Build repository dirname
                    if (null !== $repository = $io->getRepository(['inherit' => true])) {
                        if (!isset($repository->slug)) {
                            $slug = $this->getUniqueSlug($repository->getAuthorizedFormOfName(['sourceCulture' => true]));
                            if (!isset($slug) || 0 == strlen($slug)) {
                                continue;
                            }

                            $repoName = $repository->slug;
                        }

                        $repoName = $repository->slug;
                    } else {
                        $repoName = 'null';
                    }

                    // Update digital object and derivatives paths
                    $criteria = new Criteria();
                    $c1 = $criteria->getNewCriterion(QubitDigitalObject::PARENT_ID, $item->id);
                    $c2 = $criteria->getNewCriterion(QubitDigitalObject::ID, $item->id);
                    $c1->addOr($c2);
                    $criteria->add($c1);
                    $criteria->addAscendingOrderByColumn(QubitDigitalObject::USAGE_ID);
                    foreach (QubitDigitalObject::get($criteria) as $digitalObject) {
                        // Don't try to move remote assets
                        if (QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId) {
                            continue;
                        }

                        $oldPath = $digitalObject->path;

                        // Build new path
                        if (preg_match('|\d/\d/\d{3,}/$|', $oldPath, $matches)) {
                            $newPath = '/uploads/r/'.$repoName.'/'.$matches[0];
                        } else {
                            continue;
                        }

                        // Create new directories
                        if (!file_exists(sfConfig::get('sf_web_dir').$newPath)) {
                            if (!mkdir(sfConfig::get('sf_web_dir').$newPath, 0775, true)) {
                                continue;
                            }
                        }

                        // Move files
                        if (file_exists(sfConfig::get('sf_web_dir').$oldPath)) {
                            if (
                                !rename(
                                    sfConfig::get('sf_web_dir').$oldPath.$digitalObject->name,
                                    sfConfig::get('sf_web_dir').$newPath.$digitalObject->name
                                )
                            ) {
                                continue; // If rename fails, don't update path
                            }
                        }

                        // Delete old dirs, if they are empty
                        QubitDigitalObject::pruneEmptyDirs(sfConfig::get('sf_web_dir').$oldpath);

                        // Update path
                        $digitalObject->path = $newPath;

                        $digitalObject->save();
                    }

                    QubitInformationObject::clearCache();
                    QubitDigitalObject::clearCache();
                }

                break;

            // Add default value for repository.upload_limit column
            case 71:
                $sql = 'UPDATE '.QubitRepository::TABLE_NAME.' SET upload_limit = -1';
                QubitPdo::modify($sql);

                break;

            // Add physical object menu
            case 72:
                $node = new QubitMenu();
                $node->parentId = QubitMenu::MANAGE_ID;
                $node->name = 'browsePhysicalObjects';
                $node->path = 'physicalobject/browse';
                $node->label = 'Physical storage';

                foreach (
                    [
                        'es' => 'Almacenamiento físico',
                        'fr' => 'Localisation physique',
                        'nl' => 'Bergplaats',
                        'pl' => 'Składowanie w ujęciu fizycznym',
                        'sl' => 'Fizična hramba',
                    ] as $key => $value
                ) {
                    $nodeI18n = new QubitMenuI18n();
                    $nodeI18n->culture = $key;
                    $nodeI18n->label = $value;

                    $node->menuI18ns[] = $nodeI18n;
                }

                $node->save();

                // Introduce it after "donors"
                if (null !== $target = QubitMenu::getByName('donors')) {
                    $node->moveToNextSiblingOf($target);
                }

                break;

            // Migrate to sfCaribou theme to users that are currently using sfClassic
            case 73:
                if (null !== $setting = QubitSetting::getByName('plugins')) {
                    $plugin = 'sfClassicPlugin';
                    $replacement = 'qtTrilliumPlugin';

                    $settings = unserialize($setting->getValue(['sourceCulture' => true]));

                    // Find plugin
                    if (-1 < ($index = array_search($plugin, $settings))) {
                        // Replace
                        $settings[$index] = $replacement;

                        // Serialize
                        $setting->setValue(serialize($settings), ['sourceCulture' => true]);

                        // Save
                        $setting->save();
                    }
                }

                break;

            // Ensure all information objects get an explicit publication status
            case 74:
                $sql = 'SELECT id
                    FROM information_object
                    WHERE
                        information_object.parent_id IS NOT NULL
                        AND 1 > (SELECT COUNT(id) FROM status
                                WHERE
                                    status.object_id = information_object.id AND
                                    status.type_id = '.QubitTerm::STATUS_TYPE_PUBLICATION_ID.')';
                $stmt = QubitPdo::prepareAndExecute($sql);
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $item = QubitInformationObject::getById($row[0]);

                    // Ascend up object hierarchy until a publication status is found
                    // right up to the root object if necessary (which is set to 'draft' by default)
                    foreach ($item->ancestors->orderBy('rgt') as $ancestor) {
                        $status = $ancestor->getPublicationStatus();
                        if (isset($status) && null !== $status->statusId) {
                            $item->setPublicationStatus($status->statusId);
                            $item->save();

                            continue;
                        }
                    }

                    QubitInformationObject::clearCache();
                    QubitStatus::clearCache();
                }

                break;

            // Return false if no upgrade available
            default:
                return false;
        }

        return true;
    }

    public function updateSchema($configuration)
    {
        // Add ON DELETE CASCADE in slug.object_id
        $connection = Propel::getConnection();
        $connection->exec('SET FOREIGN_KEY_CHECKS = 0');
        $sql = 'ALTER TABLE `slug`
            DROP FOREIGN KEY `slug_FK_1`,
            ADD FOREIGN KEY (`object_id`)
            REFERENCES  `object` (`id`)
            ON DELETE CASCADE
            ON UPDATE RESTRICT;';
        $connection->exec($sql);
        $connection->exec('SET FOREIGN_KEY_CHECKS = 1');

        // Drop parent_id, lft and rgt columns from table note
        QubitMigrate::dropColumn(QubitNote::TABLE_NAME, 'parent_id');
        QubitMigrate::dropColumn(QubitNote::TABLE_NAME, 'lft');
        QubitMigrate::dropColumn(QubitNote::TABLE_NAME, 'rgt');

        // Add start_date and end_date in table other_name
        QubitMigrate::addColumn(QubitOtherName::TABLE_NAME, 'start_date DATE', ['after' => 'type_id']);
        QubitMigrate::addColumn(QubitOtherName::TABLE_NAME, 'end_date DATE', ['after' => 'start_date']);

        // Add dates column to other_name_i18n
        QubitMigrate::addColumn(QubitOtherNameI18n::TABLE_NAME, 'dates TEXT', ['after' => 'note']);

        // Add relation.source_culture
        QubitMigrate::addColumn(QubitRelation::TABLE_NAME, 'source_culture VARCHAR(7) NOT NULL', ['after' => 'end_date']);

        // Add repository.upload_limit
        QubitMigrate::addColumn(QubitRepository::TABLE_NAME, 'upload_limit FLOAT', ['after' => 'desc_identifier']);

        // Drop column digital_object.checksum_type_id
        QubitMigrate::dropColumn(QubitDigitalObject::TABLE_NAME, 'checksum_type_id');

        // Add column digital_object.checksumn_type
        QubitPdo::modify('ALTER TABLE digital_object ADD `checksum_type` VARCHAR(50) AFTER `checksum`');

        // Create relation_i18n
        $sql = <<<'sql'

CREATE TABLE `relation_i18n`
(
        `description` TEXT,
        `date` VARCHAR(255),
        `id` INTEGER  NOT NULL,
        `culture` VARCHAR(7)  NOT NULL,
        PRIMARY KEY (`id`,`culture`),
        CONSTRAINT `relation_i18n_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `relation` (`id`)
                ON DELETE CASCADE
)Engine=InnoDB;

sql;
        QubitPdo::modify($sql);

        // Drop unneeded tables
        foreach (
            [
                'historical_event',
                'map',
                'map_i18n',
                'place',
                'place_i18n',
                'place_map_relation',
                'rights',
                'rights_i18n',
                'rights_actor_relation',
                'rights_term_relation',
                'system_event',
            ] as $item
        ) {
            QubitMigrate::dropTable($item);
        }

        // Drop updated_at and created_at columns
        foreach (
            [
                'note',
                'other_name',
                'property',
                'status',
                'taxonomy',
            ] as $item
        ) {
            // Copy column updated_at and drop it
            $sql = "UPDATE object, {$item}";
            $sql .= " SET object.updated_at = {$item}.updated_at";
            $sql .= " WHERE object.id = {$item}.id";
            QubitPdo::modify($sql);
            QubitMigrate::dropColumn($item, 'updated_at');

            // Copy column created_at and drop it
            $sql = "UPDATE object, {$item}";
            $sql .= " SET object.created_at = {$item}.created_at";
            $sql .= " WHERE object.id = {$item}.id";
            QubitPdo::modify($sql);
            QubitMigrate::dropColumn($item, 'created_at');
        }

        // Add table keymap
        $sql = <<<'sql'

CREATE TABLE `keymap`
(
        `source_id` INTEGER,
        `target_id` INTEGER,
        `source_name` TEXT,
        `target_name` TEXT,
        `id` INTEGER  NOT NULL AUTO_INCREMENT,
        `serial_number` INTEGER default 0 NOT NULL,
        PRIMARY KEY (`id`)
)Engine=InnoDB;

sql;
        QubitPdo::modify($sql);

        // Add rights and rights_i18n tables
        $sql = <<<'sql'

CREATE TABLE `rights`
(
        `id` INTEGER  NOT NULL,
        `start_date` DATE,
        `end_date` DATE,
        `restriction` TINYINT default 1,
        `basis_id` INTEGER,
        `act_id` INTEGER,
        `rights_holder_id` INTEGER,
        `copyright_status_id` INTEGER,
        `copyright_status_date` DATE,
        `copyright_jurisdiction` VARCHAR(255),
        `statute_determination_date` DATE,
        `source_culture` VARCHAR(7)  NOT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `rights_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `object` (`id`)
                ON DELETE CASCADE,
        INDEX `rights_FI_2` (`basis_id`),
        CONSTRAINT `rights_FK_2`
                FOREIGN KEY (`basis_id`)
                REFERENCES `term` (`id`)
                ON DELETE SET NULL,
        INDEX `rights_FI_3` (`act_id`),
        CONSTRAINT `rights_FK_3`
                FOREIGN KEY (`act_id`)
                REFERENCES `term` (`id`)
                ON DELETE SET NULL,
        INDEX `rights_FI_4` (`rights_holder_id`),
        CONSTRAINT `rights_FK_4`
                FOREIGN KEY (`rights_holder_id`)
                REFERENCES `actor` (`id`)
                ON DELETE SET NULL,
        INDEX `rights_FI_5` (`copyright_status_id`),
        CONSTRAINT `rights_FK_5`
                FOREIGN KEY (`copyright_status_id`)
                REFERENCES `term` (`id`)
                ON DELETE SET NULL
)Engine=InnoDB;

CREATE TABLE `rights_i18n`
(
        `rights_note` TEXT,
        `copyright_note` TEXT,
        `license_identifier` TEXT,
        `license_terms` TEXT,
        `license_note` TEXT,
        `statute_jurisdiction` TEXT,
        `statute_citation` TEXT,
        `statute_note` TEXT,
        `id` INTEGER  NOT NULL,
        `culture` VARCHAR(7)  NOT NULL,
        PRIMARY KEY (`id`,`culture`),
        CONSTRAINT `rights_i18n_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `rights` (`id`)
                ON DELETE CASCADE
)Engine=InnoDB;

sql;
        QubitPdo::modify($sql);

        // Add rights_holder table
        $sql = <<<'sql'

CREATE TABLE `rights_holder`
(
        `id` INTEGER  NOT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `rights_holder_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `actor` (`id`)
                ON DELETE CASCADE
)Engine=InnoDB;

sql;
        QubitPdo::modify($sql);

        // Add qtAccessionPlugin SQL
        $sql = <<<'sql'

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `accession`
(
  `id` INTEGER  NOT NULL,
  `acquisition_type_id` INTEGER,
  `date` DATE,
  `identifier` VARCHAR(255),
  `processing_priority_id` INTEGER,
  `processing_status_id` INTEGER,
  `resource_type_id` INTEGER,
  `created_at` DATETIME  NOT NULL,
  `updated_at` DATETIME  NOT NULL,
  `source_culture` VARCHAR(7)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accession_U_1` (`identifier`),
  CONSTRAINT `accession_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `object` (`id`)
    ON DELETE CASCADE,
  INDEX `accession_FI_2` (`acquisition_type_id`),
  CONSTRAINT `accession_FK_2`
    FOREIGN KEY (`acquisition_type_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL,
  INDEX `accession_FI_3` (`processing_priority_id`),
  CONSTRAINT `accession_FK_3`
    FOREIGN KEY (`processing_priority_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL,
  INDEX `accession_FI_4` (`processing_status_id`),
  CONSTRAINT `accession_FK_4`
    FOREIGN KEY (`processing_status_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL,
  INDEX `accession_FI_5` (`resource_type_id`),
  CONSTRAINT `accession_FK_5`
    FOREIGN KEY (`resource_type_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL
)Engine=InnoDB;

CREATE TABLE `accession_i18n`
(
  `appraisal` TEXT,
  `archival_history` TEXT,
  `location_information` TEXT,
  `physical_characteristics` TEXT,
  `processing_notes` TEXT,
  `received_extent_units` TEXT,
  `scope_and_content` TEXT,
  `source_of_acquisition` TEXT,
  `title` VARCHAR(255),
  `id` INTEGER  NOT NULL,
  `culture` VARCHAR(7)  NOT NULL,
  PRIMARY KEY (`id`,`culture`),
  CONSTRAINT `accession_i18n_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `accession` (`id`)
    ON DELETE CASCADE
)Engine=InnoDB;

CREATE TABLE `deaccession`
(
  `id` INTEGER  NOT NULL,
  `accession_id` INTEGER,
  `date` DATE,
  `identifier` VARCHAR(255),
  `scope_id` INTEGER,
  `created_at` DATETIME  NOT NULL,
  `updated_at` DATETIME  NOT NULL,
  `source_culture` VARCHAR(7)  NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `deaccession_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `object` (`id`)
    ON DELETE CASCADE,
  INDEX `deaccession_FI_2` (`accession_id`),
  CONSTRAINT `deaccession_FK_2`
    FOREIGN KEY (`accession_id`)
    REFERENCES `accession` (`id`)
    ON DELETE CASCADE,
  INDEX `deaccession_FI_3` (`scope_id`),
  CONSTRAINT `deaccession_FK_3`
    FOREIGN KEY (`scope_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL
)Engine=InnoDB;

CREATE TABLE `deaccession_i18n`
(
  `description` TEXT,
  `extent` TEXT,
  `reason` TEXT,
  `id` INTEGER  NOT NULL,
  `culture` VARCHAR(7)  NOT NULL,
  PRIMARY KEY (`id`,`culture`),
  CONSTRAINT `deaccession_i18n_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `deaccession` (`id`)
    ON DELETE CASCADE
)Engine=InnoDB;

CREATE TABLE `donor`
(
  `id` INTEGER  NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `donor_FK_1`
    FOREIGN KEY (`id`)
    REFERENCES `actor` (`id`)
    ON DELETE CASCADE
)Engine=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

sql;
        QubitPdo::modify($sql);
    }
}
