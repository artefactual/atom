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
 * Upgrade qubit data from version 1.0.4 to 1.0.5 schema.
 *
 * @author     David Juhasz <david@artefactual.com
 */
class QubitMigrate104 extends QubitMigrate
{
    /**
     * Controller for calling methods to alter data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterData()
    {
        // Delete "stub" objects
        $this->deleteStubObjects();

        // Alter qubit classes (methods ordered alphabetically)
        $this->alterQubitInformationObjects();
        $this->alterQubitMenus();
        $this->alterQubitRoles();
        $this->alterQubitSettings();
        $this->alterQubitStaticPages();
        $this->alterQubitTerms();

        return $this;
    }

    /**
     * Call all sort methods.
     *
     * @return QubitMigrate104 this object
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
     * Alter QubitInformationObject data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterQubitInformationObjects()
    {
        // Initialize oai_identifier auto-increment column with root Info Object
        if ($rootInfoObjectKey = $this->getRowKey('QubitInformationObject', 'lft', '1')) {
            $this->data['QubitInformationObject'][$rootInfoObjectKey]['oai_local_identifier'] = '10001';
        }

        return $this;
    }

    /**
     * Alter QubitRoles data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterQubitRoles()
    {
        // Add constant ids for Qubit Roles
        foreach ($this->data['QubitRole'] as $key => $role) {
            switch ($role['name']) {
                case 'administrator':
                    $this->data['QubitRole'][$key]['id'] = '<?php echo QubitRole::ADMINISTRATOR_ID."\n" ?>';

                    break;

                case 'editor':
                    $this->data['QubitRole'][$key]['id'] = '<?php echo QubitRole::EDITOR_ID."\n" ?>';

                    break;

                case 'contributor':
                    $this->data['QubitRole'][$key]['id'] = '<?php echo QubitRole::CONTRIBUTOR_ID."\n" ?>';

                    break;

                case 'translator':
                    $this->data['QubitRole'][$key]['id'] = '<?php echo QubitRole::TRANSLATOR_ID."\n" ?>';

                    break;
            }
        }

        return $this;
    }

    /**
     * Alter QubitSetting data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterQubitSettings()
    {
        // Add new settings
        $this->data['QubitSetting']['QubitSetting_oai_repository_code'] = [
            'name' => 'oai_repository_code',
            'scope' => 'oai',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => ''],
        ];
        $this->data['QubitSetting']['QubitSetting_resumption_token_limit'] = [
            'name' => 'resumption_token_limit',
            'scope' => 'oai',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => '100'],
        ];
        $this->data['QubitSetting']['QubitSetting_inherit_code_informationobject'] = [
            'name' => 'inherit_code_informationobject',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => '1'],
        ];

        // Replace full, localized language names with two letter ISO code for
        // enabled language settings
        foreach ($this->data['QubitSetting'] as $key => $setting) {
            if ('i18n_languages' == $setting['scope']) {
                $this->data['QubitSetting'][$key]['value'] = ['en' => $setting['name']];
            }
        }

        // Update version number
        if ($settingVersionKey = $this->getRowKey('QubitSetting', 'name', 'version')) {
            foreach ($this->data['QubitSetting'][$settingVersionKey]['value'] as $culture => $value) {
                $this->data['QubitSetting'][$settingVersionKey]['value'][$culture] = str_replace('1.0.4', '1.0.5', $value);
            }
        }

        return $this;
    }

    /**
     * Alter QubitStaticPage data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterQubitStaticPages()
    {
        // Update version number
        foreach ($this->data['QubitStaticPage'] as $key => $page) {
            if ('homepage' == $page['permalink'] || 'about' == $page['permalink']) {
                array_walk($this->data['QubitStaticPage'][$key]['content'], function (&$x) {
                    $x = str_replace('1.0.4', '1.0.5', $x);
                });
            }
        }

        return $this;
    }

    /**
     * Alter QubitMenu data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterQubitMenus()
    {
        $this->data['QubitMenu']['QubitMenu_root'] = [
            'id' => '<?php echo QubitMenu::ROOT_ID."\n" ?>',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu'] = [
            'id' => '<?php echo QubitMenu::MAIN_MENU_ID."\n" ?>',
            'parent_id' => 'QubitMenu_root',
            'source_culture' => 'en',
            'name' => 'Main menu',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks'] = [
            'id' => '<?php echo QubitMenu::QUICK_LINKS_ID."\n" ?>',
            'parent_id' => 'QubitMenu_root',
            'source_culture' => 'en',
            'name' => 'Quick links',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_addedit'] = [
            'id' => '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>',
            'parent_id' => 'QubitMenu_mainmenu',
            'source_culture' => 'en',
            'name' => 'add/edit',
            'label' => ['de' => 'Hinzufügen/Bearbeiten', 'en' => 'add/edit', 'es' => 'agregar/editar', 'fa' => 'افزودن/ويرايش', 'fr' => 'ajouter/modifier', 'it' => 'aggiungi/modifica', 'nl' => 'toevoegen/wijzigen', 'pt' => 'adicionar/editar', 'sl' => 'uporabniški vmesnik'],
            'path' => 'informationobject/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_importexport'] = [
            'id' => '<?php echo QubitMenu::IMPORT_EXPORT_ID."\n" ?>',
            'parent_id' => 'QubitMenu_mainmenu',
            'source_culture' => 'en',
            'name' => 'import/export',
            'label' => ['en' => 'import/export', 'es' => 'importar/exportar', 'fa' => 'وارد كردن/صادر كردن', 'fr' => 'importer/exporter', 'it' => 'importa/esporta', 'nl' => 'import/export', 'pt' => 'importar/exportar', 'sl' => 'uvoz/izvoz'],
            'path' => 'object/importexport',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_translate'] = [
            'id' => '<?php echo QubitMenu::TRANSLATE_ID."\n" ?>',
            'parent_id' => 'QubitMenu_mainmenu',
            'source_culture' => 'en',
            'name' => 'translate',
            'label' => ['de' => 'Übersetzen', 'en' => 'translate', 'es' => 'traducir', 'fa' => 'ترجمه', 'fr' => 'traduire', 'it' => 'traduci', 'nl' => 'vertalen', 'pt' => 'traduzir', 'sl' => 'prevedi'],
            'path' => 'i18n/listUserInterfaceTranslation',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_admin'] = [
            'id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
            'parent_id' => 'QubitMenu_mainmenu',
            'source_culture' => 'en',
            'name' => 'admin',
            'label' => ['de' => 'Administrator', 'en' => 'admin', 'es' => 'administrador', 'fa' => 'مدير', 'fr' => 'administrer', 'it' => 'amministra', 'sl' => 'administrator'],
            'path' => 'user/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_addedit_informationobject'] = [
            'parent_id' => 'QubitMenu_mainmenu_addedit',
            'source_culture' => 'en',
            'name' => 'information object',
            'label' => ['en' => 'information object', 'fr' => 'objet d\'information', 'it' => 'oggetto informativo', 'nl' => 'information object', 'pt' => 'objeto informacional', 'sl' => 'informacijski objekt'],
            'path' => 'informationobject/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_addedit_actor'] = [
            'parent_id' => 'QubitMenu_mainmenu_addedit',
            'source_culture' => 'en',
            'name' => 'actor',
            'label' => ['en' => 'person/organization', 'fr' => 'personne/organisation', 'it' => 'persona/organizzazione', 'nl' => 'persoon/organisatie', 'pt' => 'pessoa/organização', 'sl' => 'oseba/organizacija'],
            'path' => 'actor/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_addedit_repository'] = [
            'parent_id' => 'QubitMenu_mainmenu_addedit',
            'source_culture' => 'en',
            'name' => 'repository',
            'label' => ['en' => 'repository', 'fr' => 'service d\'archives', 'it' => 'soggetto conservatore', 'nl' => 'depot', 'pt' => 'repositório', 'sl' => 'skladišče'],
            'path' => 'repository/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_addedit_term'] = [
            'parent_id' => 'QubitMenu_mainmenu_addedit',
            'source_culture' => 'en',
            'name' => 'term',
            'label' => ['en' => 'term', 'fr' => 'terme', 'it' => 'termine', 'nl' => 'term', 'pt' => 'termo', 'sl' => 'izraz'],
            'path' => 'term/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_importexport_importexport'] = [
            'parent_id' => 'QubitMenu_mainmenu_importexport',
            'source_culture' => 'en',
            'name' => 'import/export',
            'label' => ['en' => 'import/export', 'es' => 'importar/exportar', 'fa' => 'وارد كردن/صادر كردن', 'fr' => 'importer/exporter', 'it' => 'importa/esporta', 'nl' => 'import/export', 'pt' => 'importar/exportar', 'sl' => 'uvoz/izvoz'],
            'path' => 'object/importexport',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_translate_userinterface'] = [
            'parent_id' => 'QubitMenu_mainmenu_translate',
            'source_culture' => 'en',
            'name' => 'user interface',
            'label' => ['en' => 'user interface', 'es' => 'interfaz del usuario', 'fa' => 'رابط كاربر', 'fr' => 'interface utilisateur', 'it' => 'interfaccia utente', 'nl' => 'gebruikersinterface', 'pt' => 'interface de usuário', 'sl' => 'uporabniški vmesnik'],
            'path' => 'i18n/listUserInterfaceTranslation',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_translate_defaultcontent'] = [
            'parent_id' => 'QubitMenu_mainmenu_translate',
            'source_culture' => 'en',
            'name' => 'default content',
            'label' => ['de' => 'Vorgegebener Inhalt', 'en' => 'default content', 'es' => 'contenidos por defecto', 'fa' => 'محتواي پيش فرض', 'fr' => 'contenu par défaut', 'it' => 'contenuto predefinito', 'nl' => 'standaard inhoud', 'pt' => 'conteúdo padrão', 'sl' => 'prednastavljena vsebina'],
            'path' => 'i18n/listDefaultContentTranslation',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_admin_users'] = [
            'parent_id' => 'QubitMenu_mainmenu_admin',
            'source_culture' => 'en',
            'name' => 'users',
            'label' => ['en' => 'users', 'es' => 'usuarios', 'fa' => 'كاربران', 'fr' => 'utilisateurs', 'it' => 'utenti', 'nl' => 'gebruikers', 'pt' => 'usuários', 'sl' => 'uporabniki'],
            'path' => 'user/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_admin_staticpages'] = [
            'parent_id' => 'QubitMenu_mainmenu_admin',
            'source_culture' => 'en',
            'name' => 'static pages',
            'label' => ['en' => 'static pages', 'es' => 'páginas estáticas', 'fa' => 'صفحات ايستا', 'fr' => 'pages statiques', 'it' => 'pagine statiche', 'nl' => 'statische pagina\'s', 'pt' => 'páginas estáticas', 'sl' => 'statična stran'],
            'path' => 'staticpage/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_admin_settings'] = [
            'parent_id' => 'QubitMenu_mainmenu_admin',
            'source_culture' => 'en',
            'name' => 'settings',
            'label' => ['de' => 'Einstellungen', 'en' => 'settings', 'es' => 'configuración', 'fa' => 'تنظيمات', 'fr' => 'paramètres', 'it' => 'impostazioni', 'nl' => 'instellingen', 'pt' => 'configurações', 'sl' => 'nastavitve'],
            'path' => 'settings/list',
        ];
        $this->data['QubitMenu']['QubitMenu_mainmenu_admin_menu'] = [
            'parent_id' => 'QubitMenu_mainmenu_admin',
            'source_culture' => 'en',
            'name' => 'menu',
            'label' => ['en' => 'menu'],
            'path' => 'menu/list',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks_home'] = [
            'parent_id' => 'QubitMenu_quicklinks',
            'source_culture' => 'en',
            'name' => 'home',
            'label' => ['en' => 'home', 'es' => 'inicio', 'fa' => 'صفحه اصلي', 'fr' => 'accueil', 'it' => 'pagina iniziale', 'nl' => 'home', 'pt' => 'inicio', 'sl' => 'domov'],
            'path' => '',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks_about'] = [
            'parent_id' => 'QubitMenu_quicklinks',
            'source_culture' => 'en',
            'name' => 'about',
            'label' => ['de' => 'Über', 'en' => 'about', 'es' => 'acerca', 'fa' => 'درباره ما', 'fr' => 'à propos', 'it' => 'informazioni su', 'nl' => 'over', 'pt' => 'sobre', 'sl' => 'o tem'],
            'path' => 'staticpage/static?permalink=about',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks_help'] = [
            'parent_id' => 'QubitMenu_quicklinks',
            'source_culture' => 'en',
            'name' => 'help',
            'label' => ['de' => 'Hilfe', 'en' => 'help', 'es' => 'ayuda', 'fa' => 'راهنما', 'fr' => 'aide', 'it' => 'aiuto', 'nl' => 'help', 'pt' => 'ajuda', 'sl' => 'pomoč'],
            'path' => 'http://accesstomemory.org',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks_myProfile'] = [
            'parent_id' => 'QubitMenu_quicklinks',
            'source_culture' => 'en',
            'name' => 'my profile',
            'label' => ['en' => 'my profile'],
            'path' => '%profile%',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks_login'] = [
            'parent_id' => 'QubitMenu_quicklinks',
            'source_culture' => 'en',
            'name' => 'log in',
            'label' => ['en' => 'log in', 'es' => 'iniciar sesión', 'fa' => 'ورود به سيستم', 'fr' => 'ouverture de session', 'it' => 'accesso', 'nl' => 'inloggen', 'pt' => 'entrar', 'sl' => 'prijava'],
            'path' => 'user/login',
        ];
        $this->data['QubitMenu']['QubitMenu_quicklinks_logout'] = [
            'parent_id' => 'QubitMenu_quicklinks',
            'source_culture' => 'en',
            'name' => 'logout',
            'label' => ['en' => 'log out', 'es' => 'cerrar sesión', 'fa' => 'خروج', 'fr' => 'fermeture de session', 'it' => 'esci', 'nl' => 'uitloggen', 'pt' => 'sair', 'sl' => 'izhod'],
            'path' => 'user/logout',
        ];

        return $this;
    }

    /**
     * Alter QubitTerm data.
     *
     * @return QubitMigrate104 this object
     */
    protected function alterQubitTerms()
    {
        // Get "Note" taxonomy key
        $taxonomyNoteTypeKey = $this->getRowKey('QubitTaxonomy', 'id', '<?php echo QubitTaxonomy::NOTE_TYPE_ID."\n" ?>');

        // Add "Other Descriptive Data" note type
        if ($taxonomyNoteTypeKey) {
            $this->data['QubitTerm']['QubitTerm_odd'] = [
                'taxonomy_id' => $taxonomyNoteTypeKey,
                'class_name' => 'QubitTerm',
                'id' => '<?php echo QubitTerm::OTHER_DESCRIPTIVE_DATA_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => ['en' => 'Other Descriptive Data'],
            ];
        }

        // Give archivist's note a constant id
        if ($archivistsNoteKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Archivist\'s note'])) {
            $this->data['QubitTerm'][$archivistsNoteKey]['id'] = '<?php echo QubitTerm::ARCHIVIST_NOTE_ID."\n" ?>';
        } elseif ($taxonomyNoteTypeKey) {
            $this->data['QubitTerm']['QubitTerm_archivist_note'] = [
                'taxonomy_id' => $taxonomyNoteTypeKey,
                'class_name' => 'QubitTerm',
                'id' => '<?php echo QubitTerm::ARCHIVIST_NOTE_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => ['en' => 'Archivist\'s note', 'fr' => 'Note de l\'archiviste', 'it' => 'Nota dell\'archivista', 'nl' => 'Verantwoording', 'pt' => 'Nota do arquivista', 'sl' => 'Opombe arhivista'],
            ];
        }

        // Give general note a constant id
        if ($generalNoteKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'General note'])) {
            $this->data['QubitTerm'][$generalNoteKey]['id'] = '<?php echo QubitTerm::GENERAL_NOTE_ID."\n" ?>';
        } elseif ($taxonomyNoteTypeKey) {
            $this->data['QubitTerm']['QubitTerm_general_note'] = [
                'taxonomy_id' => $taxonomyNoteTypeKey,
                'class_name' => 'QubitTerm',
                'id' => '<?php echo QubitTerm::GENERAL_NOTE_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => ['en' => 'General note', 'fr' => 'Note générale', 'it' => 'Nota generale', 'nl' => 'Algemene aantekening', 'pt' => 'Nota geral', 'sl' => 'Splošne opombe'],
            ];
        }

        // Remove Finding Aids Term
        if ($findingAidTermKey = $this->getRowKey('QubitTerm', 'id', '<?php echo QubitTerm::FINDING_AIDS_ID."\n" ?>')) {
            unset($this->data['QubitTerm'][$findingAidTermKey]);
        }

        // Add compound digital object usage_id term
        $this->data['QubitTerm']['QubitTerm_compound_id'] = [
            'taxonomy_id' => '<?php echo QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID."\n" ?>',
            'class_name' => 'QubitTerm',
            'id' => '<?php echo QubitTerm::COMPOUND_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => [
                'en' => 'Compound representation',
            ],
        ];

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate104 this object
     */
    protected function sortQubitInformationObjects()
    {
        $newList = [];
        $highLft = 0;
        foreach ($this->data['QubitInformationObject'] as $key => $row) {
            // If this left value is higher than any previous value, then just add
            // current row to the end of $newList
            if ($row['lft'] > $highLft) {
                $newList[$key] = $row;
                $highLft = $row['lft'];
            }

            // Else, find the right place in $newList to insert the current row
            // (sorted by lft values)
            else {
                $i = 0;
                foreach ($newList as $newKey => $newRow) {
                    if ($newRow['lft'] > $row['lft']) {
                        QubitMigrate::array_insert($newList, $i, [$key => $row]);

                        break;
                    }
                    ++$i;
                }
            }
        }

        $this->data['QubitInformationObject'] = $newList;
    }

    /**
     * Sort term objects with pre-defined IDs to start of array to prevent
     * pre-emptive assignment IDs by auto-increment.
     *
     * @return QubitMigrate104 this object
     */
    protected function sortQubitTerms()
    {
        $qubitTermConstantIds = [
            // EventType taxonomy
            'CREATION_ID',
            'SUBJECT_ID',
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
        ];

        // Restack array with Constant values at top
        $qubitTermArray = $this->data['QubitTerm'];
        foreach ($qubitTermConstantIds as $key => $constantName) {
            foreach ($qubitTermArray as $key => $term) {
                if ($term['id'] == '<?php echo QubitTerm::'.$constantName.'."\n" ?>') {
                    $newTermArray[$key] = $term;
                    unset($qubitTermArray[$key]);

                    break;
                }
            }
        }

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
     * @return QubitMigrate104 this object
     */
    protected function sortClasses()
    {
        $ormSortOrder = [
            'QubitTaxonomy',
            'QubitTerm',
            'QubitSetting',
            'QubitStaticPage',
            'QubitActor',
            'QubitUser',
            'QubitRole',
            'QubitUserRoleRelation',
            'QubitRepository',
            'QubitContactInformation',
            'QubitInformationObject',
            'QubitDigitalObject',
            'QubitPhysicalObject',
            'QubitEvent',
            'QubitObjectTermRelation',
            'QubitRelation',
            'QubitProperty',
            'QubitNote',
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
} // Close class QubitMigrate104
