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
 * Upgrade qubit data from version 1.0.5 to 1.0.6 schema.
 *
 * @author     David Juhasz <david@artefactual.com
 */
class QubitMigrate105 extends QubitMigrate
{
    /**
     * Controller for calling methods to alter data.
     *
     * @return QubitMigrate105 this object
     */
    protected function alterData()
    {
        // Delete "stub" objects
        $this->deleteStubObjects();

        // Alter qubit classes (methods ordered alphabetically)
        $this->alterQubitMenus();
        $this->alterQubitSettings();
        $this->alterQubitStaticPages();
        $this->alterQubitTerms();

        return $this;
    }

    /**
     * Call all sort methods.
     *
     * @return QubitMigrate105 this object
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
     * Alter QubitMenu data.
     *
     * @return QubitMigrate105 this object
     */
    protected function alterQubitMenus()
    {
        // Add 'recent updates' menu
        if (false === $this->getRowKey('QubitMenu', 'name', 'recent updates')) {
            $this->data['QubitMenu']['QubitMenu_recent_updates'] = [
                'parent_id' => '<?php echo QubitMenu::ADD_EDIT_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => 'recent updates',
                'label' => ['en' => 'recent updates'],
                'path' => 'search/recentUpdates',
            ];
        }

        // Add 'harvester' menu
        if (false === $this->getRowKey('QubitMenu', 'name', 'harvester')) {
            $this->data['QubitMenu']['QubitMenu_admin_oaiHarvester'] = [
                'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => 'harvester',
                'label' => ['en' => 'Harvester'],
                'path' => 'oai/harvesterList',
            ];
        }

        // Add 'plugins' menu
        if (false === $this->getRowKey('QubitMenu', 'name', 'plugins')) {
            $this->data['QubitMenu']['QubitMenu_admin_plugins'] = [
                'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => 'plugins',
                'label' => ['en' => 'Plugins'],
                'path' => 'sfPluginAdminPlugin/index',
            ];
        }

        // Add 'themes' menu
        if (false === $this->getRowKey('QubitMenu', 'name', 'themes')) {
            $this->data['QubitMenu']['QubitMenu_admin_themes'] = [
                'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => 'themes',
                'label' => ['en' => 'Themes'],
                'path' => 'sfThemePlugin/index',
            ];
        }

        // Remove "translate" menu
        $translateMenuKey = $this->getRowKey('QubitMenu', 'name', 'translate');
        if ($translateMenuKey) {
            $this->data['QubitMenu'] = QubitMigrate::cascadeDelete($this->data['QubitMenu'], $translateMenuKey);
        }

        // Pluralize 'Menus' menu label
        if (false !== $adminMenusMenuKey = $this->getRowKey('QubitMenu', 'label', ['en' => 'menu'])) {
            $this->data['QubitMenu'][$adminMenusMenuKey]['label']['en'] = 'menus';
        } elseif (false !== $adminMenusMenuKey = $this->getRowKey('QubitMenu', 'label', ['en' => 'Menu'])) {
            $this->data['QubitMenu'][$adminMenusMenuKey]['label']['en'] = 'Menus';
        }

        return $this;
    }

    /**
     * Alter QubitSetting data.
     *
     * @return QubitMigrate105 this object
     */
    protected function alterQubitSettings()
    {
        if (false === $this->getRowKey('QubitSetting', 'name', 'toggleDescription')) {
            $this->data['QubitSetting']['QubitSetting_toggleDescription'] = [
                'name' => 'toggleDescription',
                'value' => 1,
            ];
        }

        if (false === $this->getRowKey('QubitSetting', 'name', 'toggleLogo')) {
            $this->data['QubitSetting']['QubitSetting_toggleLogo'] = [
                'name' => 'toggleLogo',
                'value' => 1,
            ];
        }

        if (false === $this->getRowKey('QubitSetting', 'name', 'toggleTitle')) {
            $this->data['QubitSetting']['QubitSetting_toggleTitle'] = [
                'name' => 'toggleTitle',
                'value' => 1,
            ];
        }

        // Update version number
        if ($settingVersionKey = $this->getRowKey('QubitSetting', 'name', 'version')) {
            foreach ($this->data['QubitSetting'][$settingVersionKey]['value'] as $culture => $value) {
                $this->data['QubitSetting'][$settingVersionKey]['value'][$culture] = str_replace('1.0.5', '1.0.6', $value);
            }
        }

        return $this;
    }

    /**
     * Alter QubitStaticPage data.
     *
     * @return QubitMigrate105 this object
     */
    protected function alterQubitStaticPages()
    {
        // Update version number
        foreach ($this->data['QubitStaticPage'] as $key => $page) {
            if ('homepage' == $page['permalink'] || 'about' == $page['permalink']) {
                array_walk($this->data['QubitStaticPage'][$key]['content'], function (&$x) {
                    $x = str_replace('1.0.5', '1.0.6', $x);
                });
            }
        }

        return $this;
    }

    /**
     * Alter QubitTerm data.
     *
     * @return QubitMigrate105 this object
     */
    protected function alterQubitTerms()
    {
        // Get "Actor name type" taxonomy key
        $taxonomyActorNameTypeKey = $this->getRowKey('QubitTaxonomy', 'id', '<?php echo QubitTaxonomy::ACTOR_NAME_TYPE_ID."\n" ?>');

        // Make "parallel form of name" a protected term
        $termParallelFormKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Parallel form']);
        if ($termParallelFormKey) {
            $this->data['QubitTerm'][$termParallelFormKey]['id'] = '<?php echo QubitTerm::PARALLEL_FORM_OF_NAME_ID."\n" ?>';
        } else {
            $this->data['QubitTerm']['QubitTerm_parallel_form_of_name'] = [
                'taxonomy_id' => $taxonomyActorNameTypeKey,
                'class_name' => 'QubitTerm',
                'id' => '<?php echo QubitTerm::PARALLEL_FORM_OF_NAME_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => ['en' => 'Parallel form', 'es' => 'Forma paralela', 'fr' => 'Forme parall̬le', 'it' => 'Forma parallela', 'nl' => 'Parallelle naam', 'pt' => 'Forma paralela', 'sl' => 'Vzporedna oblika'],
            ];
        }

        // Make "other form of name" a protected term
        $termOtherFormKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Other name']);
        if ($termParallelFormKey) {
            $this->data['QubitTerm'][$termOtherFormKey]['id'] = '<?php echo QubitTerm::OTHER_FORM_OF_NAME_ID."\n" ?>';
        } else {
            $this->data['QubitTerm']['QubitTerm_other_form_of_name'] = [
                'taxonomy_id' => $taxonomyActorNameTypeKey,
                'class_name' => 'QubitTerm',
                'id' => '<?php echo QubitTerm::OTHER_FORM_OF_NAME_ID."\n" ?>',
                'source_culture' => 'en',
                'name' => ['en' => 'Other name', 'es' => 'Outra forma do nome', 'fr' => 'Autre nom', 'it' => 'Altro nome', 'nl' => 'Andere naam', 'pt' => 'Outra forma do nome', 'sl' => 'Drugo ime'],
            ];
        }

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate105 this object
     */
    protected function sortQubitInformationObjects()
    {
        QubitMigrate::sortByLft($this->data['QubitInformationObject']);

        return $this;
    }

    /**
     * Sort term objects with pre-defined IDs to start of array to prevent
     * pre-emptive assignment IDs by auto-increment.
     *
     * @return QubitMigrate105 this object
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
            // Actor name type taxonomy
            'PARALLEL_FORM_OF_NAME_ID',
            'OTHER_FORM_OF_NAME_ID',
        ];

        // Restack array with Constant values at top
        $qubitTermArray = $this->data['QubitTerm'];
        foreach ($qubitTermConstantIds as $key => $constantName) {
            foreach ($qubitTermArray as $key => $term) {
                if (isset($term['id']) && '<?php echo QubitTerm::'.$constantName.'."\n" ?>' == $term['id']) {
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
     * @return QubitMigrate105 this object
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
} // Close class QubitMigrate105
