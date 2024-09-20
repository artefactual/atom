<?php decorate_with('layout_3col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $rad]); ?>

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

  <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

  <?php echo get_partial('informationobject/genreAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

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

<section id="titleAndStatementOfResponsibilityArea">

  <?php if (check_field_visibility('app_element_visibility_rad_title_responsibility_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Title and statement of responsibility area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'titleAndStatementOfResponsibilityArea', 'title' => __('Edit title and statement of responsibility area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Title proper'), render_value($resource->getTitle(['cultureFallback' => true])), ['fieldLabel' => 'title']); ?>

  <div class="field">
    <h3><?php echo __('General material designation'); ?></h3>
    <div class="generalMaterialDesignation">
      <ul>
        <?php foreach ($resource->getMaterialTypes() as $materialType) { ?>
          <li><?php echo render_value_inline($materialType->term); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>


  <?php echo render_show(__('Parallel title'), render_value($resource->getAlternateTitle(['cultureFallback' => true])), ['fieldLabel' => 'parallelTitle']); ?>

  <?php echo render_show(__('Other title information'), render_value($rad->getProperty('otherTitleInformation', ['cultureFallback' => true])), ['fieldLabel' => 'otherTitleInformation']); ?>

  <?php echo render_show(__('Title statements of responsibility'), render_value($rad->getProperty('titleStatementOfResponsibility', ['cultureFallback' => true])), ['fieldLabel' => 'titleStatementsOfResponsibility']); ?>

  <div class="field">
    <h3><?php echo __('Title notes'); ?></h3>
    <div class="titleNotes">
      <ul>
        <?php foreach ($resource->getNotesByTaxonomy(['taxonomyId' => QubitTaxonomy::RAD_TITLE_NOTE_ID]) as $item) { ?>
          <?php if (0 != count($item->getContent(['cultureFallback' => true]))) { ?>
            <li><?php echo render_value_inline($item->type); ?>: <?php echo render_value_inline($item->getContent(['cultureFallback' => true])); ?></li>
          <?php } ?>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription), ['fieldLabel' => 'levelOfDescription']); ?>

  <div class="repository">
    <?php echo render_show_repository(__('Repository'), $resource); ?>
  </div>

  <?php echo render_show(__('Reference code'), $rad->getProperty('referenceCode', ['cultureFallback' => true]), ['fieldLabel' => 'referenceCode']); ?>

</section> <!-- /section#titleAndStatementOfResponsibilityArea -->

<section id="editionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_edition_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Edition area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'editionArea', 'title' => __('Edit edition area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Edition statement'), render_value($resource->getEdition(['cultureFallback' => true])), ['fieldLabel' => 'editionStatement']); ?>

  <?php echo render_show(__('Edition statement of responsibility'), render_value($rad->getProperty('editionStatementOfResponsibility', ['cultureFallback' => true])), ['fieldLabel' => 'editionStatementOfResponsibility']); ?>

</section> <!-- /section#editionArea -->

<section class="section" id="classOfMaterialSpecificDetailsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_material_specific_details_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Class of material specific details area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'classOfMaterialSpecificDetailsArea', 'title' => __('Edit class of material specific details area')]); ?>
  <?php } ?>


  <?php echo render_show(__('Statement of scale (cartographic)'), render_value($rad->getProperty('statementOfScaleCartographic', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfScale']); ?>

  <?php echo render_show(__('Statement of projection (cartographic)'), render_value($rad->getProperty('statementOfProjection', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfProjection']); ?>

  <?php echo render_show(__('Statement of coordinates (cartographic)'), render_value($rad->getProperty('statementOfCoordinates', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfCoordinates']); ?>

  <?php echo render_show(__('Statement of scale (architectural)'), render_value($rad->getProperty('statementOfScaleArchitectural', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfScale']); ?>

  <?php echo render_show(__('Issuing jurisdiction and denomination (philatelic)'), render_value($rad->getProperty('issuingJurisdictionAndDenomination', ['cultureFallback' => true])), ['fieldLabel' => 'issuingJurisdictionAndDenomination']); ?>
</section> <!-- /section#classOfMaterialSpecificDetailsArea -->

<section class="section" id="datesOfCreationArea">

  <?php if (check_field_visibility('app_element_visibility_rad_dates_of_creation_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Dates of creation area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'datesOfCreationArea', 'title' => __('Edit dates of creation area')]); ?>
  <?php } ?>

  <div class="datesOfCreation">
    <?php echo get_partial('informationobject/dates', ['resource' => $resource]); ?>
  </div>

</section> <!-- /section#datesOfCreationArea -->

<section id="physicalDescriptionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_physical_description_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Physical description area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'physicalDescriptionArea', 'title' => __('Edit physical description area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Physical description'), render_value($resource->getCleanExtentAndMedium(['cultureFallback' => true])), ['fieldLabel' => 'physicalDescription']); ?>

</section> <!-- /section#physicalDescriptionArea -->

<section id="publishersSeriesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_publishers_series_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Publisher\'s series area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'publishersSeriesArea', 'title' => __('Edit publisher\'s series area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Title proper of publisher\'s series'), render_value($rad->getProperty('titleProperOfPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'titleProperOfPublishersSeries']); ?>

  <?php echo render_show(__('Parallel titles of publisher\'s series'), render_value($rad->getProperty('parallelTitleOfPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'parallelTitleOfPublishersSeries']); ?>

  <?php echo render_show(__('Other title information of publisher\'s series'), render_value($rad->getProperty('otherTitleInformationOfPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'otherTitleInformationOfPublishersSeries']); ?>

  <?php echo render_show(__('Statement of responsibility relating to publisher\'s series'), render_value($rad->getProperty('statementOfResponsibilityRelatingToPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfResponsibilityRelatingToPublishersSeries']); ?>

  <?php echo render_show(__('Numbering within publisher\'s series'), render_value($rad->getProperty('numberingWithinPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'numberingWithinPublishersSeries']); ?>

  <?php echo render_show(__('Note on publisher\'s series'), render_value($rad->getProperty('noteOnPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'noteOnPublishersSeries']); ?>

</section> <!-- /section#publishersSeriesArea -->

<section id="archivalDescriptionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_archival_description_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Archival description area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'archivalDescriptionArea', 'title' => __('Edit archival description area')]); ?>
  <?php } ?>

  <?php echo get_component('informationobject', 'creatorDetail', [
      'resource' => $resource,
      'creatorHistoryLabels' => $creatorHistoryLabels, ]); ?>

  <?php if (check_field_visibility('app_element_visibility_rad_archival_history')) { ?>
    <?php echo render_show(__('Custodial history'), render_value($resource->getArchivalHistory(['cultureFallback' => true])), ['fieldLabel' => 'custodialHistory']); ?>
  <?php } ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(['cultureFallback' => true])), ['fieldLabel' => 'scopeAndContent']); ?>

</section> <!-- /section#archivalDescriptionArea -->

<section class="section" id="notesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_notes_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'notesArea', 'title' => __('Edit notes area')]); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_physical_condition')) { ?>
    <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(['cultureFallback' => true])), ['fieldLabel' => 'physicalCondition']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_immediate_source')) { ?>
    <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getAcquisition(['cultureFallback' => true])), ['fieldLabel' => 'immediateSourceOfAcquisition']); ?>
  <?php } ?>

  <?php echo render_show(__('Arrangement'), render_value($resource->getArrangement(['cultureFallback' => true])), ['fieldLabel' => 'arrangement']); ?>

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

  <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID]) as $item) { ?>
    <?php echo render_show(__('Language and script note'), render_value($item->getContent(['cultureFallback' => true])), ['fieldLabel' => 'languageAndScriptNote']); ?>
  <?php } ?>

  <?php echo render_show(__('Location of originals'), render_value($resource->getLocationOfOriginals(['cultureFallback' => true])), ['fieldLabel' => 'locationOfOriginals']); ?>

  <?php echo render_show(__('Availability of other formats'), render_value($resource->getLocationOfCopies(['cultureFallback' => true])), ['fieldLabel' => 'availabilityOfOtherFormats']); ?>

  <?php echo render_show(__('Restrictions on access'), render_value($resource->getAccessConditions(['cultureFallback' => true])), ['fieldLabel' => 'restrictionsOnAccess']); ?>

  <?php echo render_show(__('Terms governing use, reproduction, and publication'), render_value($resource->getReproductionConditions(['cultureFallback' => true])), ['fieldLabel' => 'termsGoverningUseReproductionAndPublication']); ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(['cultureFallback' => true])), ['fieldLabel' => 'findingAids']); ?>

  <?php echo get_component('informationobject', 'findingAidLink', ['resource' => $resource]); ?>

  <?php echo render_show(__('Associated materials'), render_value($resource->getRelatedUnitsOfDescription(['cultureFallback' => true])), ['fieldLabel' => 'associatedMaterials']); ?>

  <div class="relatedMaterialDescriptions">
    <?php echo get_partial('informationobject/relatedMaterialDescriptions', ['resource' => $resource, 'template' => 'rad']); ?>
  </div>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(['cultureFallback' => true])), ['fieldLabel' => 'accruals']); ?>

  <?php if (check_field_visibility('app_element_visibility_rad_general_notes')) { ?>
    <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::GENERAL_NOTE_ID]) as $item) { ?>
      <?php echo render_show(__('General note'), render_value($item->getContent(['cultureFallback' => true])), ['fieldLabel' => 'generalNote']); ?>
    <?php } ?>
  <?php } ?>

  <?php foreach ($resource->getNotesByTaxonomy(['taxonomyId' => QubitTaxonomy::RAD_NOTE_ID]) as $item) { ?>

    <?php if ($conservationTermID === $item->type->id && !check_field_visibility('app_element_visibility_rad_conservation_notes')) { ?>
      <?php continue; ?>
    <?php } ?>

    <?php if ($rightsTermID === $item->type->id && !check_field_visibility('app_element_visibility_rad_rights_notes')) { ?>
      <?php continue; ?>
    <?php } ?>

    <div class="field">
      <h3><?php echo __(render_value_inline($item->type)); ?></h3>
      <div class="radNote">
        <?php echo render_value($item->getContent(['cultureFallback' => true])); ?>
      </div>
    </div>

  <?php } ?>

  <div class="alternativeIdentifiers">
    <?php echo get_partial('informationobject/alternativeIdentifiersIndex', ['resource' => $resource]); ?>
  </div>

</section> <!-- /section#notesArea -->

<section id="standardNumberArea">

  <?php if (check_field_visibility('app_element_visibility_rad_standard_number_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Standard number area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'standardNumberArea', 'title' => __('Edit standard number area')]); ?>
  <?php } ?>

  <?php echo render_show(__('Standard number'), render_value($rad->getProperty('standardNumber', ['cultureFallback' => true])), ['fieldLabel' => 'standardNumber']); ?>

</section> <!-- /section#standardNumberArea -->

<section id="accessPointsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_access_points_area')) { ?>
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

<section class="section" id="descriptionControlArea">

  <?php if (check_field_visibility('app_element_visibility_rad_description_control_area')) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Control area').'</h2>', [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['anchor' => 'descriptionControlArea', 'title' => __('Edit control area')]); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_description_identifier')) { ?>
    <?php echo render_show(__('Description record identifier'), $resource->descriptionIdentifier, ['fieldLabel' => 'descriptionRecordIdentifier']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_institution_identifier')) { ?>
    <?php echo render_show(__('Institution identifier'), $resource->getInstitutionResponsibleIdentifier(['cultureFallback' => true]), ['fieldLabel' => 'institutionIdentifier']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_rules_conventions')) { ?>
    <?php echo render_show(__('Rules or conventions'), render_value($resource->getRules(['cultureFallback' => true])), ['fieldLabel' => 'rulesOrConventions']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_status')) { ?>
    <?php echo render_show(__('Status'), render_value($resource->descriptionStatus), ['fieldLabel' => 'status']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_level_of_detail')) { ?>
    <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail), ['fieldLabel' => 'levelOfDetail']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_dates')) { ?>
    <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true])), ['fieldLabel' => 'datesOfCreationRevisionAndDeletion']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_language')) { ?>
    <div class="field">
      <h3><?php echo __('Language of description'); ?></h3>
      <div class="languageOfDescription">
        <ul>
          <?php foreach ($resource->languageOfDescription as $code) { ?>
            <li><?php echo format_language($code); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_script')) { ?>
    <div class="field">
      <h3><?php echo __('Script of description'); ?></h3>
      <div class="scriptOfDescription">
        <ul>
          <?php foreach ($resource->scriptOfDescription as $code) { ?>
            <li><?php echo format_script($code); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_sources')) { ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(['cultureFallback' => true])), ['fieldLabel' => 'sources']); ?>
  <?php } ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()) { ?>

  <section id="rightsArea">

    <h2><?php echo __('Rights area'); ?> </h2>

    <div class="relatedRights">
      <?php echo get_component('right', 'relatedRights', ['resource' => $resource]); ?>
    </div>

  </section> <!-- /section#rightsArea -->

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
