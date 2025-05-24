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
 * Upgrade qubit data from version 1.0.8 to 1.0.9 schema.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitMigrate108 extends QubitMigrate
{
    public const MILESTONE = '1.0.8';
    public const INIT_VERSION = 0;
    public const FINAL_VERSION = 39;

    /**
     * Controller for calling methods to alter data.
     *
     * @return QubitMigrate108 this object
     */
    protected function alterData()
    {
        switch ($this->version) {
            default:
                $this->updateStaticPageVersionNumber();

                // no break
            case 1:
                $this->addIsdfFunctionTypes();

                // no break
            case 2:
                $this->moveActorNameToOtherName();

                // no break
            case 3:
                // Enable new plugins
                $plugins = ['sfDcPlugin', 'sfEadPlugin', 'sfIsadPlugin', 'sfModsPlugin', 'sfRadPlugin'];

                // Find setting
                $found = false;
                foreach ($this->data['QubitSetting'] as $key => $value) {
                    if ('plugins' == $value['name']) {
                        // Found setting, add new plugins
                        $found = true;
                        $this->data['QubitSetting'][$key]['value'][$value['source_culture']] = serialize(array_unique(array_merge(unserialize($value['value'][$value['source_culture']]), $plugins)));

                        break;
                    }
                }

                if (!$found) {
                    // No setting, add one
                    $value = [];
                    $value['name'] = 'plugins';
                    $value['source_culture'] = 'en';
                    $value['value']['en'] = serialize($plugins);

                    $this->data['QubitSetting'][rand()] = $value;
                }

                // no break
            case 4:
                $this->dropImportDigitalObjectMenu();

                // no break
            case 5:
                $this->alterMenuRecentUpdatesToFunctions();

                // no break
            case 6:
                // Enable classic theme
                $plugins = ['sfClassicPlugin'];

                // Find setting
                $found = false;
                foreach ($this->data['QubitSetting'] as $key => $value) {
                    if ('plugins' == $value['name']) {
                        // Found setting, add new plugins
                        $found = true;
                        $this->data['QubitSetting'][$key]['value'][$value['source_culture']] = serialize(array_unique(array_merge(unserialize($value['value'][$value['source_culture']]), $plugins)));

                        break;
                    }
                }

                if (!$found) {
                    // No setting, add one
                    $value = [];
                    $value['name'] = 'plugins';
                    $value['source_culture'] = 'en';
                    $value['value']['en'] = serialize($plugins);

                    $this->data['QubitSetting'][rand()] = $value;
                }

                // no break
            case 7:
                $this->addIsdfRelationTypeTaxonomy();

                // no break
            case 8:
                $this->singleToMultiRepositoryType();

                // no break
            case 9:
                $this->addStandardizedNameConstant();

                // no break
            case 10:
                $this->changeFunctionsTaxonomyName();

                // no break
            case 11:
                $this->dropAclActionTable();

                // no break
            case 12:
                $this->promoteGroupsMenu();

                // no break
            case 13:
                $this->addUserSubmenus();

                // no break
            case 14:
                $this->updateTranslatorAcl();

                // no break
            case 15:
                $this->dropRoleTables();

                // no break
            case 16:
                $this->addRootActorAndAdopt();

                // no break
            case 17:
                $this->addActorPermissions();

                // no break
            case 18:
                $this->addActorAclMenu();

                // no break
            case 19:
                $this->rootAuthenticatedGroup();

                // no break
            case 20:
                $this->addGroupSubmenus();

                // no break
            case 21:
                $this->addExternalUriConstant();

                // no break
            case 22:
                $this->addDigitalObjectPermissions();

                // no break
            case 23:
                $this->addTermAclMenus();

                // no break
            case 24:
                $this->rootTaxonomies();

                // no break
            case 25:
                $this->simplifyAdminAcl();

                // no break
            case 26:
                $this->simplifyReadAcl();

                // no break
            case 27:
                $this->addTermPermissions();

                // no break
            case 28:
                $this->changeAddEditMenuToAdd();

                // no break
            case 29:
                $this->changeShowPathToIndex();

                // no break
            case 30:
                $this->changeThemesMenuToPlugins();

                // no break
            case 31:
                $this->removeThemesConfigureMenuOptions();

                // no break
            case 32:
                $this->addTaxonomyToMainMenu();

                // no break
            case 33:
                $this->addBrowseMenu();

                // no break
            case 34:
                $this->ensureCompoundRepTerm();

                // no break
            case 35:
                $this->removeRepoAndUserParent();

                // no break
            case 36:
                $this->camelCaseMenuNames();

                // no break
            case 37:
                $this->switchFromClassicToCaribouTheme();

                // no break
            case 38:
                $this->updateEnMenuLabels();
        }

        // Delete "stub" object
        $this->deleteStubObjects();

        return $this;
    }

    /**
     * Call all sort methods.
     *
     * @return QubitMigrate108 this object
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
     * Ver 3: Change table name from QubitActorName to QubitOtherName and column
     * actor_id -> object_id.
     *
     * @return QubitMigrate108 this object
     */
    protected function moveActorNameToOtherName()
    {
        if (isset($this->data['QubitActorName'])) {
            foreach ($this->data['QubitActorName'] as $key => $actorName) {
                $newKey = str_replace('QubitActorName', 'QubitOtherName', $key);
                $otherNames[$newKey] = $actorName;

                // Change actor_id foreign key -> object_id
                $otherNames[$newKey]['object_id'] = $actorName['actor_id'];
                unset($otherNames[$newKey]['actor_id']);
            }

            // Insert 'QubitOtherName' in same position in data as 'QubitActorName'
            $insertPos = array_search('QubitActorName', array_keys($this->data));
            QubitMigrate::array_insert($this->data, $insertPos, ['QubitOtherName' => $otherNames]);

            // Remove QubitActorName array
            unset($this->data['QubitActorName']);
        }

        return $this;
    }

    /**
     * Ver 2: Add ISDF function types to 'Function' taxonomy.
     *
     * @return QubitMigrate108 this object
     */
    protected function addIsdfFunctionTypes()
    {
        $this->data['QubitTerm']['QubitTerm_function_function'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::FUNCTION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'de' => 'Funktion',
                'en' => 'Function',
                'es' => 'Funcion',
                'fr' => 'Fonction',
            ],
        ];
        $this->data['QubitTerm']['QubitTerm_function_Subfunction'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::FUNCTION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => ['en' => 'Subfunction'],
        ];
        $this->data['QubitTerm']['QubitTerm_function_Business_process'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::FUNCTION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => ['en' => 'Business process'],
        ];
        $this->data['QubitTerm']['QubitTerm_function_Activity'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::FUNCTION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => ['en' => 'Activity'],
        ];
        $this->data['QubitTerm']['QubitTerm_function_Task'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::FUNCTION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => ['en' => 'Task'],
        ];
        $this->data['QubitTerm']['QubitTerm_function_Transaction'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::FUNCTION_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => ['en' => 'Transaction'],
        ];

        return $this;
    }

    /**
     * Ver 5: Drop the "Import digital object" menu.
     *
     * @return QubitMigrate108 this object
     */
    protected function dropImportDigitalObjectMenu()
    {
        // Remove 'import digital objects' menu
        $importDigitalObjectMenuKey = $this->getRowKey('QubitMenu', 'name', 'import digital objects');
        if ($importDigitalObjectMenuKey) {
            $this->data['QubitMenu'] = QubitMigrate::cascadeDelete($this->data['QubitMenu'], $importDigitalObjectMenuKey);
        }

        return $this;
    }

    /**
     * Ver 6: Swap 'recent updates' menu for 'functions' menu.
     *
     * @return QubitMigrate108 this object
     */
    protected function alterMenuRecentUpdatesToFunctions()
    {
        // Remove 'recent updates' menu
        $recentUpdatesKey = $this->getRowKey('QubitMenu', 'name', 'recent updates');
        if ($recentUpdatesKey) {
            $this->data['QubitMenu'] = QubitMigrate::cascadeDelete($this->data['QubitMenu'], $recentUpdatesKey);
        }

        // Add 'functions' menu
        $this->data['QubitMenu']['QubitMenu_addedit_function'] = [
            'parent_id' => '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'functions',
            'label' => ['en' => 'Functions'],
            'path' => 'function/list',
        ];

        return $this;
    }

    /**
     * Ver 8: Add ISDF relation type taxonomy.
     *
     * @return QubitMigrate108 this object
     */
    protected function addIsdfRelationTypeTaxonomy()
    {
        // Add taxonomy row
        $this->data['QubitTaxonomy']['QubitTaxonomy_isdf_relation_type'] = [
            'id' => '<?php echo QubitTaxonomy::ISDF_RELATION_TYPE_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'en' => 'ISDF Relation Type',
            ],
            'note' => [
                'en' => 'Function-to-function relationship categories defined by the ICA ISDF specification, 1st Edition, Section 5.3.3, \'Category of relationship\'.',
            ],
        ];

        // Add related terms
        $this->data['QubitTerm']['QubitTerm_isdf_relationship_hierarchical'] = [
            'id' => '<?php echo QubitTerm::ISDF_HIERARCHICAL_RELATION_ID."\n" ?>',
            'taxonomy_id' => '<?php echo QubitTaxonomy::ISDF_RELATION_TYPE_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'en' => 'hierarchical',
            ],
        ];
        $this->data['QubitTerm']['QubitTerm_isdf_relationship_temporal'] = [
            'id' => '<?php echo QubitTerm::ISDF_TEMPORAL_RELATION_ID."\n" ?>',
            'taxonomy_id' => '<?php echo QubitTaxonomy::ISDF_RELATION_TYPE_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'en' => 'temporal',
            ],
        ];
        $this->data['QubitTerm']['QubitTerm_isdf_relationship_associative'] = [
            'id' => '<?php echo QubitTerm::ISDF_ASSOCIATIVE_RELATION_ID."\n" ?>',
            'taxonomy_id' => '<?php echo QubitTaxonomy::ISDF_RELATION_TYPE_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'en' => 'associative',
            ],
        ];

        return $this;
    }

    /**
     * Ver 9: Convert repository 'type_id' field to a QubitObjectTermRelation.
     *
     * @return QubitMigrate108 this object
     */
    protected function singleToMultiRepositoryType()
    {
        foreach ($this->data['QubitRepository'] as $key => $row) {
            if (isset($row['type_id'])) {
                $this->data['QubitObjectTermRelation'][rand()] = [
                    'object_id' => $key,
                    'term_id' => $row['type_id'],
                ];
            }

            unset($this->data['QubitRepository'][$key]['type_id']);
        }

        return $this;
    }

    /**
     * Ver 10: Give "Standarized form of name" a constant id.
     *
     * @return QubitMigrate108 this object
     */
    protected function addStandardizedNameConstant()
    {
        $key = $this->getRowKey('QubitTerm', 'name', ['en' => 'Standardized form']);
        if ($key) {
            $this->data['QubitTerm'][$key]['id'] = '<?php echo QubitTerm::STANDARDIZED_FORM_OF_NAME_ID."\n" ?>';
        }
    }

    /**
     * Ver 11: Change "Functions" taxonomy name to "ISDF Function Types".
     *
     * @return QubitMigrate108 this object
     */
    protected function changeFunctionsTaxonomyName()
    {
        $key = $this->getRowKey('QubitTaxonomy', 'name', ['en' => 'Functions']);
        if ($key) {
            $this->data['QubitTaxonomy'][$key]['name'] = [
                'de' => 'ISDF Funktionen',
                'en' => 'ISDF Function Types',
                'es' => 'ISDF Tipos de Funcion',
                'fr' => 'ISDF Types de Fonction',
                'it' => 'ISDF Tipi di Funzion',
                'nl' => 'ISDF Functie Soorten',
                'pt' => 'ISDF Tipos de Funções',
                'sl' => 'ISDF Tip Funkcija',
            ];
        }
    }

    /**
     * Ver 12: Drop acl_action and acl_action_i18n tables and migrate action
     * constants to plain strings.
     *
     * @return QubitMigrate108 this object
     */
    protected function dropAclActionTable()
    {
        // Replace QubitAclAction::ACTION_ID constant with string
        $pattern = '/QubitAclAction::([A-Z_]+)_ID/';

        if (isset($this->data['QubitAclPermission'])) {
            foreach ($this->data['QubitAclPermission'] as $key => $row) {
                if (0 < preg_match($pattern, $row['action_id'], $matches)) {
                    // Direct match in acl_permission.action_id column
                    $action = strtolower($matches[1]);
                } elseif (isset($this->data['QubitAclAction'][$row['action_id']]) && 0 < preg_match($pattern, $this->data['QubitAclAction'][$row['action_id']]['id'], $matches)) {
                    // Follow action_id row alias to QubitAclAction row, and match constant
                    $action = strtolower($matches[1]);
                } else {
                    continue;
                }

                // Switch 'view_draft' string to camelCase
                if ('view_draft' == $action) {
                    $action = 'viewDraft';
                }

                // Change name of `action_id` column to `action`
                $this->data['QubitAclPermission'][$key]['action'] = $action;
                unset($this->data['QubitAclPermission'][$key]['action_id']);
            }
        }

        // Drop QubitAclAction table data
        unset($this->data['QubitAclAction']);

        return $this;
    }

    /**
     * Ver 13: Promote 'groups' menu to child of 'admin'.
     *
     * @return QubitMigrate108 this object
     */
    protected function promoteGroupsMenu()
    {
        // Try to find existing 'Groups' menu
        if ($groupMenuKey = $this->getRowKey('QubitMenu', 'name', 'groups')) {
            $groupMenu = $this->data['QubitMenu'][$groupMenuKey];
            $groupMenu['parent_id'] = '<?php echo QubitMenu::ADMIN_ID."\n" ?>';
        } else {
            // If 'Groups' menu doesn't exist already then create it
            $groupMenu = [
                'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
                'name' => 'groups',
                'path' => 'aclGroup/list',
                'source_culture' => 'en',
                'label' => ['en' => 'Groups'],
            ];
        }

        // Remove all children of 'User' menu.  This cleans up repeated 'user'
        // sub-menus from r3510
        $userMenuKey = null;
        foreach ($this->data['QubitMenu'] as $key => $row) {
            if (null != $userMenuKey) {
                $nextKey = $key;

                break;
            }
            if (isset($row['name']) && 'users' == $row['name']) {
                // Find the 'users' menu that is a child of the admin menu
                if (
                    strpos($row['parent_id'], 'QubitMenu::ADMIN_ID')
                    || isset($this->data['QubitMenu'][$row['parent_id']])
                    && strpos($this->data['QubitMenu'][$row['parent_id']]['id'], 'QubitMenu::ADMIN_ID')
                ) {
                    $userMenuKey = $key;

                    // Delete all children of user menu (users and groups)
                    foreach ($this->data['QubitMenu'] as $key2 => $item) {
                        if (isset($item['parent_id']) && $userMenuKey == $item['parent_id']) {
                            unset($this->data['QubitMenu'][$key2]);
                        }
                    }
                }
            }
        }

        // Insert 'Groups' menu right after 'Users' menu
        if (isset($nextKey)) {
            QubitMigrate::insertBeforeNestedSet($this->data['QubitMenu'], $nextKey, ['QubitMenu_mainmenu_admin_groups' => $groupMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_users_users'] = $groupMenu;
        }

        return $this;
    }

    /**
     * Ver 14: Add user sub-menus 'profile' and 'archival description
     * permissions'.
     *
     * @return QubitMigrate108 this object
     */
    protected function addUserSubmenus()
    {
        // Find existing 'Users' menu
        if ($key = $this->getRowKey('QubitMenu', 'name', 'users')) {
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_users_profile'] = [
                'parent_id' => $key,
                'name' => 'userProfile',
                'path' => 'user/show?id=%currentId%',
                'source_culture' => 'en',
                'label' => [
                    'en' => 'Profile',
                    'fr' => 'Profil',
                ],
            ];

            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_users_infoObjectAcl'] = [
                'parent_id' => $key,
                'name' => 'userInformationObjectAcl',
                'path' => 'user/showInformationObjectAcl?id=%currentId%',
                'source_culture' => 'en',
                'label' => [
                    'en' => 'Information object permissions',
                ],
            ];
        }

        return $this;
    }

    /**
     * Ver 15: Change translator 'update' permission to 'translate'.
     *
     * @return QubitMigrate108 this object
     */
    protected function updateTranslatorAcl()
    {
        // Remove 'update' permission for translators
        if ($translatorGroupKey = $this->getRowKey('QubitAclGroup', 'id', '<?php echo QubitAclGroup::TRANSLATOR_ID."\n" ?>')) {
            foreach ($this->data['QubitAclPermission'] as $key => $row) {
                if ($translatorGroupKey == $row['group_id'] && 'update' == $row['action']) {
                    unset($this->data['QubitAclPermission'][$key]);
                }
            }
        }

        // Add translate permission
        $this->data['QubitAclPermission']['QubitAclPermission_translator_translate'] = [
            'group_id' => '<?php echo QubitAclGroup::TRANSLATOR_ID."\n" ?>',
            'action' => 'translate',
            'grant_deny' => 1,
        ];
    }

    /**
     * Ver 16: Drop QubitRole and QubitUserRoleRelation table data.
     *
     * @return QubitMigrate108 this object
     */
    protected function dropRoleTables()
    {
        if (isset($this->data['QubitRole'])) {
            unset($this->data['QubitRole']);
        }

        if (isset($this->data['QubitUserRoleRelation'])) {
            unset($this->data['QubitUserRoleRelation']);
        }

        return $this;
    }

    /**
     * Ver 17: Add actor root object and adopt all existing actors.
     *
     * @return QubitMigrate108 this object
     */
    protected function addRootActorAndAdopt()
    {
        // Make root actor the parent of all existing actors
        foreach ($this->data['QubitActor'] as $key => $row) {
            $this->data['QubitActor'][$key]['parent_id'] = 'QubitActor_ROOT';
        }

        // Add root actor
        $newActor['QubitActor_ROOT']['id'] = '<?php echo QubitActor::ROOT_ID."\n" ?>';
        $this->data['QubitActor'] = $newActor + $this->data['QubitActor'];

        return $this;
    }

    /**
     * Ver 18: Add actor permissions to default groups.
     *
     * @return QubitMigrate108 this object
     */
    protected function addActorPermissions()
    {
        // Editor permissions (grant read, create, update & delete)
        $this->data['QubitAclPermission']['QubitAclPermission_'.rand()] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitActor::ROOT_ID."\n" ?>',
            'action' => 'create',
            'grant_deny' => 1,
        ];
        $this->data['QubitAclPermission']['QubitAclPermission_'.rand()] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitActor::ROOT_ID."\n" ?>',
            'action' => 'update',
            'grant_deny' => 1,
        ];
        $this->data['QubitAclPermission']['QubitAclPermission_'.rand()] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitActor::ROOT_ID."\n" ?>',
            'action' => 'delete',
            'grant_deny' => 1,
        ];

        // Contributor permissions (grant create & update)
        $this->data['QubitAclPermission']['QubitAclPermission_'.rand()] = [
            'group_id' => '<?php echo QubitAclGroup::CONTRIBUTOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitActor::ROOT_ID."\n" ?>',
            'action' => 'create',
            'grant_deny' => 1,
        ];
        $this->data['QubitAclPermission']['QubitAclPermission_'.rand()] = [
            'group_id' => '<?php echo QubitAclGroup::CONTRIBUTOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitActor::ROOT_ID."\n" ?>',
            'action' => 'update',
            'grant_deny' => 1,
        ];

        return $this;
    }

    /**
     * Ver 19: Add menu for acl permissions to user page.
     *
     * @return QubitMigrate108 this object
     */
    protected function addActorAclMenu()
    {
        if ($userKey = $this->getRowKey('QubitMenu', 'name', 'users')) {
            $this->data['QubitMenu']['QubitMenu_actor_acl'] = [
                'parent_id' => $userKey,
                'name' => 'userActorAcl',
                'path' => 'user/showActorAcl?id=%currentId%',
                'source_culture' => 'en',
                'label' => ['en' => 'Actor permissions'],
            ];
        }

        return $this;
    }

    /**
     * Ver 20: Re-parent authenticated group to ROOT instead of inheriting from
     * the anonymous group.
     *
     * @return QubitMigrate108 this object
     */
    protected function rootAuthenticatedGroup()
    {
        if ($key = $this->getRowKey('QubitAclGroup', 'id', '<?php echo QubitAclGroup::AUTHENTICATED_ID."\n" ?>')) {
            $this->data['QubitAclGroup'][$key]['parent_id'] = '<?php echo QubitAclGroup::ROOT_ID."\n" ?>';
        }

        return $this;
    }

    /**
     * Ver 21: Add group "tab" sub-menus.
     *
     * @return QubitMigrate108 this object
     */
    protected function addGroupSubmenus()
    {
        // Find existing 'Groups' menu
        if ($key = $this->getRowKey('QubitMenu', 'name', 'groups')) {
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_groups_profile'] = [
                'parent_id' => $key,
                'name' => 'groupProfile',
                'path' => 'aclGroup/show?id=%currentId%',
                'source_culture' => 'en',
                'label' => [
                    'en' => 'Profile',
                    'fr' => 'Profil',
                ],
            ];

            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_groups_infoObjectAcl'] = [
                'parent_id' => $key,
                'name' => 'groupInformationObjectAcl',
                'path' => 'aclGroup/showInformationObjectAcl?id=%currentId%',
                'source_culture' => 'en',
                'label' => [
                    'en' => 'Information object permissions',
                ],
            ];

            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_groups_actorAcl'] = [
                'parent_id' => $key,
                'name' => 'groupActorAcl',
                'path' => 'aclGroup/showActorAcl?id=%currentId%',
                'source_culture' => 'en',
                'label' => ['en' => 'Actor permissions'],
            ];
        }

        return $this;
    }

    /**
     * Ver 22: Give "External URI" a constant id.
     *
     * @return QubitMigrate108 this object
     */
    protected function addExternalUriConstant()
    {
        $this->data['QubitTerm'][rand()] = [
            'id' => '<?php echo QubitTerm::EXTERNAL_URI_ID."\n" ?>',
            'taxonomyId' => '<?php echo QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID."\n" ?>',
            'parent_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'es' => 'URI externo',
                'en' => 'External URI',
            ],
        ];

        return $this;
    }

    /**
     * Ver 23: Add readMaster and readReference permissions to default groups.
     *
     * @return QubitMigrate108 this object
     */
    protected function addDigitalObjectPermissions()
    {
        // Anonymous - grant readReference
        $this->data['QubitAclPermission']['QubitAclPermission_anonymous_readReference'] = [
            'group_id' => '<?php echo QubitAclGroup::ANONYMOUS_ID."\n" ?>',
            'object_id' => '<?php echo QubitInformationObject::ROOT_ID."\n" ?>',
            'action' => 'readReference',
            'grant_deny' => 1,
        ];

        // Authenticated - grant readReference
        $this->data['QubitAclPermission']['QubitAclPermission_authenticated_readReference'] = [
            'group_id' => '<?php echo QubitAclGroup::AUTHENTICATED_ID."\n" ?>',
            'object_id' => '<?php echo QubitInformationObject::ROOT_ID."\n" ?>',
            'action' => 'readReference',
            'grant_deny' => 1,
        ];

        // Editor - grant readMaster
        $this->data['QubitAclPermission']['QubitAclPermission_editor_readMaster'] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitInformationObject::ROOT_ID."\n" ?>',
            'action' => 'readMaster',
            'grant_deny' => 1,
        ];

        // Contributor - grant readMaster
        $this->data['QubitAclPermission']['QubitAclPermission_contributor_readMaster'] = [
            'group_id' => '<?php echo QubitAclGroup::CONTRIBUTOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitInformationObject::ROOT_ID."\n" ?>',
            'action' => 'readMaster',
            'grant_deny' => 1,
        ];

        return $this;
    }

    /**
     * Ver 24: Add menus for term ACL.
     *
     * @return QubitMigrate108 this object
     */
    protected function addTermAclMenus()
    {
        // Add as child of 'users' menu
        if ($key = $this->getRowKey('QubitMenu', 'name', 'users')) {
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_user_termAcl'] = [
                'parent_id' => $key,
                'name' => 'userTermAcl',
                'path' => 'user/showTermAcl?id=%currentId%',
                'source_culture' => 'en',
                'label' => [
                    'en' => 'Taxonomy permissions',
                ],
            ];
        }

        // Add as child of 'groups' menu
        if ($key = $this->getRowKey('QubitMenu', 'name', 'groups')) {
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_groups_termAcl'] = [
                'parent_id' => $key,
                'name' => 'userTermAcl',
                'path' => 'aclGroup/showTermAcl?id=%currentId%',
                'source_culture' => 'en',
                'label' => [
                    'en' => 'Taxonomy permissions',
                ],
            ];
        }

        return $this;
    }

    /**
     * Ver 25: Make all taxonomy objects children of taxonomy root.
     *
     * @return QubitMigrate108 this object
     */
    protected function rootTaxonomies()
    {
        foreach ($this->data['QubitTaxonomy'] as $key => $row) {
            // Mark position of ROOT taxonomy
            if ('<?php echo QubitTaxonomy::ROOT_ID."\n" ?>' == $row['id']) {
                $rootKey = $key;

                continue; // Don't assign parent
            }

            $this->data['QubitTaxonomy'][$key]['parent_id'] = '<?php echo QubitTaxonomy::ROOT_ID."\n" ?>';
        }

        // Move taxonomy ROOT to top of taxonomy list
        if (isset($rootKey)) {
            $root[$rootKey] = $this->data['QubitTaxonomy'][$rootKey];
            unset($this->data['QubitTaxonomy'][$rootKey]);

            $this->data['QubitTaxonomy'] = $root + $this->data['QubitTaxonomy'];
        }
    }

    /**
     * Ver 26: Remove multiple admin rules and add single global 'grant'.
     *
     * @return QubitMigrate108 this object
     */
    protected function simplifyAdminAcl()
    {
        // Delete existing admin permissions
        foreach ($this->data['QubitAclPermission'] as $key => $row) {
            $group_id = $row['group_id'];

            // If group_id points to a QubitAclGroup row, then use `id` column from
            // that row
            if (isset($this->data['QubitAclGroup'][$row['group_id']])) {
                $group_id = $this->data['QubitAclGroup'][$row['group_id']]['id'];
            }

            if (
                '<?php echo QubitAclGroup::ADMINISTRATOR_ID."\n" ?>' == $group_id
                || '<?php echo QubitAclGroup::ADMIN_ID."\n" ?>' == $group_id
            ) {
                unset($this->data['QubitAclPermission'][$key]);
            }
        }

        // Define single "allow all" rule
        $this->data['QubitAclPermission']['QubitAclPermission_admin_all'] = [
            'group_id' => '<?php echo QubitAclGroup::ADMINISTRATOR_ID."\n" ?>',
            'grant_deny' => 1,
        ];

        return $this;
    }

    /**
     * Ver 27: Remove multiple read acl rules and add single grant.
     *
     * @return QubitMigrate108 this object
     */
    protected function simplifyReadAcl()
    {
        // Delete existing admin permissions
        foreach ($this->data['QubitAclPermission'] as $key => $row) {
            if (isset($row['action']) && 'read' == $row['action']) {
                unset($this->data['QubitAclPermission'][$key]);
            }
        }

        // Define single "allow read" rule for authenticated and anonymous groups
        $this->data['QubitAclPermission']['QubitAclPermission_authenticated_read_all'] = [
            'group_id' => '<?php echo QubitAclGroup::AUTHENTICATED_ID."\n" ?>',
            'action' => 'read',
            'grant_deny' => 1,
        ];
        $this->data['QubitAclPermission']['QubitAclPermission_anonymous_read_all'] = [
            'group_id' => '<?php echo QubitAclGroup::ANONYMOUS_ID."\n" ?>',
            'action' => 'read',
            'grant_deny' => 1,
        ];

        return $this;
    }

    /**
     * Ver 28: Add default term permissions.
     *
     * @return QubitMigrate108 this object
     */
    protected function addTermPermissions()
    {
        // Editor - grant term permissions (update & delete)
        $this->data['QubitAclPermission']['QubitAclPermission_editor_term_update'] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'action' => 'update',
            'grant_deny' => 1,
        ];
        $this->data['QubitAclPermission']['QubitAclPermission_editor_term_delete'] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitTerm::ROOT_ID."\n" ?>',
            'action' => 'delete',
            'grant_deny' => 1,
        ];

        // Editor - grant createTerm permission for all taxonomies
        $this->data['QubitAclPermission']['QubitAclPermission_editor_createTerm'] = [
            'group_id' => '<?php echo QubitAclGroup::EDITOR_ID."\n" ?>',
            'object_id' => '<?php echo QubitTaxonomy::ROOT_ID."\n" ?>',
            'action' => 'createTerm',
            'grant_deny' => 1,
        ];

        return $this;
    }

    /**
     * Ver 29: Change add/edit menu to "add" menu, like revision 6151.
     *
     * @return QubitMigrate108 this object
     */
    protected function changeAddEditMenuToAdd()
    {
        if ($addMenuKey = $this->getRowKey('QubitMenu', 'id', '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>')) {
            // Change add/edit menu name, label and path
            $this->data['QubitMenu'][$addMenuKey]['name'] = 'add';
            $this->data['QubitMenu'][$addMenuKey]['path'] = 'informationobject/create';
            $this->data['QubitMenu'][$addMenuKey]['label'] = [
                'de' => 'Hinzufügen',
                'en' => 'Add',
                'es' => 'agregar',
                'fa' => 'افزودن',
                'fr' => 'ajouter',
                'it' => 'aggiungi',
                'nl' => 'toevoegen',
                'pt' => 'adicionar',
                'sl' => 'uporabniški',
            ];

            // Switch from linking list pages to linking create pages
            foreach ($this->data['QubitMenu'] as $key => $row) {
                if (isset($row['parent_id']) && ($addMenuKey == $row['parent_id'] || '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>' == $row['parent_id'])) {
                    $this->data['QubitMenu'][$key]['path'] = str_replace('list', 'create', $row['path']);
                }
            }
        }

        return $this;
    }

    /**
     * Ver 30: Change 'show' paths to 'index' paths.
     *
     * @return QubitMigrate108 this object
     */
    protected function changeShowPathToIndex()
    {
        foreach ($this->data['QubitMenu'] as $key => $row) {
            if (isset($row['path']) && false !== strpos($row['path'], 'show')) {
                $this->data['QubitMenu'][$key]['path'] = str_replace('show', 'index', $row['path']);
            }
        }

        return $this;
    }

    /**
     * Ver 31: Change 'Admin -> Themes' menu to 'Admin -> Plugins', like r6320.
     *
     * @return QubitMigrate108 this object
     */
    protected function changeThemesMenuToPlugins()
    {
        // Try to find existing 'Themes' menu
        if (
            ($pluginMenuKey = $this->getRowKey('QubitMenu', 'name', 'themes'))
            || ($pluginMenuKey = $this->getRowKey('QubitMenu', 'name', 'plugins'))
        ) {
            $this->data['QubitMenu'][$pluginMenuKey]['name'] = 'plugins';
            $this->data['QubitMenu'][$pluginMenuKey]['label'] = ['en' => 'Plugins'];
        }

        return $this;
    }

    /**
     * Ver 32: Remove Themes 'List | Configure' menu option. Has been moved to
     * Settings -> Default Page Elements.
     *
     * @return QubitMigrate108 this object
     */
    protected function removeThemesConfigureMenuOptions()
    {
        $pluginsListKey = $this->getRowKey('QubitMenu', 'name', 'list');
        if ($pluginsListKey) {
            $this->data['QubitMenu'] = QubitMigrate::cascadeDelete($this->data['QubitMenu'], $pluginsListKey);
        }

        $themesConfigureKey = $this->getRowKey('QubitMenu', 'name', 'configure');
        if ($themesConfigureKey) {
            $this->data['QubitMenu'] = QubitMigrate::cascadeDelete($this->data['QubitMenu'], $themesConfigureKey);
        }

        return $this;
    }

    /**
     * Ver 33: Add taxonomy link to main menu.
     *
     * @return QubitMigrate108 this object
     */
    protected function addTaxonomyToMainMenu()
    {
        $taxonomyMenu = [
            'id' => '<?php echo QubitMenu::TAXONOMY_ID."\n" ?>',
            'parent_id' => '<?php echo QubitMenu::MAIN_MENU_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'taxonomies',
            'label' => [
                'en' => 'Taxonomies',
            ],
            'path' => 'term/list',
        ];

        if (
            ($pivotKey = $this->getRowKey('QubitMenu', 'id', '<?php echo QubitMenu::IMPORT_ID."\n" ?>'))
            || ($pivotKey = $this->getRowKey('QubitMenu', 'id', '<?php echo QubitMenu::TRANSLATE_ID."\n" ?>'))
            || ($pivotKey = $this->getRowKey('QubitMenu', 'id', '<?php echo QubitMenu::ADMIN_ID."\n" ?>'))
        ) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $pivotKey, ['QubitMenu_mainmenu_taxonomy' => $taxonomyMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_mainmenu_taxonomy'] = $taxonomyMenu;
        }

        return $this;
    }

    /**
     * Ver 34: Add browse menu, like r6431, r6432 & r6440.
     *
     * @return QubitMigrate108 this object
     */
    protected function addBrowseMenu()
    {
        // Add parent menu
        $browseMenu = [
            'id' => '<?php echo QubitMenu::BROWSE_ID."\n" ?>',
            'parent_id' => '<?php echo QubitMenu::ROOT_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'browse',
            'label' => [
                'en' => 'Browse',
            ],
        ];

        if ($addEditKey = $this->getRowKey('QubitMenu', 'id', '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>')) {
            self::insertBeforeNestedSet($this->data['QubitMenu'], $addEditKey, ['QubitMenu_browse' => $browseMenu]);
        } else {
            $this->data['QubitMenu']['QubitMenu_browse'] = $browseMenu;
        }

        // Add sub-menus
        $this->data['QubitMenu']['QubitMenu_browse_informationobjects'] = [
            'parent_id' => '<?php echo QubitMenu::BROWSE_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'browseInformationObjects',
            'label' => [
                'en' => 'Information objects',
            ],
            'path' => 'informationobject/browse',
        ];

        $this->data['QubitMenu']['QubitMenu_browse_actors'] = [
            'parent_id' => 'QubitMenu_browse',
            'source_culture' => 'en',
            'name' => 'browseActors',
            'label' => [
                'en' => 'Actors',
            ],
            'path' => 'actor/browse',
        ];

        $this->data['QubitMenu']['QubitMenu_browse_repositories'] = [
            'parent_id' => 'QubitMenu_browse',
            'source_culture' => 'en',
            'name' => 'browseRepositories',
            'label' => [
                'en' => 'Repositories',
            ],
            'path' => 'repository/browse',
        ];

        $this->data['QubitMenu']['QubitMenu_browse_functions'] = [
            'parent_id' => 'QubitMenu_browse',
            'source_culture' => 'en',
            'name' => 'browseFunctions',
            'label' => [
                'en' => 'Functions',
            ],
            'path' => 'function/browse',
        ];

        $this->data['QubitMenu']['QubitMenu_browse_subjects'] = [
            'parent_id' => 'QubitMenu_browse',
            'source_culture' => 'en',
            'name' => 'browseSubjects',
            'label' => [
                'en' => 'Subjects',
            ],
            'path' => 'term/browseTaxonomy?id=<?php echo QubitTaxonomy::SUBJECT_ID ?>',
        ];

        $this->data['QubitMenu']['QubitMenu_browse_places'] = [
            'parent_id' => 'QubitMenu_browse',
            'source_culture' => 'en',
            'name' => 'browsePlaces',
            'label' => [
                'en' => 'Places',
            ],
            'path' => 'term/browseTaxonomy?id=<?php echo QubitTaxonomy::PLACE_ID ?>',
        ];

        $this->data['QubitMenu']['QubitMenu_browse_digital_objects'] = [
            'parent_id' => 'QubitMenu_browse',
            'source_culture' => 'en',
            'name' => 'browseDigitalObjects',
            'label' => [
                'en' => 'Digital objects',
            ],
            'path' => 'digitalobject/list',
        ];

        return $this;
    }

    /**
     * Ver 35: Ensure that COMPOUND_ID term is added to data migrated from
     * release 1.0.4 before revision 6459.
     *
     * @return QubitMigrate108 this object
     */
    protected function ensureCompoundRepTerm()
    {
        if (!$this->getRowKey('QubitTerm', 'id', '<?php echo QubitTerm::COMPOUND_ID."\n" ?>')) {
            $this->data['QubitTerm']['QubitTerm_compound_id'] = [
                'taxonomy_id' => '<?php echo QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID."\n" ?>',
                'class_name' => 'QubitTerm',
                'id' => '<?php echo QubitTerm::COMPOUND_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => [
                    'en' => 'Compound representation',
                ],
            ];
        }

        return $this;
    }

    /**
     * Ver 36: Remove Actor::ROOT_ID parent from QubitRepository and QubitUser
     * rows (See https://projects.artefactual.com/issues/3459).
     *
     * @return QubitMigrate108 this object
     */
    protected function removeRepoAndUserParent()
    {
        foreach (['QubitRepository', 'QubitUser'] as $class) {
            if (isset($this->data[$class]) && 0 < count($this->data[$class])) {
                foreach ($this->data[$class] as $key => $row) {
                    if (
                        isset($row['parent_id']) && (QubitActor::ROOT_ID == $row['parent_id']
                        || '<?php echo QubitActor::ROOT_ID."\n" ?>' == $this->data['QubitActor'][$row['parent_id']]['id'])
                    ) {
                        unset($this->data[$class][$key]['parent_id']);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Ver 37: Update names of "add" menus, like r6787.
     *
     * @return QubitMigrate108 this object
     */
    protected function camelCaseMenuNames()
    {
        foreach ($this->data['QubitMenu'] as $key => $row) {
            if (!isset($row['name'])) {
                continue;
            }

            switch ($name = $row['name']) {
                case 'information object':
                    $name = 'addInformationObject';

                    break;

                case 'actor':
                    $name = 'addActor';

                    break;

                case 'repository':
                    $name = 'addRepository';

                    break;

                case 'term':
                    $name = 'addTerm';

                    break;

                case 'functions':
                    $name = 'addFunction';

                    break;

                case 'log in':
                    $name = 'login';

                    break;

                default:
                    if (false !== strpos($name, ' ')) {
                        $name = strtolower(trim($row['name']));
                        $name = preg_replace_callback('/ (.)/', function ($matches) {
                            return strtoupper($matches[1]);
                        }, $name);
                    }
            }

            $this->data['QubitMenu'][$key]['name'] = $name;
        }

        return $this;
    }

    /**
     * Ver 38: Migrate to sfCaribou theme to users that are currently using sfClassic.
     *
     * @return QubitMigrate108 this object
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
     * Ver 39: Update menu labels to match data fixtures as of r6802 (English
     * only).
     *
     * @return QubitMigrate108 this object
     */
    protected function updateEnMenuLabels()
    {
        foreach ($this->data['QubitMenu'] as $key => $row) {
            if (!isset($row['label']) || !isset($row['label']['en'])) {
                continue;
            }

            switch ($label = strtolower($row['label']['en'])) {
                case 'information object':
                    $label = 'Information objects';

                    break;

                case 'person/organization':
                    $label = 'Persons/organizations';

                    break;

                case 'repository':
                    $label = 'Repositories';

                    break;

                case 'term':
                    $label = 'Terms';

                    break;

                case 'xml':
                    $label = 'XML';

                    break;

                case 'oai':
                    $label = 'OAI';

                    break;

                default:
                    $label = preg_replace_callback('/^(\w)/', function ($matches) {
                        return strtoupper($matches[1]);
                    }, $label);
            }

            $this->data['QubitMenu'][$key]['label']['en'] = $label;
        }

        return $this;
    }

    /**
     * Ver 1: Update version number in static pages.
     *
     * @return QubitMigrate108 this object
     */
    protected function updateStaticPageVersionNumber()
    {
        // Update version number
        foreach ($this->data['QubitStaticPage'] as $key => $page) {
            if ('homepage' == $page['permalink'] || 'about' == $page['permalink']) {
                array_walk($this->data['QubitStaticPage'][$key]['content'], function (&$x) {
                    $x = preg_replace('/1\.0\.8(\.1)?/', '1.0.9', $x);
                });
            }
        }

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate108 this object
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
     * @return QubitMigrate108 this object
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
            'EXTERNAL_URI_ID',
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
            'RELATION_NOTE_DATE_DISPLAY_ID',
            // Term relation taxonomy
            'TERM_RELATION_EQUIVALENCE_ID',
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
     * @return QubitMigrate108 this object
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
}
