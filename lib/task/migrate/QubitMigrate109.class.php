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
 * Upgrade qubit data from version 1.0.9 to 1.1 schema.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitMigrate109 extends QubitMigrate
{
    public const MILESTONE = '1.0.9';
    public const INIT_VERSION = 39;
    public const FINAL_VERSION = 62;

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
     * @return QubitMigrate109 this object
     */
    protected function alterData()
    {
        switch ($this->version) {
            case 39:
                $this->updateStaticPageVersionNumber();

                // no break
            case 40:
                $this->mergeTermAltLabels();

                // no break
            case 41:
                $this->renameEquivalenceTermConstant();

                // no break
            case 42:
                $this->activateEacIsaarIsdiahAndSkosPlugins();

                // no break
            case 43:
                $this->updateTaxonomyPaths();

                // no break
            case 44:
                $this->addCheckForUpdatesSetting();

                // no break
            case 45:
                $this->updateFunctionRelationTaxonomyName();

                // no break
            case 46:
                $this->guessSourceStandard();

                // no break
            case 47:
                $this->addExplodeMultipageFilesSetting();

                // no break
            case 48:
                $this->addShowTooltipsSetting();

                // no break
            case 49:
                $this->addFunctionUiLabel();

                // no break
            case 50:
                $this->addDescriptionUpdatesToAdminMenu();

                // no break
            case 51:
                $this->addDefaultPubStatusSetting();

                // no break
            case 52:
                $this->setPubStatusExplictly();

                // no break
            case 53:
                $this->activateIsdfPlugin();

                // no break
            case 54:
                // Replace "permalink" property with "slug"
                foreach ($this->data['QubitStaticPage'] as $key => $value) {
                    if (isset($value['permalink'])) {
                        $this->data['QubitStaticPage'][$key]['slug'] = $value['permalink'];
                        if ('homepage' == $value['permalink']) {
                            $this->data['QubitStaticPage'][$key]['slug'] = 'home';
                        }

                        unset($this->data['QubitStaticPage'][$key]['permalink']);
                    }
                }

                // no break
            case 55:
                // Update "home" and "about" links
                foreach ($this->data['QubitMenu'] as $key => $value) {
                    switch (@$value['path']) {
                        case 'staticpage/static?permalink=about':
                            $this->data['QubitMenu'][$key]['path'] = 'staticpage/index?slug=about';

                            break;

                        case 'staticpage/static?permalink=homepage':
                            $this->data['QubitMenu'][$key]['path'] = '@homepage';

                            break;
                    }
                }

                // no break
            case 56:
                foreach ($this->data['QubitMenu'] as $key => $value) {
                    if (isset($value['path'])) {
                        $this->data['QubitMenu'][$key]['path'] = str_replace(['create', 'duplicate'], ['add', 'copy'], $value['path']);
                    }
                }

                // no break
            case 57:
                // Update tab links in user module
                foreach ($this->data['QubitMenu'] as $key => $value) {
                    switch (@$value['path']) {
                        case 'user/index?id=%currentId%':
                            $this->data['QubitMenu'][$key]['path'] = 'user/index?slug=%currentSlug%';

                            break;

                        case 'user/indexInformationObjectAcl?id=%currentId%':
                            $this->data['QubitMenu'][$key]['path'] = 'user/indexInformationObjectAcl?slug=%currentSlug%';

                            break;

                        case 'user/indexActorAcl?id=%currentId%':
                            $this->data['QubitMenu'][$key]['path'] = 'user/indexActorAcl?slug=%currentSlug%';

                            break;

                        case 'user/indexTermAcl?id=%currentId%':
                            $this->data['QubitMenu'][$key]['path'] = 'user/indexTermAcl?slug=%currentSlug%';

                            break;
                    }
                }

                // no break
            case 58:
                if (isset($this->data['QubitEvent'])) {
                    foreach ($this->data['QubitEvent'] as $key => $value) {
                        if (isset($value['date_display'])) {
                            $this->data['QubitEvent'][$key]['date'] = $value['date_display'];

                            unset($this->data['QubitEvent'][$key]['date_display']);
                        }
                    }
                }

                // no break
            case 59:
                if (isset($this->data['QubitEvent'])) {
                    foreach ($this->data['QubitEvent'] as $key => $value) {
                        if (isset($value['date'])) {
                            foreach ($value['date'] as $culture => $date) {
                                if (0 == strlen($date)) {
                                    unset($this->data['QubitEvent'][$key]['date'][$culture]);
                                }
                            }

                            if (0 == count($this->data['QubitEvent'][$key]['date'])) {
                                unset($this->data['QubitEvent'][$key]['date']);
                            }
                        }

                        if ('0000-00-00' == @$value['end_date']) {
                            unset($this->data['QubitEvent'][$key]['end_date']);
                        }

                        if ('0000-00-00' == @$value['start_date']) {
                            unset($this->data['QubitEvent'][$key]['start_date']);
                        }
                    }
                }

                // no break
            case 60:
                if (isset($ths->data['QubitEvent'])) {
                    foreach ($this->data['QubitEvent'] as $key => $value) {
                        if (!isset($value['end_date']) && isset($value['start_date'])) {
                            $this->data['QubitEvent'][$key]['end_date'] = $value['start_date'];
                        }
                    }
                }

                // no break
            case 61:
                $this->renameRelationNoteDateConstant();
        }

        // Delete "stub" objects
        $this->deleteStubObjects();

        return $this;
    }

    /**
     * Slugs are inserted when some resources are inserted, but slugs are dumped
     * separately when data is dumped.  So loading slug data will try to insert
     * duplicate slugs.  To work around this, turn slugs into resource properties
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
     * Call all sort methods.
     *
     * @return QubitMigrate109 this object
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
     * Ver 40: Update static page release number to 1.1.
     *
     * @return QubitMigrate109 this object
     */
    protected function updateStaticPageVersionNumber()
    {
        // Update version number
        foreach ($this->data['QubitStaticPage'] as $key => $page) {
            if ('homepage' == $page['permalink'] || 'about' == $page['permalink']) {
                array_walk($this->data['QubitStaticPage'][$key]['content'], function (&$x) {
                    $x = preg_replace('/1\.0\.9/', '1.1', $x);
                });
            }
        }

        return $this;
    }

    /**
     * Ver 41: Merge alternate labels from related terms into other_name table.
     *
     * @return QubitMigrate109 this object
     */
    protected function mergeTermAltLabels()
    {
        $eqKey = $this->getRowKey('QubitTerm', 'id', '<?php echo QubitTerm::TERM_RELATION_EQUIVALENCE_ID."\n" ?>');

        if (!$eqKey) {
            return $this;
        }

        foreach ($this->data['QubitRelation'] as $key => $row) {
            if (!isset($row['type_id']) || $eqKey != $row['type_id'] || null === $eqTerm = $this->data['QubitTerm'][$row['object_id']]) {
                continue;
            }

            $otherName = [
                'object_id' => $row['subject_id'],
                'type_id' => $row['type_id'],
                'source_culture' => $eqTerm['source_culture'],
                'name' => $eqTerm['name'],
            ];

            $this->data['QubitOtherName'][rand()] = $otherName;
            unset($this->data['QubitRelation'][$key], $this->data['QubitTerm'][$row['object_id']]);
        }

        return $this;
    }

    /**
     * Ver 42: Change Term constant "TERM_RELATION_EQUIVALENCE_ID" to
     * "ALTERNATIVE_LABEL_ID".
     *
     * @return QubitMigrate109 this object
     */
    protected function renameEquivalenceTermConstant()
    {
        if ($key = $this->getRowKey('QubitTerm', 'id', '<?php echo QubitTerm::TERM_RELATION_EQUIVALENCE_ID."\n" ?>')) {
            $this->data['QubitTerm'][$key]['id'] = '<?php echo QubitTerm::ALTERNATIVE_LABEL_ID."\n" ?>';
            $this->data['QubitTerm'][$key]['source_culture'] = 'en';
            $this->data['QubitTerm'][$key]['name'] = ['en' => 'alternative label'];
        }

        return $this;
    }

    /**
     * Ver 43: Activate sfEacPlugin, sfIsaarPlugin, sfIsdiahPlugin
     * and sfSkosPlugin plugins.
     *
     * @return QubitMigrate109 this object
     */
    protected function activateEacIsaarIsdiahAndSkosPlugins()
    {
        $plugins = ['sfEacPlugin', 'sfIsaarPlugin', 'sfIsdiahPlugin', 'sfSkosPlugin'];

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
    }

    /**
     * Ver 44: Update taxonomy paths like revision 7131 and 7132.
     *
     * @return QubitMigrate109 this object
     */
    protected function updateTaxonomyPaths()
    {
        if ($key = $this->getRowKey('QubitMenu', 'path', 'term/list')) {
            $this->data['QubitMenu'][$key]['path'] = 'taxonomy/list';
        }

        foreach ($this->data['QubitMenu'] as $key => $row) {
            if (isset($row['path']) && null !== strpos($row['path'], 'term/browseTaxonomy')) {
                $this->data['QubitMenu'][$key]['path'] = str_replace('term/browseTaxonomy', 'taxonomy/browse', $row['path']);
            }
        }
    }

    /**
     * Ver 45: Add check_for_updates QubitSetting.
     *
     * @return QubitMigrate109 this object
     */
    protected function addCheckForUpdatesSetting()
    {
        if (false === $this->getRowKey('QubitSetting', 'name', 'QubitSetting_checkForUpdates')) {
            $this->data['QubitSetting']['QubitSetting_checkForUpdates'] = [
                'name' => 'check_for_updates',
                'value' => 1,
            ];
        }

        return $this;
    }

    /**
     * Ver 46: Change taxonomy name "ISDF Relation Type" -> "Function Relation
     * Type".
     *
     * @return QubitMigrate109 this object
     */
    protected function updateFunctionRelationTaxonomyName()
    {
        if ($key = $this->getRowKey('QubitTaxonomy', 'name', ['en' => 'ISDF Relation Type'])) {
            $this->data['QubitTaxonomy'][$key]['name']['en'] = 'Function Relation Type';
        }

        return $this;
    }

    /**
     * Ver 47: Make an educated guess at source_standard for legacy data.
     *
     * @return QubitMigrate109 this object
     */
    protected function guessSourceStandard()
    {
        $standards = [
            'dc' => 'Dublin Core Simple version 1.1',
            'isad' => 'ISAD(G) 2nd edition',
            'isaar' => 'ISAAR(CPF) 2nd edition',
            'isdiah' => 'ICA-ISDIAH 1st edition',
            'mods' => 'MODS version 3.3',
            'rad' => 'RAD version Jul2008',
        ];

        foreach ($this->data['QubitSetting'] as $key => $row) {
            if (isset($row['scope']) && 'default_template' != $row['scope']) {
                continue;
            }

            switch ($row['name']) {
                case 'informationobject':
                    $className = 'QubitInformationObject';
                    $standard = $standards['isad'];

                    break;

                case 'actor':
                    $className = 'QubitActor';
                    $standard = $standards['isaar'];

                    break;

                case 'repository':
                    $className = 'QubitRepository';
                    $standard = $standards['isdiah'];

                    break;

                default:
                    break;
            }

            if (isset($row['value']['en'], $standards[$row['value']['en']])) {
                $standard = $standards[$row['value']['en']];
            }

            if (isset($className)) {
                foreach ($this->data[$className] as $key2 => $row2) {
                    if (!isset($row2['source_standard']) && false === strpos(@$row2['id'], 'ROOT_ID') && !in_array(@$row2['id'], [QubitInformationObject::ROOT_ID, QubitActor::ROOT_ID, QubitRepository::ROOT_ID])) {
                        $this->data[$className][$key2]['source_standard'] = $standard;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Ver 48: Add explode_multipage_files QubitSetting.
     *
     * @return QubitMigrate109 this object
     */
    protected function addExplodeMultipageFilesSetting()
    {
        if (false === $this->getRowKey('QubitSetting', 'name', 'QubitSetting_explodeMultipageFiles')) {
            $this->data['QubitSetting']['QubitSetting_explodeMultipageFiles'] = [
                'name' => 'explode_multipage_files',
                'value' => 0,
            ];
        }

        return $this;
    }

    /**
     * Ver 49: Add show_tooltips QubitSetting.
     *
     * @return QubitMigrate109 this object
     */
    protected function addShowTooltipsSetting()
    {
        if (false === $this->getRowKey('QubitSetting', 'name', 'QubitSetting_showTooltips')) {
            $this->data['QubitSetting']['QubitSetting_showTooltips'] = [
                'name' => 'show_tooltips',
                'value' => 1,
            ];
        }

        return $this;
    }

    /**
     * Ver 50: Add "function" UI label QubitSetting.
     *
     * @return QubitMigrate109 this object
     */
    protected function addFunctionUiLabel()
    {
        $this->data['QubitSetting']['function_ui_label'] = [
            'name' => 'function',
            'scope' => 'ui_label',
            'editable' => 1,
            'deleteable' => 0,
            'source_culture' => 'en',
            'value' => [
                'en' => 'Function',
                'es' => 'Función',
                'fr' => 'Fonction',
                'pt' => 'Funçao',
            ],
        ];

        return $this;
    }

    /**
     * Ver 51: Add descriptionUpdates to Admin menu.
     *
     * @return QubitMigrate109 this object
     */
    protected function addDescriptionUpdatesToAdminMenu()
    {
        // $recentUpdatesMenu = array(
        $this->data['QubitMenu']['QubitMenu_admin_descriptionUpdates'] = [
            'parent_id' => '<?php echo QubitMenu::ADMIN_ID."\n" ?>',
            'source_culture' => 'en',
            'name' => 'descriptionUpdates',
            'label' => ['en' => 'Description updates'],
            'path' => 'search/descriptionUpdates',
        ];

        return $this;
    }

    /**
     * Ver 52: Add default publication status with initial value "draft".
     *
     * @return QubitMigrate109 this object
     */
    protected function addDefaultPubStatusSetting()
    {
        $this->data['QubitSetting']['QubitSetting_default_pub_status'] = [
            'name' => 'defaultPubStatus',
            'editable' => 1,
            'value' => '<?php echo QubitTerm::PUBLICATION_STATUS_DRAFT_ID."\n" ?>',
        ];

        return $this;
    }

    /**
     * Ver 53: Explicitly set publication status on all info objects.
     *
     * @return QubitMigrate109 this object
     */
    protected function setPubStatusExplictly()
    {
        foreach ($this->data['QubitInformationObject'] as $key => $item) {
            // Don't touch root info object
            if (isset($item['id']) && '<?php echo QubitInformationObject::ROOT_ID."\n" ?>' == $item['id']) {
                continue;
            }

            if (false === $this->getRowKey('QubitStatus', 'object_id', $key)) {
                $keys = [$key];

                // Build array of all descriptions from the current one until we reach
                // an ancestor with a publication status
                while (isset($this->data['QubitInformationObject'][$keys[0]]['parent_id'])) {
                    $parentKey = $this->data['QubitInformationObject'][$keys[0]]['parent_id'];
                    $statusKey = $this->getRowKey('QubitStatus', 'object_id', $parentKey);

                    if ($statusKey) {
                        break;
                    }

                    array_unshift($keys, $parentKey);
                }

                // Duplicate ancestor's publication status
                if ($statusKey) {
                    $status = [];
                    $status['type_id'] = $this->data['QubitStatus'][$statusKey]['type_id'];
                    $status['status_id'] = $this->data['QubitStatus'][$statusKey]['status_id'];

                    // Assign status to all descendents in $keys stack
                    while (0 < count($keys)) {
                        $status['object_id'] = array_shift($keys);

                        $this->data['QubitStatus']["QubitStatus_{$status['object_id']}"] = $status;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Ver 54: Activate sfIsdfPlugin plugin.
     *
     * @return QubitMigrate109 this object
     */
    protected function activateIsdfPlugin()
    {
        $plugin = ['sfIsdfPlugin'];

        // Find setting
        $found = false;
        foreach ($this->data['QubitSetting'] as $key => $value) {
            if ('plugins' == $value['name']) {
                // Found setting, add new plugins
                $found = true;
                $this->data['QubitSetting'][$key]['value'][$value['source_culture']] = serialize(array_unique(array_merge(unserialize($value['value'][$value['source_culture']]), $plugin)));

                break;
            }
        }

        if (!$found) {
            // No setting, add one
            $value = [];
            $value['name'] = 'plugins';
            $value['source_culture'] = 'en';
            $value['value']['en'] = serialize($plugin);

            $this->data['QubitSetting'][rand()] = $value;
        }
    }

    /**
     * Ver 62: Update name of QubitTerm::RELATION_NOTE_DATE_ID.
     *
     * @return QubitMigrate109 this object
     */
    protected function renameRelationNoteDateConstant()
    {
        foreach ($this->data['QubitTerm'] as $key => &$item) {
            if (isset($item['id']) && false !== strpos($item['id'], 'RELATION_NOTE_DATE_DISPLAY_ID')) {
                $item['id'] = str_replace('RELATION_NOTE_DATE_DISPLAY_ID', 'RELATION_NOTE_DATE_ID', $item['id']);
            }
        }

        return $this;
    }

    /**
     * Sort information objects by lft value so that parent objects are inserted
     * before their children.
     *
     * @return QubitMigrate109 this object
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
     * @return QubitMigrate109 this object
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
     * @return QubitMigrate109 this object
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
