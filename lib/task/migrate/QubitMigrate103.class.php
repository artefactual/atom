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
 * Upgrade qubit data model from 1.0.3 to 1.0.4.
 *
 * @author     David Juhasz <david@artefactual.com
 */
class QubitMigrate103 extends QubitMigrate
{
    protected $taxonomyActorRoleKey;

    /**
     * Controller for calling methods to alter data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterData()
    {
        // Delete stub object
        $this->deleteStubObjects();

        // Alter qubit classes (order is important!)
        $this->alterQubitActors();
        $this->alterQubitEvents();
        $this->alterQubitObjectTermRelations();
        $this->alterQubitProperties();
        $this->alterQubitStaticPages();
        $this->alterQubitSettings();
        $this->alterQubitTaxonomy();
        $this->alterQubitTerms();
        $this->alterQubitNotes();  // Must come after QubitTerms

        return $this;
    }

    /**
     * Call all sort methods.
     *
     * @return QubitMigrate103 this object
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
     * Alter QubitActor data.
     *
     * @return QubitMigrate103 $this object
     */
    protected function alterQubitActors()
    {
        // NOTE: 'dates_of_existence' data is added to QubitActor objects in
        // The alterQubitEvents() method.
    }

    /**
     * Alter QubitEvent data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitEvents()
    {
        // Delete QubitEvent objects that have BOTH no related info object
        // AND no related actor (either one is enough for the event to be valid)
        foreach ($this->data['QubitEvent'] as $key => $event) {
            if (!isset($event['information_object_id']) && !isset($event['actor_id'])) {
                unset($this->data['QubitEvent'][$key]);
            }
        }

        // Re-map data QubitEvent::description -> QubitEvent::date_display
        foreach ($this->data['QubitEvent'] as $key => $event) {
            if (isset($this->data['QubitEvent'][$key]['description'])) {
                $this->data['QubitEvent'][$key]['date_display'] = $this->data['QubitEvent'][$key]['description'];
                unset($this->data['QubitEvent'][$key]['description']);
            }
        }

        // Remove "existence" events and move existence info into actor table
        // dates_of_existence column
        $existenceTermKey = $this->getTermExistenceKey();
        foreach ($this->data['QubitEvent'] as $key => $columns) {
            if ($columns['type_id'] == $existenceTermKey && isset($columns['date_display'])) {
                $this->data['QubitActor'][$columns['actor_id']]['dates_of_existence'] = $columns['date_display'];
                unset($this->data['QubitEvent'][$key]);
            }
        }

        // Switch from assigning actor_role_id to event to determining actor role
        // based on event type (eg. event type = creation then actor role = creator)
        $oldSubjectKey = $this->getTermKey('<?php echo QubitTerm::SUBJECT_ID."\n" ?>');
        $creationEventTermKey = $this->getTermKey('<?php echo QubitTerm::CREATION_ID."\n" ?>');
        foreach ($this->data['QubitEvent'] as $key => $columns) {
            if (isset($columns['actor_role_id'])) {
                // If this was a subject access point relationship, then give the 1.0.4
                // event type_id = 'subject'
                if ($columns['actor_role_id'] == $oldSubjectKey && !isset($columns['type_id'])) {
                    $this->data['QubitEvent'][$key]['type_id'] = 'QubitTerm_subject';
                }

                // Remove actor_role_id column from existing events (deprecated)
                unset($this->data['QubitEvent'][$key]['actor_role_id']);
            }

            // Add event type_id of "creation" to events that don't have an
            // assigned type_id
            if (!isset($this->data['QubitEvent'][$key]['type_id'])) {
                $this->data['QubitEvent'][$key] = array_merge(
                    ['type_id' => $creationEventTermKey],
                    $this->data['QubitEvent'][$key]
                );
            }
        }

        // If there are no QubitEvent objects left, remove the section
        if ([] == $this->data['QubitEvent']) {
            unset($this->data['QubitEvent']);
        }

        return $this;
    }

    /**
     * Alter QubitProperty data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitProperties()
    {
        // re-map QubitProperty 'value' column to i18n table
        foreach ($this->data['QubitProperty'] as $key => $property) {
            if (isset($property['value'])) {
                $this->data['QubitProperty'][$key]['source_culture'] = 'en';
                $this->data['QubitProperty'][$key]['value'] = ['en' => $property['value']];
            }
        }

        return $this;
    }

    /**
     * Alter QubitStaticPage data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitStaticPages()
    {
        // Update version number
        foreach ($this->data['QubitStaticPage'] as $key => $page) {
            if ('homepage' == $page['permalink'] || 'about' == $page['permalink']) {
                array_walk($this->data['QubitStaticPage'][$key]['content'], function (&$x) {
                    $x = str_replace('1.0.3', '1.0.4', $x);
                });
            }
        }

        return $this;
    }

    /**
     * Alter QubitSetting data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitSettings()
    {
        // Remove old QubitSettings for default templates
        $i = 0;
        foreach ($this->data['QubitSetting'] as $key => $setting) {
            switch ($setting['name']) {
                case 'informationobject_edit':
                case 'informationobject_show':
                case 'informationobject_list':
                case 'actor_edit':
                case 'actor_show':
                case 'actor_list':
                case 'repository_edit':
                case 'repository_show':
                case 'repository_list':
                    if (!isset($defaultTemplateIndex)) {
                        $defaultTemplateIndex = $i;
                    }
                    unset($this->data['QubitSetting'][$key]);

                    break;
            }

            ++$i;
        }

        // Add new Default Template Qubit Settings (insert in place of previous
        // default template data
        $defaultTemplates['QubitSetting_default_template_informationobject'] = [
            'name' => 'informationobject',
            'scope' => 'default_template',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => 'isad'],
        ];
        $defaultTemplates['QubitSetting_default_template_actor'] = [
            'name' => 'actor',
            'scope' => 'default_template',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => 'isaar'],
        ];
        $defaultTemplates['QubitSetting_default_template_repository'] = [
            'name' => 'repository',
            'scope' => 'default_template',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => 'isdiah'],
        ];
        QubitMigrate::array_insert($this->data['QubitSetting'], $defaultTemplateIndex, $defaultTemplates);

        $this->data['QubitSetting']['QubitSetting_multi_repository'] = [
            'name' => 'multi_repository',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => '1'],
        ];
        $this->data['QubitSetting']['QubitSetting_site_title'] = [
            'name' => 'site_title',
            'scope' => 'site_information',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => ''],
        ];
        $this->data['QubitSetting']['QubitSetting_site_description'] = [
            'name' => 'site_description',
            'scope' => 'site_information',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => ''],
        ];
        $this->data['QubitSetting']['QubitSetting_material_type'] = [
            'name' => 'materialtype',
            'scope' => 'ui_label',
            'editable' => '1',
            'deleteable' => '0',
            'source_culture' => 'en',
            'value' => ['en' => 'material type'],
        ];

        // Update version number
        if ($settingVersionKey = $this->getRowKey('QubitSetting', 'name', 'version')) {
            foreach ($this->data['QubitSetting'][$settingVersionKey]['value'] as $culture => $value) {
                $this->data['QubitSetting'][$settingVersionKey]['value'][$culture] = str_replace('1.0.3', '1.0.4', $value);
            }
        }

        return $this;
    }

    /**
     * Alter QubitTaxonomy data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitTaxonomy()
    {
        // Add new QubitTaxonomy objects
        $this->data['QubitTaxonomy']['QubitTaxonomy_MaterialType'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::MATERIAL_TYPE_ID."\n" ?>',
            'name' => ['en' => 'Material Type'],
        ];
        $this->data['QubitTaxonomy']['QubitTaxonomy_Rad_Note'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::RAD_NOTE_ID."\n" ?>',
            'name' => ['en' => 'RAD Note'],
            'note' => ['en' => 'Note types that occur specifically within the Canadian Council of Archives\' Rules for Archival Description (RAD)'],
        ];
        $this->data['QubitTaxonomy']['QubitTaxonomy_Rad_Title_Note'] = [
            'source_culture' => 'en',
            'id' => '<?php echo QubitTaxonomy::RAD_TITLE_NOTE_ID."\n" ?>',
            'name' => ['en' => 'RAD Title Note'],
            'note' => ['en' => 'Title note types that occur specifically within the Canadian Council of Archives\' Rules for Archival Description (RAD)'],
        ];

        // Remove actor role Taxonomy
        if ($taxonomyActorRoleKey = $this->getTaxonomyActorRoleKey()) {
            unset($this->data['QubitTaxonomy'][$taxonomyActorRoleKey]);
        }

        return $this;
    }

    /**
     * Alter QubitTerm data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitTerms()
    {
        // Swap Term EXISTENCE_ID for SUBJECT_ID in the Event type taxonomy (they
        // share analogous primary keys 12 vs. 112)
        if ($existenceKey = $this->getTermExistenceKey()) {
            $existenceArrayKeyIndex = QubitMigrate::getArrayKeyIndex($this->data['QubitTerm'], $existenceKey);
            $subjectTerm = $this->data['QubitTerm'][$existenceKey];
            $subjectTerm['id'] = '<?php echo QubitTerm::SUBJECT_ID."\n" ?>';
            $subjectTerm['name'] = ['en' => 'Subject', 'fr' => 'Sujet', 'nl' => 'Onderwerp', 'pt' => 'Assunto'];

            // Splice SUBJECT_ID term into data array where EXISTENCE_ID lives now
            QubitMigrate::array_insert($this->data['QubitTerm'], $existenceArrayKeyIndex, ['QubitTerm_subject' => $subjectTerm]);

            // Delete existence term
            unset($this->data['QubitTerm'][$existenceKey]);
        }

        // Add new Event Types
        $taxonomyEventTypeKey = $this->getRowKey('QubitTaxonomy', 'id', '<?php echo QubitTaxonomy::EVENT_TYPE_ID."\n" ?>');
        $this->data['QubitTerm']['QubitTerm_accumulation'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'id' => '<?php echo QubitTerm::ACCUMULATION_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => ['en' => 'Accumulation'],
        ];
        $this->data['QubitTerm']['QubitTerm_authoring'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Authoring'],
        ];
        $this->data['QubitTerm']['QubitTerm_editing'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Editing'],
        ];
        $this->data['QubitTerm']['QubitTerm_translation'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Translation'],
        ];
        $this->data['QubitTerm']['QubitTerm_compilation'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Compilation'],
        ];
        $this->data['QubitTerm']['QubitTerm_distribution'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Distribution'],
        ];
        $this->data['QubitTerm']['QubitTerm_broadcasting'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Broadcasting'],
        ];
        $this->data['QubitTerm']['QubitTerm_manufacturing'] = [
            'taxonomy_id' => $taxonomyEventTypeKey,
            'source_culture' => 'en',
            'name' => ['en' => 'Manufacturing'],
        ];

        // Add new Note types
        $taxonomyNoteTypeKey = $this->getRowKey('QubitTaxonomy', 'id', '<?php echo QubitTaxonomy::NOTE_TYPE_ID."\n" ?>');
        $this->data['QubitTerm']['QubitTerm_display_note'] = [
            'taxonomy_id' => $taxonomyNoteTypeKey,
            'id' => '<?php echo QubitTerm::DISPLAY_NOTE_ID."\n" ?>',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Display note'],
        ];

        // Add new Material Types
        $this->data['QubitTerm']['QubitTerm_material_type_architectural_drawing'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Architectural drawing'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_cartographic_material'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Cartographic material'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_graphic_material'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Graphic material'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_moving_images'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Moving images'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_multiple_media'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Multiple media'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_object'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Object'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_philatelic_record'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Philatelic record'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_sound_recording'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Sound recording'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_technical_drawing'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Technical drawing'],
        ];
        $this->data['QubitTerm']['QubitTerm_material_type_textual_record'] = [
            'taxonomy_id' => 'QubitTaxonomy_MaterialType',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Textual record'],
        ];

        // Add new RAD Note Types
        $this->data['QubitTerm']['QubitTerm_rad_notes_edition'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Edition'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_physical_description'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Physical description'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_conservation'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Conservation'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_accompanying_material'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Accompanying material'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_publishers_series'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Publisher\'s series'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_alpha_numeric_designations'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Alpha-numeric designations'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_rights'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Rights'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_notes_general_note'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'General note'],
        ];

        // Add new RAD Title Notes
        $this->data['QubitTerm']['QubitTerm_rad_title_variations_in_title'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Title_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Variations in title'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_title_source_of_title_proper'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Title_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Source of title proper'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_title_parallel_titles_etc'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Title_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Parallel titles and other title information'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_title_continuation_of_title'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Title_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Continuation of title'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_title_statements_of_responsibility'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Title_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Statements of responsibility'],
        ];
        $this->data['QubitTerm']['QubitTerm_rad_title_attributions_and_conjectures'] = [
            'taxonomy_id' => 'QubitTaxonomy_Rad_Title_Note',
            'class_name' => 'QubitTerm',
            'source_culture' => 'en',
            'name' => ['en' => 'Attributions and conjectures'],
        ];

        // Remove Actor Role Taxonomy Terms
        $taxonomyActorRoleKey = $this->getTaxonomyActorRoleKey();
        if ($taxonomyActorRoleKey) {
            foreach ($this->data['QubitTerm'] as $key => $columns) {
                if (isset($columns['taxonomy_id']) && $columns['taxonomy_id'] == $taxonomyActorRoleKey) {
                    unset($this->data['QubitTerm'][$key]);

                    // And delete any QubitNotes linked to this term
                    while ($relatedNoteKey = $this->getRowKey('QubitNote', 'object_id', $key)) {
                        unset($this->data['QubitNote'][$relatedNoteKey]);
                    }
                }
            }
        }

        // Remove SUBJECT_ACCESS_POINT_ID term
        if ($subjectAccessPointKey = $this->getRowKey('QubitTerm', 'id', '<?php echo QubitTerm::SUBJECT_ACCESS_POINT_ID."\n" ?>')) {
            unset($this->data['QubitTerm'][$subjectAccessPointKey]);

            // And delete any QubitNotes linked to this term
            while ($relatedNoteKey = $this->getRowKey('QubitNote', 'object_id', $subjectAccessPointKey)) {
                unset($this->data['QubitNote'][$relatedNoteKey]);
            }
        }

        return $this;
    }

    /**
     * Alter QubitNotes data.
     *
     * @return QubitMigrate103 this object
     */
    protected function alterQubitNotes()
    {
        // Add new Qubit Display Notes
        $this->data['QubitNote']['QubitNote_accumulator'] = [
            'object_id' => 'QubitTerm_accumulation',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Accumulator'],
        ];
        if ($termKey = $this->getTermKey('<?php echo QubitTerm::CREATION_ID."\n" ?>')) {
            $this->data['QubitNote']['QubitNote_creator'] = [
                'object_id' => $termKey,
                'type_id' => 'QubitTerm_display_note',
                'scope' => 'QubitTerm',
                'source_culture' => 'en',
                'content' => ['en' => 'Creator', 'es' => 'Produtor', 'fr' => 'Producteur', 'nl' => 'Vervaardiger', 'pt' => 'Produtor'],
            ];
        }
        if ($termKey = $this->getTermKey('<?php echo QubitTerm::SUBJECT_ID."\n" ?>')) {
            $this->data['QubitNote']['QubitNote_subject'] = [
                'object_id' => $termKey,
                'type_id' => 'QubitTerm_display_note',
                'scope' => 'QubitTerm',
                'source_culture' => 'en',
                'content' => ['en' => 'Subject', 'fr' => 'Sujet', 'nl' => 'Onderwerp', 'pt' => 'Assunto'],
            ];
        }
        if ($termKey = $this->getTermKey('<?php echo QubitTerm::CUSTODY_ID."\n" ?>')) {
            $this->data['QubitNote']['QubitNote_custodian'] = [
                'object_id' => $termKey,
                'type_id' => 'QubitTerm_display_note',
                'scope' => 'QubitTerm',
                'source_culture' => 'en',
                'content' => ['en' => 'Custodian', 'es' => 'Custodiador', 'fr' => 'Détenteur', 'nl' => 'Beheerder', 'pt' => 'Custodiador'],
            ];
        }
        if ($termKey = $this->getTermKey('<?php echo QubitTerm::PUBLICATION_ID."\n" ?>')) {
            $this->data['QubitNote']['QubitNote_publisher'] = [
                'object_id' => $termKey,
                'type_id' => 'QubitTerm_display_note',
                'scope' => 'QubitTerm',
                'source_culture' => 'en',
                'content' => ['en' => 'Publisher', 'es' => 'Publicador', 'fr' => 'Éditeur', 'nl' => 'Uitgever', 'pt' => 'Publicador'],
            ];
        }
        if ($termKey = $this->getTermKey('<?php echo QubitTerm::CONTRIBUTION_ID."\n" ?>')) {
            $this->data['QubitNote']['QubitNote_contributor'] = [
                'object_id' => $termKey,
                'type_id' => 'QubitTerm_display_note',
                'scope' => 'QubitTerm',
                'source_culture' => 'en',
                'content' => ['en' => 'Contributor', 'es' => 'Colaborador', 'fr' => 'Collaborateur', 'nl' => 'Contribuant', 'pt' => 'Colaborador'],
            ];
        }
        if ($termKey = $this->getTermKey('<?php echo QubitTerm::COLLECTION_ID."\n" ?>')) {
            $this->data['QubitNote']['QubitNote_collector'] = [
                'object_id' => 'QubitTerm_17',
                'type_id' => 'QubitTerm_display_note',
                'scope' => 'QubitTerm',
                'source_culture' => 'en',
                'content' => ['en' => 'Collector', 'fr' => 'Collectionneur', 'nl' => 'Verzamelaar', 'pt' => 'Coletor'],
            ];
        }
        $this->data['QubitNote']['QubitNote_author'] = [
            'object_id' => 'QubitTerm_authoring',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Author'],
        ];
        $this->data['QubitNote']['QubitNote_editor'] = [
            'object_id' => 'QubitTerm_editing',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Editor'],
        ];
        $this->data['QubitNote']['QubitNote_translator'] = [
            'object_id' => 'QubitTerm_translation',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Translator'],
        ];
        $this->data['QubitNote']['QubitNote_compiler'] = [
            'object_id' => 'QubitTerm_compilation',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Compiler'],
        ];
        $this->data['QubitNote']['QubitNote_distributor'] = [
            'object_id' => 'QubitTerm_distribution',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Distributor'],
        ];
        $this->data['QubitNote']['QubitNote_broadcaster'] = [
            'object_id' => 'QubitTerm_broadcasting',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Broadcaster'],
        ];
        $this->data['QubitNote']['QubitNote_manufacturer'] = [
            'object_id' => 'QubitTerm_manufacturing',
            'type_id' => 'QubitTerm_display_note',
            'scope' => 'QubitTerm',
            'source_culture' => 'en',
            'content' => ['en' => 'Manufacturer'],
        ];

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate103 this object
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
     * @return QubitMigrate103 this object
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
            // CollectionType taxonomy
            'ARCHIVAL_MATERIAL_ID',
            'FINDING_AIDS_ID',
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
     * @return QubitMigrate103 this object
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

    /**
     * Get Taxonomy Actor Role key - used to delete the taxonomy and it's terms.
     *
     * @return string key for the Taxonomy in $this->data array
     */
    protected function getTaxonomyActorRoleKey()
    {
        if (!isset($this->taxonomyActorRoleKey)) {
            $this->taxonomyActorRoleKey = $this->getRowKey('QubitTaxonomy', 'id', '<?php echo QubitTaxonomy::ACTOR_ROLE_ID."\n" ?>');
        }

        return $this->taxonomyActorRoleKey;
    }

    /**
     * Get and cache Existence Term key.
     *
     * @return string key in $this->data array
     */
    protected function getTermExistenceKey()
    {
        if (!isset($this->termExistenceKey)) {
            $this->termExistenceKey = $this->getRowKey('QubitTerm', 'id', '<?php echo QubitTerm::EXISTENCE_ID."\n" ?>');
        }

        return $this->termExistenceKey;
    }
} // Close class QubitMigrate103
