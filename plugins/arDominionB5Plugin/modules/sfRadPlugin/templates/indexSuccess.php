<?php decorate_with('layout_3col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $rad]); ?>

  <?php if (isset($errorSchema)) { ?>
    <div class="alert alert-danger" role="alert">
      <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
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

  <nav>

    <?php echo get_partial('informationobject/actionIcons', ['resource' => $resource]); ?>

    <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

    <?php echo get_partial('informationobject/nameAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

    <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

    <?php echo get_partial('informationobject/genreAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

    <?php if (check_field_visibility('app_element_visibility_physical_storage')) { ?>
      <?php echo get_component('physicalobject', 'contextMenu', ['resource' => $resource]); ?>
    <?php } ?>

  </nav>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <?php echo get_component('digitalobject', 'imageflow', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>
  <?php echo get_component('digitalobject', 'show', ['link' => $digitalObjectLink, 'resource' => $resource->digitalObjectsRelatedByobjectId[0], 'usageType' => QubitTerm::REFERENCE_ID]); ?>
<?php } ?>

<?php
    // TODO: Move this to the controller when we only have B5 themes
    $headingsCondition = SecurityPrivileges::editCredentials($sf_user, 'informationObject');
    $headingsUrl = [$resource, 'module' => 'informationobject', 'action' => 'edit'];
?>

<section id="titleAndStatementOfResponsibilityArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_title_responsibility_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Title and statement of responsibility area'),
        $headingsCondition,
        $headingsUrl,
        [
            'anchor' => 'title-collapse',
            'class' => 0 < count($resource->digitalObjectsRelatedByobjectId) ? '' : 'rounded-top',
        ]
    ); ?>
  <?php } ?>

  <?php echo render_show(__('Title proper'), render_value_inline($resource->getTitle(['cultureFallback' => true])), ['fieldLabel' => 'title']); ?>

  <?php
      $terms = [];
      foreach ($resource->getMaterialTypes() as $materialType) {
          $terms[] = $materialType->term;
      }
      echo render_show(__('General material designation'), $terms);
  ?>

  <?php echo render_show(__('Parallel title'), render_value_inline($resource->getAlternateTitle(['cultureFallback' => true])), ['fieldLabel' => 'parallelTitle']); ?>

  <?php echo render_show(__('Other title information'), render_value_inline($rad->getProperty('otherTitleInformation', ['cultureFallback' => true])), ['fieldLabel' => 'otherTitleInformation']); ?>

  <?php echo render_show(__('Title statements of responsibility'), render_value_inline($rad->getProperty('titleStatementOfResponsibility', ['cultureFallback' => true])), ['fieldLabel' => 'titleStatementsOfResponsibility']); ?>

  <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
    <?php echo render_b5_show_label(__('Title notes')); ?>
    <div class="titleNotes <?php echo render_b5_show_value_css_classes(); ?>">
      <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
        <?php foreach ($resource->getNotesByTaxonomy(['taxonomyId' => QubitTaxonomy::RAD_TITLE_NOTE_ID]) as $item) { ?>
          <?php if (0 != strlen($item->getContent(['cultureFallback' => true]))) { ?>
            <li><?php echo render_value_inline($item->type); ?>: <?php echo render_value_inline($item->getContent(['cultureFallback' => true])); ?></li>
          <?php } ?>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value_inline($resource->levelOfDescription), ['fieldLabel' => 'levelOfDescription']); ?>

  <div class="repository">
    <?php echo render_show_repository(__('Repository'), $resource); ?>
  </div>

  <?php echo render_show(__('Reference code'), $rad->getProperty('referenceCode', ['cultureFallback' => true]), ['fieldLabel' => 'referenceCode']); ?>

</section> <!-- /section#titleAndStatementOfResponsibilityArea -->

<section id="editionArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_edition_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Edition area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'edition-collapse']
    ); ?>
  <?php } ?>

  <?php echo render_show(__('Edition statement'), render_value_inline($resource->getEdition(['cultureFallback' => true])), ['fieldLabel' => 'editionStatement']); ?>

  <?php echo render_show(__('Edition statement of responsibility'), render_value_inline($rad->getProperty('editionStatementOfResponsibility', ['cultureFallback' => true])), ['fieldLabel' => 'editionStatementOfResponsibility']); ?>

</section> <!-- /section#editionArea -->

<section class="section border-bottom" id="classOfMaterialSpecificDetailsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_material_specific_details_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Class of material specific details area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'class-collapse']
    ); ?>
  <?php } ?>


  <?php echo render_show(__('Statement of scale (cartographic)'), render_value_inline($rad->getProperty('statementOfScaleCartographic', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfScale']); ?>

  <?php echo render_show(__('Statement of projection (cartographic)'), render_value_inline($rad->getProperty('statementOfProjection', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfProjection']); ?>

  <?php echo render_show(__('Statement of coordinates (cartographic)'), render_value_inline($rad->getProperty('statementOfCoordinates', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfCoordinates']); ?>

  <?php echo render_show(__('Statement of scale (architectural)'), render_value_inline($rad->getProperty('statementOfScaleArchitectural', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfScale']); ?>

  <?php echo render_show(__('Issuing jurisdiction and denomination (philatelic)'), render_value_inline($rad->getProperty('issuingJurisdictionAndDenomination', ['cultureFallback' => true])), ['fieldLabel' => 'issuingJurisdictionAndDenomination']); ?>
</section> <!-- /section#classOfMaterialSpecificDetailsArea -->

<section class="section border-bottom" id="datesOfCreationArea">

  <?php if (check_field_visibility('app_element_visibility_rad_dates_of_creation_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Dates of creation area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'dates-collapse']
    ); ?>
  <?php } ?>

  <div class="datesOfCreation">
    <?php echo get_partial('informationobject/dates', ['resource' => $resource]); ?>
  </div>

</section> <!-- /section#datesOfCreationArea -->

<section id="physicalDescriptionArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_physical_description_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Physical description area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'physical-collapse']
    ); ?>
  <?php } ?>

  <?php echo render_show(__('Physical description'), render_value($resource->getCleanExtentAndMedium(['cultureFallback' => true])), ['fieldLabel' => 'physicalDescription']); ?>

</section> <!-- /section#physicalDescriptionArea -->

<section id="publishersSeriesArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_publishers_series_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Publisher\'s series area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'publisher-collapse']
    ); ?>
  <?php } ?>

  <?php echo render_show(__('Title proper of publisher\'s series'), render_value_inline($rad->getProperty('titleProperOfPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'titleProperOfPublishersSeries']); ?>

  <?php echo render_show(__('Parallel titles of publisher\'s series'), render_value_inline($rad->getProperty('parallelTitleOfPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'parallelTitleOfPublishersSeries']); ?>

  <?php echo render_show(__('Other title information of publisher\'s series'), render_value_inline($rad->getProperty('otherTitleInformationOfPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'otherTitleInformationOfPublishersSeries']); ?>

  <?php echo render_show(__('Statement of responsibility relating to publisher\'s series'), render_value_inline($rad->getProperty('statementOfResponsibilityRelatingToPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'statementOfResponsibilityRelatingToPublishersSeries']); ?>

  <?php echo render_show(__('Numbering within publisher\'s series'), render_value_inline($rad->getProperty('numberingWithinPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'numberingWithinPublishersSeries']); ?>

  <?php echo render_show(__('Note on publisher\'s series'), render_value_inline($rad->getProperty('noteOnPublishersSeries', ['cultureFallback' => true])), ['fieldLabel' => 'noteOnPublishersSeries']); ?>

</section> <!-- /section#publishersSeriesArea -->

<section id="archivalDescriptionArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_archival_description_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Archival description area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'archival-collapse']
    ); ?>
  <?php } ?>

  <?php echo get_component('informationobject', 'creatorDetail', [
      'resource' => $resource,
      'creatorHistoryLabels' => $creatorHistoryLabels, ]); ?>

  <?php if (check_field_visibility('app_element_visibility_rad_archival_history')) { ?>
    <?php echo render_show(__('Custodial history'), render_value($resource->getArchivalHistory(['cultureFallback' => true])), ['fieldLabel' => 'custodialHistory']); ?>
  <?php } ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(['cultureFallback' => true])), ['fieldLabel' => 'scopeAndContent']); ?>

</section> <!-- /section#archivalDescriptionArea -->

<section class="section border-bottom" id="notesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_notes_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Notes area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'notes-collapse']
    ); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_physical_condition')) { ?>
    <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(['cultureFallback' => true])), ['fieldLabel' => 'physicalCondition']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_immediate_source')) { ?>
    <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getAcquisition(['cultureFallback' => true])), ['fieldLabel' => 'immediateSourceOfAcquisition']); ?>
  <?php } ?>

  <?php echo render_show(__('Arrangement'), render_value($resource->getArrangement(['cultureFallback' => true])), ['fieldLabel' => 'arrangement']); ?>

  <?php
      $languages = [];
      foreach ($resource->language as $code) {
          $languages[] = format_language($code);
      }
      echo render_show(__('Language of material'), $languages);
  ?>

  <?php
      $scripts = [];
      foreach ($resource->script as $code) {
          $scripts[] = format_script($code);
      }
      echo render_show(__('Script of material'), $scripts);
  ?>

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

    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(__(render_value_inline($item->type))); ?>
      <div class="radNote <?php echo render_b5_show_value_css_classes(); ?>">
        <?php echo render_value($item->getContent(['cultureFallback' => true])); ?>
      </div>
    </div>

  <?php } ?>

  <div class="alternativeIdentifiers">
    <?php echo get_partial('informationobject/alternativeIdentifiersIndex', ['resource' => $resource]); ?>
  </div>

</section> <!-- /section#notesArea -->

<section id="standardNumberArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_standard_number_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Standard number'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'standard-collapse']
    ); ?>
  <?php } ?>

  <?php echo render_show(__('Standard number'), render_value_inline($rad->getProperty('standardNumber', ['cultureFallback' => true])), ['fieldLabel' => 'standardNumber']); ?>

</section> <!-- /section#standardNumberArea -->

<section id="accessPointsArea" class="border-bottom">

  <?php if (check_field_visibility('app_element_visibility_rad_access_points_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Access points'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'access-collapse']
    ); ?>
  <?php } ?>

  <div class="subjectAccessPoints">
    <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="placeAccessPoints">
    <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="nameAccessPoints">
    <?php echo get_partial('informationobject/nameAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="genreAccessPoints">
    <?php echo get_partial('informationobject/genreAccessPoints', ['resource' => $resource]); ?>
  </div>

</section> <!-- /section#accessPointsArea -->

<section class="section border-bottom" id="descriptionControlArea">

  <?php if (check_field_visibility('app_element_visibility_rad_description_control_area')) { ?>
    <?php echo render_b5_section_heading(
        __('Control area'),
        $headingsCondition,
        $headingsUrl,
        ['anchor' => 'control-collapse']
    ); ?>
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
    <?php echo render_show(__('Status'), render_value_inline($resource->descriptionStatus), ['fieldLabel' => 'status']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_level_of_detail')) { ?>
    <?php echo render_show(__('Level of detail'), render_value_inline($resource->descriptionDetail), ['fieldLabel' => 'levelOfDetail']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_dates')) { ?>
    <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true])), ['fieldLabel' => 'datesOfCreationRevisionAndDeletion']); ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_language')) { ?>
    <?php
        $languages = [];
        foreach ($resource->languageOfDescription as $code) {
            $languages[] = format_language($code);
        }
        echo render_show(__('Language of description'), $languages);
    ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_script')) { ?>
    <?php
        $scripts = [];
        foreach ($resource->scriptOfDescription as $code) {
            $scripts[] = format_script($code);
        }
        echo render_show(__('Script of description'), $scripts);
    ?>
  <?php } ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_sources')) { ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(['cultureFallback' => true])), ['fieldLabel' => 'sources']); ?>
  <?php } ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()) { ?>

  <section id="rightsArea" class="border-bottom">

    <?php echo render_b5_section_heading(__('Rights area')); ?>

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

<section id="accessionArea" class="border-bottom">

  <?php echo render_b5_section_heading(__('Accession area')); ?>

  <div class="accessions">
    <?php echo get_component('informationobject', 'accessions', ['resource' => $resource]); ?>
  </div>
</section> <!-- /section#accessionArea -->

<?php slot('after-content'); ?>
  <?php echo get_partial('informationobject/actions', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php echo get_component('object', 'gaInstitutionsDimension', ['resource' => $resource]); ?>
