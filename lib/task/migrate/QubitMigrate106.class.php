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
 * Upgrade qubit data from version 1.0.6 to 1.0.7 schema.
 *
 * @author     David Juhasz <david@artefactual.com
 */
class QubitMigrate106 extends QubitMigrate
{
    /**
     * Controller for calling methods to alter data.
     *
     * @return QubitMigrate106 this object
     */
    protected function alterData()
    {
        // Delete "stub" objects
        $this->deleteStubObjects();

        // Add new taxonomies
        $this->addModsResourceTaxonomyTerms();
        $this->addDcTypeTaxonomyTerms();

        // Alter qubit classes (methods ordered alphabetically)
        $this->alterQubitMenus();
        $this->alterQubitNotes();
        $this->alterQubitSettings();
        $this->alterQubitStaticPages();
        $this->alterQubitTerms();

        return $this;
    }

    /**
     * Call all sort methods.
     *
     * @return QubitMigrate106 this object
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
     * Add the 'MODS resource type' taxonomy and terms.
     *
     * @return QubitMigrate106 this object
     */
    protected function addModsResourceTaxonomyTerms()
    {
        // Add MODS resource type taxonomy
        $this->data['QubitTaxonomy']['QubitTaxonomy_mods_resource_type'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::MODS_RESOURCE_TYPE_ID."\n" ?>',
            'name' => ['en' => 'MODS Resource Type'],
            'note' => ['en' => 'Fixed values for the typeOfResource element as prescribed by the The Library of Congress\'\' \'\'Metadata Object Description Schema (MODS)\'\''],
        ];

        // Add MODS resource type terms
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_text'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'text'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_cartographic'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'cartographic'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_notated_music'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'notated music'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_sound_recording'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'sound recording'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_sound_recording_musical'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'sound recording - musical'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_sound_recording_nonmusical'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'sound recording - nonmusical'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_stillimage'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'still image'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_moving_image'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'moving image'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_three_dimensional_object'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'three dimensional object'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_software_multimedia'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'software, multimedia'],
        ];
        $this->data['QubitTerm']['QubitTerm_mods_resource_type_mixed_material'] = [
            'taxonomy_id' => 'QubitTaxonomy_mods_resource_type',
            'source_culture' => 'en',
            'name' => ['en' => 'mixed material'],
        ];

        return $this;
    }

    /**
     * Add the 'DC type' taxonomy and terms.
     *
     * @return QubitMigrate106 this object
     */
    protected function addDcTypeTaxonomyTerms()
    {
        // Add DC type taxonomy
        $this->data['QubitTaxonomy']['QubitTaxonomy_dc_type'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::DC_TYPE_ID."\n" ?>',
            'name' => ['en' => 'Dublin Core Types'],
            'note' => ['en' => 'Fixed values for the DC Type element as prescribed by the DCMI Type Vocabulary'],
        ];

        // Add DC type terms
        $this->data['QubitTerm']['QubitTerm_dc_type_collection'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'collection'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_dataset'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'dataset'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_event'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'event'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_image'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'image'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_interactive_resource'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'interactive resource'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_moving_image'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'moving image'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_physical_object'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'physical object'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_service'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'service'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_software'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'software'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_sound'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'sound'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_still_image'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'still image'],
        ];
        $this->data['QubitTerm']['QubitTerm_dc_type_text'] = [
            'taxonomy_id' => 'QubitTaxonomy_dc_type',
            'source_culture' => 'en',
            'name' => ['en' => 'text'],
        ];

        return $this;
    }

    /**
     * Alter QubitMenu data.
     *
     * @return QubitMigrate106 this object
     */
    protected function alterQubitMenus()
    {
        // Remove themes menu
        if ($themesMenuKey = $this->getRowKey('QubitMenu', 'name', 'themes')) {
            unset($this->data['QubitMenu'][$themesMenuKey]);
        }

        // Change name and label for plugins menu
        if ($pluginsMenuKey = $this->getRowKey('QubitMenu', 'name', 'plugins')) {
            $this->data['QubitMenu'][$pluginsMenuKey]['label']['en'] = 'Themes';

            // Add sub-menus for themes
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_plugins_list'] = [
                'parent_id' => $pluginsMenuKey,
                'name' => 'list',
                'path' => 'sfPluginAdminPlugin/index',
                'source_culture' => 'en',
                'label' => ['en' => 'list'],
            ];
            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_plugins_configure'] = [
                'parent_id' => $pluginsMenuKey,
                'name' => 'configure',
                'path' => 'sfThemePlugin/index',
                'source_culture' => 'en',
                'label' => ['en' => 'configure'],
            ];
        }

        // Move 'harvester' menu after 'themes' menu
        if ($harvesterMenuKey = $this->getRowKey('QubitMenu', 'name', 'harvester')) {
            $harvesterMenu = $this->data['QubitMenu'][$harvesterMenuKey];
            unset($this->data['QubitMenu'][$harvesterMenuKey]);

            $this->data['QubitMenu']['QubitMenu_mainmenu_admin_harvester'] = $harvesterMenu;
        }
    }

    /**
     * Alter QubitNote data.
     *
     * @return QubitMigrate106 this object
     */
    protected function alterQubitNotes()
    {
        // Add source notes (with Term constant)
        $this->data['QubitNote']['QubitNote_creationSource'] = [
            'object_id' => '<?php echo QubitTerm::CREATION_ID."\n" ?>',
            'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
            'source_culture' => 'en',
            'content' => ['en' => 'ISAD(G) 3.2.1, 3.1.3; DC 1.1 core element (Creator); Rules for Archival Description 1.4B'],
        ];
        $this->data['QubitNote']['QubitNote_custodySource'] = [
            'object_id' => '<?php echo QubitTerm::CUSTODY_ID."\n" ?>',
            'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
            'source_culture' => 'en',
            'content' => ['en' => 'Rules for Archival Description 1.7C'],
        ];
        $this->data['QubitNote']['QubitNote_publicationSource'] = [
            'object_id' => '<?php echo QubitTerm::PUBLICATION_ID."\n" ?>',
            'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
            'source_culture' => 'en',
            'content' => ['en' => 'DC 1.1 element (Publisher); Rules for Archival Description 1.4, 1.8B8'],
        ];
        $this->data['QubitNote']['QubitNote_contributionSource'] = [
            'object_id' => '<?php echo QubitTerm::CONTRIBUTION_ID."\n" ?>',
            'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
            'source_culture' => 'en',
            'content' => ['en' => 'DC 1.1 element (Contributor)'],
        ];
        $this->data['QubitNote']['QubitNote_collectionSource'] = [
            'object_id' => '<?php echo QubitTerm::COLLECTION_ID."\n" ?>',
            'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
            'source_culture' => 'en',
            'content' => ['en' => 'Rules for Archival Description 1.4A6, 1.8B8a'],
        ];
        $this->data['QubitNote']['QubitNote_accumulationSource'] = [
            'object_id' => '<?php echo QubitTerm::ACCUMULATION_ID."\n" ?>',
            'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
            'source_culture' => 'en',
            'content' => ['en' => 'ISAD(G) 3.1.3; Rules for Archival Description 1.4A6, 1.8B8a'],
        ];

        // Add Source notes with no Term constant
        if ($distributionTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Distribution'])) {
            $this->data['QubitNote']['QubitNote_distributionSource'] = [
                'object_id' => $distributionTermKey,
                'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
                'source_culture' => 'en',
                'content' => ['en' => 'Rules for Archival Description 1.4, 1.8B8'],
            ];
        }
        if ($broadcastingTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Broadcasting'])) {
            $this->data['QubitNote']['QubitNote_broadcastingSource'] = [
                'object_id' => $broadcastingTermKey,
                'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
                'source_culture' => 'en',
                'content' => ['en' => 'Rules for Archival Description 8.4F'],
            ];
        }
        if ($manufacturingTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Manufacturing'])) {
            $this->data['QubitNote']['QubitNote_manufacturingSource'] = [
                'object_id' => $manufacturingTermKey,
                'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
                'source_culture' => 'en',
                'content' => ['en' => 'Rules for Archival Description 1.4G'],
            ];
        }

        return $this;
    }

    /**
     * Alter QubitSetting data.
     *
     * @return QubitMigrate106 this object
     */
    protected function alterQubitSettings()
    {
        // Rename "site_title" setting -> "siteTitle"
        if ($siteTitleKey = $this->getRowKey('QubitSetting', 'name', 'site_title')) {
            $this->data['QubitSetting'][$siteTitleKey]['name'] = 'siteTitle';
            unset($this->data['QubitSetting'][$siteTitleKey]['scope']);

            // Set required value 'site title' to 'ICA-AtoM' if it currently has no value
            $sourceCulture = $this->data['QubitSetting'][$siteTitleKey]['source_culture'];
            if (!isset($this->data['QubitSetting'][$siteTitleKey]['value']) || 0 == strlen($this->data['QubitSetting'][$siteTitleKey]['value'][$sourceCulture])) {
                $this->data['QubitSetting'][$siteTitleKey]['value'][$sourceCulture] = 'ICA-AtoM';

                // Hide the title if one was not previously set, to avoid theme conflicts
                if ($toggleTitleKey = $this->getRowKey('QubitSetting', 'name', 'toggleTitle')) {
                    $this->data['QubitSetting'][$toggleTitleKey]['value'] = null;
                }
            }
        }

        // Rename "site_description" setting -> "siteDescription"
        if ($siteDescKey = $this->getRowKey('QubitSetting', 'name', 'site_description')) {
            $this->data['QubitSetting'][$siteDescKey]['name'] = 'siteDescription';
            unset($this->data['QubitSetting'][$siteDescKey]['scope']);
        }

        // Update version number
        if ($settingVersionKey = $this->getRowKey('QubitSetting', 'name', 'version')) {
            foreach ($this->data['QubitSetting'][$settingVersionKey]['value'] as $culture => $value) {
                $this->data['QubitSetting'][$settingVersionKey]['value'][$culture] = str_replace('1.0.6', '1.0.7', $value);
            }
        }

        return $this;
    }

    /**
     * Alter QubitStaticPage data.
     *
     * @return QubitMigrate106 this object
     */
    protected function alterQubitStaticPages()
    {
        // Update version number
        foreach ($this->data['QubitStaticPage'] as $key => $page) {
            if ('homepage' == $page['permalink'] || 'about' == $page['permalink']) {
                array_walk($this->data['QubitStaticPage'][$key]['content'], function (&$x) {
                    $x = str_replace('1.0.6', '1.0.7', $x);
                });
            }
        }

        return $this;
    }

    /**
     * Alter QubitTerm data.
     *
     * @return QubitMigrate106 this object
     */
    protected function alterQubitTerms()
    {
        // Get key for 'Event Type' taxonomy key
        $eventTypeTaxonomyKey = $this->getRowKey('QubitTaxonomy', 'id', '<?php echo QubitTaxonomy::EVENT_TYPE_ID."\n" ?>');

        // Get key for creation event type
        $creationEventTypeKey = $this->getTermKey('<?php echo QubitTerm::CREATION_ID."\n" ?>');

        // Add 'Reproduction' term
        if (false === $this->getRowKey('QubitTerm', 'name', ['en' => 'Reproduction'])) {
            $this->data['QubitTerm']['QubitTerm_reproduction'] = [
                'taxonomy_id' => 'QubitTaxonomy_10',
                'source_culture' => 'en',
                'name' => ['en' => 'Reproduction'],
            ];

            // Add reproduction source note
            $this->data['QubitNote']['QubitNote_reproductionSource'] = [
                'object_id' => 'QubitTerm_reproduction',
                'type_id' => '<?php echo QubitTerm::SOURCE_NOTE_ID."\n" ?>',
                'source_culture' => 'en',
                'content' => ['en' => 'Rules for Archival Description 1.4A5'],
            ];
        }

        // Delete author terms
        if (
            ($authoringTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Authoring']))
            && ($eventTypeTaxonomyKey == $this->data['QubitTerm'][$authoringTermKey]['taxonomy_id'])
        ) {
            unset($this->data['QubitTerm'][$authoringTermKey]);

            // Delete related note
            if ($authoringNoteKey = $this->getRowKey('QubitNote', 'object_id', $authoringTermKey)) {
                unset($this->data['QubitNote'][$authoringNoteKey]);
            }

            // Reassign any 'Authoring' events to 'Creation' events
            while ($authoringEventTypeKey = $this->getRowKey('QubitEvent', 'type_id', $authoringTermKey)) {
                $this->data['QubitEvent'][$authoringEventTypeKey]['type_id'] = $creationEventTypeKey;
            }
        }

        // Delete editing terms
        if (
            ($editingTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Editing']))
            && $eventTypeTaxonomyKey == $this->data['QubitTerm'][$editingTermKey]['taxonomy_id']
        ) {
            unset($this->data['QubitTerm'][$editingTermKey]);

            // Delete related note
            if ($editingNoteKey = $this->getRowKey('QubitNote', 'object_id', $editingTermKey)) {
                unset($this->data['QubitNote'][$editingNoteKey]);
            }

            // Reassign any 'Editing' events to 'Creation' events
            while ($editingEventTypeKey = $this->getRowKey('QubitEvent', 'type_id', $editingTermKey)) {
                $this->data['QubitEvent'][$editingEventTypeKey]['type_id'] = $creationEventTypeKey;
            }
        }

        // Delete translation terms
        if (
            ($translationTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Translation']))
            && $eventTypeTaxonomyKey == $this->data['QubitTerm'][$translationTermKey]['taxonomy_id']
        ) {
            unset($this->data['QubitTerm'][$translationTermKey]);

            // Delete related note
            if ($translationNoteKey = $this->getRowKey('QubitNote', 'object_id', $translationTermKey)) {
                unset($this->data['QubitNote'][$translationNoteKey]);
            }

            // Reassign any 'Translation' events to 'Creation' events
            while ($translationEventTypeKey = $this->getRowKey('QubitEvent', 'type_id', $translationTermKey)) {
                $this->data['QubitEvent'][$translationEventTypeKey]['type_id'] = $creationEventTypeKey;
            }
        }

        // Delete compilation terms
        if (
            ($compilationTermKey = $this->getRowKey('QubitTerm', 'name', ['en' => 'Compilation']))
            && $eventTypeTaxonomyKey == $this->data['QubitTerm'][$compilationTermKey]['taxonomy_id']
        ) {
            unset($this->data['QubitTerm'][$compilationTermKey]);

            // Delete related note
            if ($compilationNoteKey = $this->getRowKey('QubitNote', 'object_id', $compilationTermKey)) {
                unset($this->data['QubitNote'][$compilationNoteKey]);
            }

            // Reassign any 'Compilation' events to 'Creation' events
            while ($compilationEventTypeKey = $this->getRowKey('QubitEvent', 'type_id', $compilationTermKey)) {
                $this->data['QubitEvent'][$compilationEventTypeKey]['type_id'] = $creationEventTypeKey;
            }
        }

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate106 this object
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
     * @return QubitMigrate106 this object
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
     * @return QubitMigrate106 this object
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
} // Close class QubitMigrate106
