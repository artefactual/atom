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
 * Upgrade qubit version 1.1 data for version 1.2 schema.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitMigrate110 extends QubitMigrate
{
    public const MILESTONE = '1.1';
    public const INIT_VERSION = 62;
    public const FINAL_VERSION = 75;

    public function execute()
    {
        $this->slugData();
        $this->alterData();
        $this->sortData();

        return $this->getData();
    }

    /**
     * Controller for calling methods to alter data.
     *
     * @return QubitMigrate110 this object
     */
    protected function alterData()
    {
        switch ($this->version) {
            case 62:
                $this->addAccessionFixtures();

                // no break
            case 63:
                $this->moveRelationNotesToI18n();

                // no break
            case 64:
                $this->setChecksumType();

                // no break
            case 65:
                $this->addCsvMenu();

                // no break
            case 66:
                $this->addRepositoryQuotaSetting();

                // no break
            case 67:
                $this->addSeparatorCharacterSetting();

                // no break
            case 68:
                $this->addThemesMenu();

                // no break
            case 69:
                $this->updatePathToAssets();

                // no break
            case 70:
                $this->addRepostioryUploadLimit();

                // no break
            case 71:
                $this->addPhysicalObjectBrowseMenu();

                // no break
            case 72:
                $this->switchFromClassicToCaribouTheme();

                // no break
            case 73:
                $this->addGlobalReplaceMenu();

                // no break
            case 74:
                $this->ensurePublicationStatus();
        }

        // Delete "stub" objects
        $this->deleteStubObjects();

        // Remove repository root
        $this->removeRepositoryRoot();

        return $this;
    }

    /**
     * Add accession module menu entry, internal
     * taxonomies, terms, settings.
     *
     * @return QubitMigrate110 this object
     */
    protected function addAccessionFixtures()
    {
        // Add accession mask user setting
        if (false === $this->getRowKey('QubitSetting', 'name', 'QubitSetting_accessionMask')) {
            $this->data['QubitSetting']['QubitSetting_accessionMask'] = [
                'name' => 'accession_mask',
                'value' => '%Y-%m-%d/#i',
            ];
        }

        // Add accession counter setting
        if (false === $this->getRowKey('QubitSetting', 'name', 'QubitSetting_accessionCounter')) {
            $this->data['QubitSetting']['QubitSetting_accessionCounter'] = [
                'name' => 'accession_counter',
                'value' => 0,
            ];
        }

        // Update add button, accession is now the default action
        if ($key = $this->findRowKeyForColumnValue($this->data['QubitMenu'], 'name', 'add')) {
            $this->data['QubitMenu'][$key]['path'] = 'accession/add';
        }

        // Create accessioning menu node
        $accessionAddMenu = [
            'parent_id' => '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'addAccessionRecord',
            'label' => [
                'en' => 'Accession records',
                'es' => 'Registros de adhesiones',
                'fr' => 'Registre des entrées',
                'pl' => 'Nabytki',
                'sl' => 'Zapisi o prevzemu',
            ],
            'path' => 'accession/add',
        ];

        // Introduce it before "addInformationObject"
        if ($pivotKey = $this->findRowKeyForColumnValue($this->data['QubitMenu'], 'name', 'addInformationObject')) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_addedit_accession' => $accessionAddMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_addedit_accession'] = $accessionAddMenu;
        }

        // Create manage menu node
        $manageMenu = [
            'id' => '<?php echo QubitMenu::MANAGE_ID."\n" ?>',
            'parent_id' => '<?php echo QubitMenu::MAIN_MENU_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'manage',
            'label' => [
                'en' => 'Manage',
                'es' => 'Administrar',
                'fr' => 'Gérer',
                'pl' => 'Zarządzanie',
                'sl' => 'Upravljaj',
            ],
            'path' => 'accession/browse',
        ];

        // Introduce it before taxonomies
        if ($pivotKey = $this->findRowKeyForColumnValue($this->data['QubitMenu'], 'name', 'taxonomies')) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_manage' => $manageMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_manage'] = $manageMenu;
        }

        // Move taxonomies under "Manage"
        $this->data['QubitMenu'][$pivotKey]['parent_id'] = QubitMenu::MANAGE_ID;

        // Create manage accession menu node
        $accessionsManageMenu = [
            'parent_id' => '<?php echo QubitMenu::MANAGE_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'accessions',
            'label' => [
                'en' => 'Accession records',
                'es' => 'Registros de adhesiones',
                'fr' => 'Registre des entrées',
                'pl' => 'Nabytki',
                'sl' => 'Zapisi o prevzemu',
            ],
            'path' => 'accession/browse',
        ];

        // Introduce it before taxonomies
        if ($pivotKey) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_manage_accessions' => $accessionsManageMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_manage_accessions'] = $accessionsManageMenu;
        }

        // Create manage donor menu node
        $donorsManageMenu = [
            'parent_id' => '<?php echo QubitMenu::MANAGE_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'donors',
            'label' => [
                'en' => 'Donors',
                'es' => 'Donantes',
                'fr' => 'Donateurs',
                'nl' => 'Schenkers',
                'pl' => 'Przekazujący (materiały archiwalne)',
                'sl' => 'Donatorji',
            ],
            'path' => 'donor/browse',
        ];

        // Introduce it before taxonomies
        if ($pivotKey) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_manage_donors' => $donorsManageMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_manage_donors'] = $donorsManageMenu;
        }

        // Create manage rightsholder menu node
        $rightsHoldersManageMenu = [
            'parent_id' => '<?php echo QubitMenu::MANAGE_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'rightsholders',
            'label' => [
                'en' => 'Rights holders',
                'es' => 'Titulares de derechos',
                'fr' => 'Détenteurs de droits',
                'nl' => 'Houders van rechten',
                'pl' => 'Posiadacze praw',
                'sl' => 'Imetniki pravic',
            ],
            'path' => 'rightsholder/browse',
        ];

        // Introduce it before taxonomies
        if ($pivotKey) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_manage_rightsholders' => $rightsHoldersManageMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_manage_rightsholders'] = $rightsHoldersManageMenu;
        }

        // New type of relations: accession and right
        $this->data['QubitTerm']['QubitTerm_accession'] = [
            'id' => '<?php echo QubitTerm::ACCESSION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'name' => ['en' => 'Accession'],
            'source_culture' => 'en',
            'taxonomy_id' => '<?php echo QubitTaxonomy::RELATION_TYPE_ID."\n" ?>',
        ];
        $this->data['QubitTerm']['QubitTerm_right'] = [
            'id' => '<?php echo QubitTerm::RIGHT_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'name' => ['en' => 'Right'],
            'source_culture' => 'en',
            'taxonomy_id' => '<?php echo QubitTaxonomy::RELATION_TYPE_ID."\n" ?>',
        ];
        $this->data['QubitTerm']['QubitTerm_donor'] = [
            'id' => '<?php echo QubitTerm::DONOR_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'name' => ['en' => 'Donor'],
            'source_culture' => 'en',
            'taxonomy_id' => '<?php echo QubitTaxonomy::RELATION_TYPE_ID."\n" ?>',
        ];

        // Accession resource type taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_accession_resource_type'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID."\n" ?>',
            'name' => ['en' => 'Accession resource type'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_resource_type_public'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'Public transfer'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_resource_type_private'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'Private transfer'],
        ];

        // Acquisition type taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_accession_acquisition_type'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID."\n" ?>',
            'name' => ['en' => 'Acquisition type'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_acquisition_type_deposit'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_acquisition_type',
            'source_culture' => 'en',
            'name' => ['en' => 'Deposit'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_acquisition_type_gift'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_acquisition_type',
            'source_culture' => 'en',
            'name' => ['en' => 'Gift'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_acquisition_type_purchase'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_acquisition_type',
            'source_culture' => 'en',
            'name' => ['en' => 'Purchase'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_acquisition_type_transfer'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_acquisition_type',
            'source_culture' => 'en',
            'name' => ['en' => 'Transfer'],
        ];

        // Processing priority taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_accession_processing_priority'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID."\n" ?>',
            'name' => ['en' => 'Processing priority'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_processing_priority_high'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_processing_priority',
            'source_culture' => 'en',
            'name' => ['en' => 'High'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_processing_priority_medium'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_processing_priority',
            'source_culture' => 'en',
            'name' => ['en' => 'Medium'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_processing_priority_low'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_processing_priority',
            'source_culture' => 'en',
            'name' => ['en' => 'Low'],
        ];

        // Processing status taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_accession_processing_status'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID."\n" ?>',
            'name' => ['en' => 'Processing status'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_processing_status_complete'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_processing_status',
            'source_culture' => 'en',
            'name' => ['en' => 'Complete'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_processing_status_incomplete'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_processing_status',
            'source_culture' => 'en',
            'name' => ['en' => 'Incomplete'],
        ];
        $this->data['QubitTerm']['QubitTerm_accession_processing_status_inprogress'] = [
            'taxonomy_id' => 'QubitTaxonomy_accession_processing_status',
            'source_culture' => 'en',
            'name' => ['en' => 'In-Progress'],
        ];

        // Deaccession scope taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_deaccession_scope'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::DEACCESSION_SCOPE_ID."\n" ?>',
            'name' => ['en' => 'Deaccession scope'],
        ];
        $this->data['QubitTerm']['QubitTerm_deaccession_scope_whole'] = [
            'taxonomy_id' => 'QubitTaxonomy_deaccession_scope',
            'source_culture' => 'en',
            'name' => ['en' => 'Whole'],
        ];
        $this->data['QubitTerm']['QubitTerm_deaccession_scope_part'] = [
            'taxonomy_id' => 'QubitTaxonomy_deaccession_scope',
            'source_culture' => 'en',
            'name' => ['en' => 'Part'],
        ];

        // Right act taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_right_act'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::RIGHT_ACT_ID."\n" ?>',
            'name' => ['en' => 'Rights act'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_delete'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Delete'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_discover'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Discover'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_display'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Display'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_disemanite'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Disseminate'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_migrate'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Migrate'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_modify'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Modify'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_act_replicate'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_act',
            'source_culture' => 'en',
            'name' => ['en' => 'Replicate'],
        ];

        // Right basis taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_right_basis'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::RIGHT_BASIS_ID."\n" ?>',
            'name' => ['en' => 'Rights basis'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_basis_copyright'] = [
            'id' => '<?php echo QubitTerm::RIGHT_BASIS_COPYRIGHT_ID."\n" ?>',
            'taxonomy_id' => 'QubitTaxonomy_right_basis',
            'source_culture' => 'en',
            'name' => ['en' => 'Copyright'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_basis_license'] = [
            'id' => '<?php echo QubitTerm::RIGHT_BASIS_LICENSE_ID."\n" ?>',
            'taxonomy_id' => 'QubitTaxonomy_right_basis',
            'source_culture' => 'en',
            'name' => ['en' => 'License'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_basis_statute'] = [
            'id' => '<?php echo QubitTerm::RIGHT_BASIS_STATUTE_ID."\n" ?>',
            'taxonomy_id' => 'QubitTaxonomy_right_basis',
            'source_culture' => 'en',
            'name' => ['en' => 'Statute'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_basis_policy'] = [
            'id' => '<?php echo QubitTerm::RIGHT_BASIS_POLICY_ID."\n" ?>',
            'taxonomy_id' => 'QubitTaxonomy_right_basis',
            'source_culture' => 'en',
            'name' => ['en' => 'Policy'],
        ];
        $this->data['QubitTerm']['QubitTerm_right_basis_donor'] = [
            'taxonomy_id' => 'QubitTaxonomy_right_basis',
            'source_culture' => 'en',
            'name' => ['en' => 'Donor'],
        ];

        // Copyright status taxonomy and terms
        $this->data['QubitTaxonomy']['QubitTaxonomy_copyright_status'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::COPYRIGHT_STATUS_ID."\n" ?>',
            'name' => ['en' => 'Copyright status'],
        ];
        $this->data['QubitTerm']['QubitTerm_copyright_status_under_copyright'] = [
            'taxonomy_id' => 'QubitTaxonomy_copyright_status',
            'source_culture' => 'en',
            'name' => ['en' => 'Under copyright'],
        ];
        $this->data['QubitTerm']['QubitTerm_copyright_status_public_domain'] = [
            'taxonomy_id' => 'QubitTaxonomy_copyright_status',
            'source_culture' => 'en',
            'name' => ['en' => 'Public domain'],
        ];
        $this->data['QubitTerm']['QubitTerm_copyright_status_unknown'] = [
            'taxonomy_id' => 'QubitTaxonomy_copyright_status',
            'source_culture' => 'en',
            'name' => ['en' => 'Unknown'],
        ];

        return $this;
    }

    /**
     * Migrate relation notes for date and description to relation_i18n table.
     *
     * @return QubitMigrate110 this object
     */
    protected function moveRelationNotesToI18n()
    {
        // Search for relation notes
        foreach ($this->data['QubitNote'] as $key => $item) {
            if (isset($item['type_id'])) {
                $type_id = $item['type_id'];

                // Test if type_id is defined in related QubitTerm row
                if (isset($this->data['QubitTerm'][$item['type_id']])) {
                    $type_id = $this->data['QubitTerm'][$item['type_id']]['id'];
                }

                switch ($type_id) {
                    case '<?php echo QubitTerm::RELATION_NOTE_DATE_ID."\n" ?>':
                        $colname = 'date';

                        break;

                    case '<?php echo QubitTerm::RELATION_NOTE_DESCRIPTION_ID."\n" ?>':
                        $colname = 'description';

                        break;

                    default:
                        continue 2;
                }

                // Replace relation note with relation_i18n row
                if (isset($this->data['QubitRelation'][$item['object_id']], $item['content'])) {
                    $this->data['QubitRelation'][$item['object_id']]['source_culture'] = $item['source_culture'];
                    $this->data['QubitRelation'][$item['object_id']][$colname] = $item['content'];
                }

                unset($this->data['QubitNote'][$key]);
            }
        }

        return $this;
    }

    /**
     * Prior to revision 9340 all checksums were md5 and the algorithm was not
     * recorded.
     *
     * @return QubitMigrate110 SELF
     */
    protected function setChecksumType()
    {
        if (isset($this->data['QubitDigitalObject'])) {
            foreach ($this->data['QubitDigitalObject'] as $key => &$item) {
                if (!isset($item['checksum']) || 0 == strlen($item['checksum'])) {
                    // No checksum, skip
                    continue;
                }

                // Used md5 for all checksums previous to r9340
                $item['checksumType'] = 'md5';
            }
        }

        return $this;
    }

    /**
     * Add menu node, see also r9373.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addCsvMenu()
    {
        $this->data['QubitMenu']['QubitMenu_mainmenu_import_csv'] = [
            'parent_id' => '<?php echo QubitMenu::IMPORT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'importCsv',
            'label' => ['en' => 'CSV'],
            'path' => 'object/importSelect?type=csv',
        ];

        return $this;
    }

    /**
     * Add menu node.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addGlobalReplaceMenu()
    {
        $this->data['QubitMenu']['QubitMenu_mainmenu_admin_globalreplace'] = [
            'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'globalReplace',
            'label' => ['en' => 'Global search/replace'],
            'path' => 'search/globalReplace',
        ];

        return $this;
    }

    /**
     * Add setting for repository upload quota.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addRepositoryQuotaSetting()
    {
        $this->data['QubitSetting']['QubitSetting_repositoryQuota'] = [
            'name' => 'repository_quota',
            'editable' => 1,
            'deleteable' => 0,
            'value' => ['en' => '-1'],
            'source_culture' => 'en',
        ];

        return $this;
    }

    /**
     * Add separator character setting.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addSeparatorCharacterSetting()
    {
        // Add separator character
        if (false === $this->getRowKey('QubitSetting', 'name', 'QubitSetting_separatorCharacter')) {
            $this->data['QubitSetting']['QubitSetting_separatorCharacter'] = [
                'name' => 'separator_character',
                'value' => '-',
            ];
        }

        return $this;
    }

    /**
     * Add themes menu and update plugins menu path.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addThemesMenu()
    {
        // Create themes menu node
        $themesMenu = [
            'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'themes',
            'label' => [
                'en' => 'Themes',
                'es' => 'Temas',
                'fr' => 'Thèmes',
                'nl' => 'Thema\'s',
                'pl' => 'Motywy',
                'sl' => 'Teme',
            ],
            'path' => 'sfPluginAdminPlugin/themes',
        ];

        // Introduce themes menu before settings
        if ($pivotKey = $this->findRowKeyForColumnValue($this->data['QubitMenu'], 'name', 'settings')) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_admin_themes' => $themesMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_themes'] = $themesMenu;
        }

        // Update plugins menu path
        if ($key = $this->findRowKeyForColumnValue($this->data['QubitMenu'], 'name', 'plugins')) {
            $this->data['QubitMenu'][$key]['path'] = 'sfPluginAdminPlugin/plugins';
        }

        return $this;
    }

    /**
     * Move digital objects to repository specific paths like (SVN r9503).
     *
     * @return QubitMigrate110 SELF
     */
    protected function updatePathToAssets()
    {
        // Create "uploads/r" subdirectory
        if (!file_exists(sfConfig::get('sf_upload_dir').'/r')) {
            mkdir(sfConfig::get('sf_upload_dir').'/r', 0775);
        }

        foreach ($this->data['QubitDigitalObject'] as $key => &$item) {
            if (!isset($item['information_object_id'])) {
                continue;
            }

            // Get the related information object
            $infoObject = $this->getRowByKeyOrId('QubitInformationObject', $item['information_object_id']);
            if (null === $infoObject) {
                continue;
            }

            // Recursively check info object ancestors for repository foreign key
            while (!isset($infoObject['repository_id']) && isset($infoObject['parent_id'])) {
                $infoObject = $this->getRowByKeyOrId('QubitInformationObject', $infoObject['parent_id']);
            }

            // Get repository
            if (isset($infoObject['repository_id'])) {
                $repo = &$this->data['QubitRepository'][$infoObject['repository_id']];

                if (!isset($repo['slug'])) {
                    $slug = $this->getUniqueSlug($repo['authorized_form_of_name'][$repo['source_culture']]);
                    if (!isset($slug) || 0 == strlen($slug)) {
                        continue;
                    }

                    $repo['slug'] = $slug;
                }

                $repoName = $repo['slug'];
            } else {
                $repoName = 'null';
            }

            // Update digital object and derivatives paths
            foreach ($this->data['QubitDigitalObject'] as $key2 => &$item2) {
                if ($key == $key2 || (isset($item2['parent_id']) && $key == $item2['parent_id'])) {
                    // Don't try to move remote assets
                    $externalUriKey = $this->getTermKey('<?php echo QubitTerm::EXTERNAL_URI_ID."\n" ?>');
                    if ($externalUriKey == $item2['usage_id']) {
                        continue;
                    }

                    $oldpath = $item2['path'];

                    // Build new path
                    if (preg_match('|\d/\d/\d{3,}/$|', $oldpath, $matches)) {
                        $newpath = '/uploads/r/'.$repoName.'/'.$matches[0];
                    } else {
                        continue;
                    }

                    if (!file_exists(sfConfig::get('sf_web_dir').$newpath)) {
                        if (!mkdir(sfConfig::get('sf_web_dir').$newpath, 0775, true)) {
                            continue;
                        }
                    }

                    if (file_exists(sfConfig::get('sf_web_dir').$oldpath)) {
                        if (!rename(sfConfig::get('sf_web_dir').$oldpath.$item2['name'], sfConfig::get('sf_web_dir').$newpath.$item2['name'])) {
                            continue; // If rename fails, don't update path
                        }
                    }

                    // Delete old dirs, if they are empty
                    QubitDigitalObject::pruneEmptyDirs(sfConfig::get('sf_web_dir').$oldpath);

                    // Update path in yaml file
                    $item2['path'] = $newpath;
                }
            }
        }

        return $this;
    }

    /**
     * Add default value for repository.upload_limit column.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addRepostioryUploadLimit()
    {
        foreach ($this->data['QubitRepository'] as $key => &$item) {
            $item['upload_limit'] = -1; // Unlimited
        }

        return $this;
    }

    /**
     * Add physical object menu.
     *
     * @return QubitMigrate110 SELF
     */
    protected function addPhysicalObjectBrowseMenu()
    {
        $menu = [
            'parent_id' => '<?php echo QubitMenu::MANAGE_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'browsePhysicalObjects',
            'label' => [
                'en' => 'Physical storage',
                'es' => 'Almacenamiento físico',
                'fr' => 'Localisation physique',
                'nl' => 'Bergplaats',
                'pl' => 'Składowanie w ujęciu fizycznym',
                'sl' => 'Fizična hramba',
            ],
            'path' => 'physicalobject/browse',
        ];

        // Introduce Physical objects menu before Rights-holders
        if ($pivotKey = $this->findRowKeyForColumnValue($this->data['QubitMenu'], 'name', 'rightsholders')) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_manage_physicalobjects' => $menu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_manage_physicalobjects'] = $menu;
        }

        return $this;
    }

    /**
     * Migrate to sfCaribou theme to users that are currently using sfClassic.
     *
     * @return QubitMigrate110 SELF
     */
    protected function switchFromClassicToCaribouTheme()
    {
        $plugin = 'sfClassicPlugin';
        $replacement = 'sfCaribouPlugin';

        // Find setting
        foreach ($this->data['QubitSetting'] as $key => $value) {
            if ('plugins' == $value['name']) {
                $settings = unserialize($value['value'][$value['source_culture']]);

                // Find plugin
                if (-1 < ($index = array_search($plugin, $settings))) {
                    // Replace
                    $settings[$index] = $replacement;
                    $this->data['QubitSetting'][$key]['value'][$value['source_culture']] = serialize($settings);
                }

                break;
            }
        }

        return $this;
    }

    /**
     * Ensure all information objects get an explicit publication status.
     *
     * @return QubitMigrate110 SELF
     */
    protected function ensurePublicationStatus()
    {
        foreach ($this->data['QubitInformationObject'] as $key => &$item) {
            // Ignore ROOT
            if ('<?php echo QubitInformationObject::ROOT_ID."\n" ?>' == $item['id']) {
                continue;
            }

            // Don't do anything if this node already has a publication status
            if ($this->findRowKeyForColumnValue($this->data['QubitStatus'], 'object_id', $key)
        || $this->findRowKeyForColumnValue($this->data['QubitStatus'], 'object_id', $item['id'])) {
                continue;
            }

            // Search up hierarchy for ancestor with status
            $inheritedStatusKey = null;
            $node = $item;

            while (!isset($inheritedStatusKey)) {
                $parentKey = $node['parent_id'];
                $statusKey = $this->findRowKeyForColumnValue($this->data['QubitStatus'], 'object_id', $parentKey);

                if ($statusKey) {
                    $inheritedStatusKey = $statusKey;
                } else {
                    $node = $this->data['QubitInformationObject'][$parentKey];
                }

                if (null == $parentKey) {
                    break;
                }
            }

            $this->data['QubitStatus']['QubitStatus_'.$key] = [
                'object_id' => $key,
                'type_id' => $this->data['QubitStatus'][$inheritedStatusKey]['type_id'],
                'status_id' => $this->data['QubitStatus'][$inheritedStatusKey]['status_id'],
            ];
        }

        return $this;
    }

    /**
     * Slugs are inserted when some resources are inserted, but slugs are dumped
     * separately when data is dumped. So loading slug data will try to insert
     * duplicate slugs. To work around this, turn slugs into resource properties
     * and drop slug data.
     */
    protected function slugData()
    {
        if (!isset($this->data['QubitSlug'])) {
            return $this;
        }

        $slug = [];
        foreach ($this->data['QubitSlug'] as $item) {
            $slug[$item['object_id']] = $item['slug'];
        }

        unset($this->data['QubitSlug']);

        foreach ($this->data as $table => $value) {
            foreach ($value as $row => $value) {
                if (isset($slug[$row])) {
                    $this->data[$table][$row]['slug'] = $slug[$row];
                }
            }
        }

        return $this;
    }

    /**
     * Set repository.parent_id to null.
     *
     * All repositories were made children of the QubitActor root object from
     * Revision 8603 onward, but this causes a foreign key error on data-load
     * (issue 2041)
     *
     * @return self
     */
    protected function removeRepositoryRoot()
    {
        foreach ($this->data['QubitRepository'] as $key => &$item) {
            if (isset($item['parent_id'])) {
                unset($item['parent_id']);
            }
        }

        return $this;
    }

    /**
     * Call all sort methods.
     *
     * @return QubitMigrate110 this object
     */
    protected function sortData()
    {
        // Sort objects within classes
        $this->sortQubitInformationObjects();
        $this->sortQubitTerms();

        // Sort classes
        $this->sortClasses();

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate110 this object
     */
    protected function sortQubitInformationObjects()
    {
        QubitMigrate::sortByLft($this->data['QubitInformationObject']);

        return $this;
    }

    /**
     * Sort term objects with pre-defined IDs to start of array to prevent
     * pre-emptive assignment by auto-increment.
     *
     * @return QubitMigrate110 this object
     */
    protected function sortQubitTerms()
    {
        $qubitTermConstantIds = [
            'ROOT_ID',
            // EventType taxonomy
            'CREATION_ID',
            'CUSTODY_ID',
            'PUBLICATION_ID',
            'CONTRIBUTION_ID',
            'COLLECTION_ID',
            'ACCUMULATION_ID',
            // NoteType taxonomy
            'TITLE_NOTE_ID',
            'PUBLICATION_NOTE_ID',
            'SOURCE_NOTE_ID',
            'SCOPE_NOTE_ID',
            'DISPLAY_NOTE_ID',
            'ARCHIVIST_NOTE_ID',
            'GENERAL_NOTE_ID',
            'OTHER_DESCRIPTIVE_DATA_ID',
            'MAINTENANCE_NOTE_ID',
            // CollectionType taxonomy
            'ARCHIVAL_MATERIAL_ID',
            'PUBLISHED_MATERIAL_ID',
            'ARTEFACT_MATERIAL_ID',
            // ActorEntityType taxonomy
            'CORPORATE_BODY_ID',
            'PERSON_ID',
            'FAMILY_ID',
            // OtherNameType taxonomy
            'FAMILY_NAME_FIRST_NAME_ID',
            // MediaType taxonomy
            'AUDIO_ID',
            'IMAGE_ID',
            'TEXT_ID',
            'VIDEO_ID',
            'OTHER_ID',
            // Digital Object Usage taxonomy
            'MASTER_ID',
            'REFERENCE_ID',
            'THUMBNAIL_ID',
            'COMPOUND_ID',
            // Physical Object Type taxonomy
            'LOCATION_ID',
            'CONTAINER_ID',
            'ARTEFACT_ID',
            // Relation Type taxonomy
            'HAS_PHYSICAL_OBJECT_ID',
            // Actor name type taxonomy
            'PARALLEL_FORM_OF_NAME_ID',
            'OTHER_FORM_OF_NAME_ID',
            // Actor relation type taxonomy
            'HIERARCHICAL_RELATION_ID',
            'TEMPORAL_RELATION_ID',
            'FAMILY_RELATION_ID',
            'ASSOCIATIVE_RELATION_ID',
            // Relation NOTE type taxonomy
            'RELATION_NOTE_DESCRIPTION_ID',
            'RELATION_NOTE_DATE_ID',
            // Term relation taxonomy
            'ALTERNATIVE_LABEL_ID',
            'TERM_RELATION_ASSOCIATIVE_ID',
            // Status types taxonomy
            'STATUS_TYPE_PUBLICATION_ID',
            // Publication status taxonomy
            'PUBLICATION_STATUS_DRAFT_ID',
            'PUBLICATION_STATUS_PUBLISHED_ID',
            // Name access point
            'NAME_ACCESS_POINT_ID',
            // ISDF relation type taxonomy
            'ISDF_HIERARCHICAL_RELATION_ID',
            'ISDF_TEMPORAL_RELATION_ID',
            'ISDF_ASSOCIATIVE_RELATION_ID',
            // ISAAR standardized form name
            'STANDARDIZED_FORM_OF_NAME_ID',
            'EXTERNAL_URI_ID',
            // Relation types
            'ACCESSION_ID',
            'RIGHT_ID',
            'DONOR_ID',
            // Rights basis
            'RIGHT_BASIS_COPYRIGHT_ID',
            'RIGHT_BASIS_LICENSE_ID',
            'RIGHT_BASIS_STATUTE_ID',
            'RIGHT_BASIS_POLICY_ID',
        ];

        // Restack array with Constant values at top
        $qubitTermArray = $this->data['QubitTerm'];
        foreach ($qubitTermConstantIds as $key => $constantName) {
            foreach ($qubitTermArray as $key => $term) {
                if (isset($term['id']) && $term['id'] == '<?php echo QubitTerm::'.$constantName.'."\n" ?>') {
                    $newTermArray[$key] = $term;
                    unset($qubitTermArray[$key]);

                    break;
                }
            }
        }

        // Sort remainder of array by lft values
        QubitMigrate::sortByLft($qubitTermArray);

        // Append remaining (variable id) terms to the end of the new array
        foreach ($qubitTermArray as $key => $term) {
            $newTermArray[$key] = $term;
        }

        $this->data['QubitTerm'] = $newTermArray;

        return $this;
    }

    /**
     * Sort ORM classes to avoid foreign key constraint failures on data load.
     *
     * @return QubitMigrate110 this object
     */
    protected function sortClasses()
    {
        $ormSortOrder = [
            'QubitTaxonomy',
            'QubitTerm',
            'QubitActor',
            'QubitRepository',
            'QubitInformationObject',
            'QubitDigitalObject',
            'QubitEvent',
            'QubitFunctionObject',
            'QubitPhysicalObject',
            'QubitStaticPage',
            'QubitUser',
            'QubitObjectTermRelation',
            'QubitOtherName',
            'QubitRelation',
            'QubitAclGroup',
            'QubitAclUserGroup',
            'QubitAclPermission',
            'QubitContactInformation',
            'QubitMenu',
            'QubitNote',
            'QubitOaiRepository',
            'QubitOaiHarvest',
            'QubitProperty',
            'QubitSetting',
        ];

        $originalData = $this->data;

        foreach ($ormSortOrder as $i => $className) {
            if (isset($originalData[$className])) {
                $sortedData[$className] = $originalData[$className];
                unset($originalData[$className]);
            }
        }

        // If their are classes in the original data that are not listed in the
        // ormSortOrder array then tack them on to the end of the sorted data
        if (count($originalData)) {
            foreach ($originalData as $className => $classData) {
                $sortedData[$className] = $classData;
            }
        }

        $this->data = $sortedData;

        return $this;
    }

    protected function getUniqueSlug($str)
    {
        $slug = $root = QubitSlug::slugify($str);

        for ($i = 0; $i < 100; ++$i) {
            foreach ($this->data['QubitRepository'] as $item) {
                if (isset($item['slug']) && $item['slug'] == $slug) {
                    $slug = $root.'-'.($i + 1);

                    break;
                }
            }

            // If $slug hasn't been incremented this pass
            if ($slug == $root || $slug == $root.'-'.$i) {
                return $slug;
            }
        }
    }
}
