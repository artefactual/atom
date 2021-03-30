<?php decorate_with('layout_3col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $isad]); ?>

  <?php if (isset($errorSchema)) { ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error) { ?>
          <?php $error = sfOutputEscaper::unescape($error); ?>
          <li><?php echo $error->getMessage(); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId) { ?>
    <?php echo include_partial('default/breadcrumb', ['resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft')]); ?>
  <?php } ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <?php echo get_partial('informationobject/actionIcons', ['resource' => $resource]); ?>

  <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

  <?php echo get_partial('informationobject/nameAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

  <?php echo get_partial('informationobject/genreAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

  <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

  <?php if (check_field_visibility('app_element_visibility_physical_storage')) { ?>
    <?php echo get_component('physicalobject', 'contextMenu', ['resource' => $resource]); ?>
  <?php } ?>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <?php echo get_component('digitalobject', 'imageflow', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>
  <?php echo get_component('digitalobject', 'show', ['link' => $digitalObjectLink, 'resource' => $resource->digitalObjectsRelatedByobjectId[0], 'usageType' => QubitTerm::REFERENCE_ID]); ?>
<?php } ?>

<section id="identityArea">

  <?php if (check_field_visibility('app_element_visibility_isad_identity_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Identity area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'identityArea', 'title' => __('Edit identity area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Reference code'), $isad->referenceCode, ['fieldLabel' => 'referenceCode']); ?>

  <?php echo render_show(__('Title'), render_title($resource), ['fieldLabel' => 'title']); ?>

  <div class="field">
    <h3><?php echo __('Date(s)'); ?></h3>
    <div class="creationDates">
      <ul>
        <?php foreach ($resource->getDates() as $item) { ?>
          <li>
            <?php echo render_value_inline(Qubit::renderDateStartEnd($item->getDate(['cultureFallback' => true]), $item->startDate, $item->endDate)); ?> (<?php echo $item->getType(['cultureFallback' => true]); ?>)
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription), ['fieldLabel' => 'levelOfDescription']); ?>

  <?php echo render_show(__('Extent and medium'), render_value($resource->getCleanExtentAndMedium(['cultureFallback' => true])), ['fieldLabel' => 'extentAndMedium']); ?>
</section> <!-- /section#identityArea -->

<section id="contextArea">

  <?php if (check_field_visibility('app_element_visibility_isad_context_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Context area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'contextArea', 'title' => __('Edit context area')]); ?>
  <?php } ?>

  <div class="creatorHistories">
    <?php echo get_component('informationobject', 'creatorDetail', [
        'resource' => $resource,
        'creatorHistoryLabels' => $creatorHistoryLabels, ]); ?>
  </div>

  <div class="relatedFunctions">
    <?php foreach ($functionRelations as $item) { ?>
      <div class="field">
        <h3><?php echo __('Related function'); ?></h3>
        <div>
          <?php echo link_to(render_title($item->subject->getLabel()), [$item->subject, 'module' => 'function']); ?>
        </div>
      </div>
    <?php } ?>
  </div>

  <div class="repository">
    <?php echo render_show_repository(__('Repository'), $resource); ?>
  </div>

  <?php if (check_field_visibility('app_element_visibility_isad_archival_history')) { ?>
    <?php echo render_show(__('Archival history'), render_value($resource->getArchivalHistory(['cultureFallback' => true])), ['fieldLabel' => 'archivalHistory']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_immediate_source')) { ?>
    <?php echo render_show(__('Immediate source of acquisition or transfer'), render_value($resource->getAcquisition(['cultureFallback' => true])), ['fieldLabel' => 'immediateSourceOfAcquisitionOrTransfer']); ?>
  <?php } ?>

</section> <!-- /section#contextArea -->

<section id="contentAndStructureArea">

  <?php if (check_field_visibility('app_element_visibility_isad_content_and_structure_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Content and structure area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'contentAndStructureArea', 'title' => __('Edit content and structure area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(['cultureFallback' => true])), ['fieldLabel' => 'scopeAndContent']); ?>

  <?php if (check_field_visibility('app_element_visibility_isad_appraisal_destruction')) { ?>
    <?php echo render_show(__('Appraisal, destruction and scheduling'), render_value($resource->getAppraisal(['cultureFallback' => true])), ['fieldLabel' => 'appraisalDestructionAndScheduling']); ?>
  <?php } ?>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(['cultureFallback' => true])), ['fieldLabel' => 'accruals']); ?>

  <?php echo render_show(__('System of arrangement'), render_value($resource->getArrangement(['cultureFallback' => true])), ['fieldLabel' => 'systemOfArrangement']); ?>
</section> <!-- /section#contentAndStructureArea -->

<section id="conditionsOfAccessAndUseArea">

  <?php if (check_field_visibility('app_element_visibility_isad_conditions_of_access_use_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Conditions of access and use area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'conditionsOfAccessAndUseArea', 'title' => __('Edit conditions of access and use area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Conditions governing access'), render_value($resource->getAccessConditions(['cultureFallback' => true])), ['fieldLabel' => 'conditionsGoverningAccess']); ?>

  <?php echo render_show(__('Conditions governing reproduction'), render_value($resource->getReproductionConditions(['cultureFallback' => true])), ['fieldLabel' => 'conditionsGoverningReproduction']); ?>

  <div class="field">
    <h3><?php echo __('Language of material'); ?></h3>
    <div class="languageOfMaterial">
      <ul>
        <?php foreach ($resource->language as $code) { ?>
          <li><?php echo format_language($code); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Script of material'); ?></h3>
    <div class="scriptOfMaterial">
      <ul>
        <?php foreach ($resource->script as $code) { ?>
          <li><?php echo format_script($code); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Language and script notes'), render_value($isad->languageNotes), ['fieldLabel' => 'languageAndScriptNotes']); ?>

  <?php if (check_field_visibility('app_element_visibility_isad_physical_condition')) { ?>
    <?php echo render_show(__('Physical characteristics and technical requirements'), render_value($resource->getPhysicalCharacteristics(['cultureFallback' => true])), ['fieldLabel' => 'physicalCharacteristics']); ?>
  <?php } ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(['cultureFallback' => true])), ['fieldLabel' => 'findingAids']); ?>

  <?php echo get_component('informationobject', 'findingAidLink', ['resource' => $resource]); ?>

</section> <!-- /section#conditionsOfAccessAndUseArea -->

<section id="alliedMaterialsArea">

  <?php if (check_field_visibility('app_element_visibility_isad_allied_materials_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Allied materials area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'alliedMaterialsArea', 'title' => __('Edit alied materials area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Existence and location of originals'), render_value($resource->getLocationOfOriginals(['cultureFallback' => true])), ['fieldLabel' => 'existenceAndLocationOfOriginals']); ?>

  <?php echo render_show(__('Existence and location of copies'), render_value($resource->getLocationOfCopies(['cultureFallback' => true])), ['fieldLabel' => 'existenceAndLocationOfCopies']); ?>

  <?php echo render_show(__('Related units of description'), render_value($resource->getRelatedUnitsOfDescription(['cultureFallback' => true])), ['fieldLabel' => 'relatedUnitsOfDescription']); ?>

  <div class="relatedMaterialDescriptions">
    <?php echo get_partial('informationobject/relatedMaterialDescriptions', ['resource' => $resource, 'template' => 'isad']); ?>
  </div>

  <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID]) as $item) { ?>
    <?php echo render_show(__('Publication note'), render_value($item->getContent(['cultureFallback' => true])), ['fieldLabel' => 'publicationNote']); ?>
  <?php } ?>
</section> <!-- /section#alliedMaterialsArea -->

<section id="notesArea">

  <?php if (check_field_visibility('app_element_visibility_isad_notes_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'notesArea', 'title' => __('Edit notes area')]); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_notes')) { ?>
    <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::GENERAL_NOTE_ID]) as $item) { ?>
      <?php echo render_show(__('Note'), render_value($item->getContent(['cultureFallback' => true])), ['fieldLabel' => 'generalNote']); ?>
    <?php } ?>
  <?php } ?>

  <div class="alternativeIdentifiers">
    <?php echo get_partial('informationobject/alternativeIdentifiersIndex', ['resource' => $resource]); ?>
  </div>
</section> <!-- /section#notesArea -->

<section id="accessPointsArea">

  <?php if (check_field_visibility('app_element_visibility_isad_access_points_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'accessPointsArea', 'title' => __('Edit access points')]); ?>
  <?php } ?>

  <div class="subjectAccessPoints">
    <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="placeAccessPoints">
    <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="nameAccessPoints">
    <?php echo get_partial('informationobject/nameAccessPoints', ['resource' => $resource, 'showActorEvents' => true]); ?>
  </div>

  <div class="genreAccessPoints">
    <?php echo get_partial('informationobject/genreAccessPoints', ['resource' => $resource]); ?>
  </div>
</section> <!-- /section#accessPointsArea -->

<section id="descriptionControlArea">

  <?php if (check_field_visibility('app_element_visibility_isad_description_control_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Description control area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'descriptionControlArea', 'title' => __('Edit description control area')]); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_description_identifier')) { ?>
    <?php echo render_show(__('Description identifier'), $resource->getDescriptionIdentifier(['cultureFallback' => true]), ['fieldLabel' => 'descriptionIdentifier']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_institution_identifier')) { ?>
    <?php echo render_show(__('Institution identifier'), $resource->getInstitutionResponsibleIdentifier(['cultureFallback' => true]), ['fieldLabel' => 'institutionIdentifier']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_rules_conventions')) { ?>
    <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(['cultureFallback' => true])), ['fieldLabel' => 'rulesAndOrConventionsUsed']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_status')) { ?>
    <?php echo render_show(__('Status'), render_value($resource->descriptionStatus), ['fieldLabel' => 'descriptionStatus']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_level_of_detail')) { ?>
    <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail), ['fieldLabel' => 'levelOfDetail']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_dates')) { ?>
    <?php echo render_show(__('Dates of creation revision deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true])), ['fieldLabel' => 'datesOfCreationRevisionDeletion']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_languages')) { ?>
    <div class="field">
      <h3><?php echo __('Language(s)'); ?></h3>
      <div class="languages">
        <ul>
          <?php foreach ($resource->languageOfDescription as $code) { ?>
            <li><?php echo format_language($code); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_scripts')) { ?>
    <div class="field">
      <h3><?php echo __('Script(s)'); ?></h3>
      <div class="scripts">
        <ul>
          <?php foreach ($resource->scriptOfDescription as $code) { ?>
            <li><?php echo format_script($code); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_sources')) { ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(['cultureFallback' => true])), ['fieldLabel' => 'sources']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_archivists_notes')) { ?>
    <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID]) as $item) { ?>
      <?php echo render_show(__('Archivist\'s note'), render_value($item->getContent(['cultureFallback' => true])), ['fieldLabel' => 'archivistNote']); ?>
    <?php } ?>
  <?php } ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()) { ?>

  <div class="section" id="rightsArea">

    <h2><?php echo __('Rights area'); ?> </h2>

    <div class="relatedRights">
      <?php echo get_component('right', 'relatedRights', ['resource' => $resource]); ?>
    </div>

  </div> <!-- /section#rightsArea -->

<?php } ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>

  <div class="digitalObjectMetadata">
    <?php echo get_component('digitalobject', 'metadata', ['resource' => $resource->digitalObjectsRelatedByobjectId[0], 'object' => $resource]); ?>
  </div>

  <div class="digitalObjectRights">
    <?php echo get_partial('digitalobject/rights', ['resource' => $resource->digitalObjectsRelatedByobjectId[0]]); ?>
  </div>

<?php } ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area'); ?></h2>

  <div class="accessions">
    <?php echo get_component('informationobject', 'accessions', ['resource' => $resource]); ?>
  </div>

</section> <!-- /section#accessionArea -->

<?php slot('after-content'); ?>
  <?php echo get_partial('informationobject/actions', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php echo get_component('object', 'gaInstitutionsDimension', ['resource' => $resource]); ?>
