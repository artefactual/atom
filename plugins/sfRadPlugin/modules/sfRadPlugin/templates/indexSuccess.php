<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <?php echo get_component('informationobject', 'descriptionHeader', array('resource' => $resource, 'title' => (string)$rad)) ?>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <?php $error = sfOutputEscaper::unescape($error) ?>
          <li><?php echo $error->getMessage() ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
    <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
  <?php endif; ?>

  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php slot('context-menu') ?>

  <?php echo get_partial('informationobject/actionIcons', array('resource' => $resource)) ?>

  <?php echo get_partial('object/subjectAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('object/placeAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php if (check_field_visibility('app_element_visibility_physical_storage')): ?>
    <?php echo get_component('physicalobject', 'contextMenu', array('resource' => $resource)) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)): ?>
  <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjectsRelatedByobjectId[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
<?php endif; ?>

<section id="titleAndStatementOfResponsibilityArea">

  <?php if (check_field_visibility('app_element_visibility_rad_title_responsibility_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Title and statement of responsibility area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'titleAndStatementOfResponsibilityArea', 'title' => __('Edit title and statement of responsibility area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Title proper'), render_value($resource->getTitle(array('cultureFallback' => true))), array('fieldLabel' => 'title')) ?>

  <div class="field">
    <h3><?php echo __('General material designation') ?></h3>
    <div class="generalMaterialDesignation">
      <ul>
        <?php foreach ($resource->getMaterialTypes() as $materialType): ?>
          <li><?php echo render_value_inline($materialType->term) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>


  <?php echo render_show(__('Parallel title'), render_value($resource->getAlternateTitle(array('cultureFallback' => true))), array('fieldLabel' => 'parallelTitle')) ?>

  <?php echo render_show(__('Other title information'), render_value($rad->__get('otherTitleInformation', array('cultureFallback' => true))), array('fieldLabel' => 'otherTitleInformation')) ?>

  <?php echo render_show(__('Title statements of responsibility'), render_value($rad->__get('titleStatementOfResponsibility', array('cultureFallback' => true))), array('fieldLabel' => 'titleStatementsOfResponsibility')) ?>

  <div class="field">
    <h3><?php echo __('Title notes') ?></h3>
    <div class="titleNotes">
      <ul>
        <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::RAD_TITLE_NOTE_ID)) as $item): ?>
          <li><?php echo render_value_inline($item->type) ?>: <?php echo render_value_inline($item->getContent(array('cultureFallback' => true))) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription), array('fieldLabel' => 'levelOfDescription')) ?>

  <div class="repository">
    <?php echo render_show_repository(__('Repository'), $resource) ?>
  </div>

  <?php echo render_show(__('Reference code'), $rad->__get('referenceCode', array('cultureFallback' => true)), array('fieldLabel' => 'referenceCode')) ?>

</section> <!-- /section#titleAndStatementOfResponsibilityArea -->

<section id="editionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_edition_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Edition area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'editionArea', 'title' => __('Edit edition area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Edition statement'), render_value($resource->getEdition(array('cultureFallback' => true))), array('fieldLabel' => 'editionStatement')) ?>

  <?php echo render_show(__('Edition statement of responsibility'), render_value($rad->__get('editionStatementOfResponsibility', array('cultureFallback' => true))), array('fieldLabel' => 'editionStatementOfResponsibility')) ?>

</section> <!-- /section#editionArea -->

<section class="section" id="classOfMaterialSpecificDetailsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_material_specific_details_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Class of material specific details area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'classOfMaterialSpecificDetailsArea', 'title' => __('Edit class of material specific details area'))) ?>
  <?php endif; ?>


  <?php echo render_show(__('Statement of scale (cartographic)'), render_value($rad->__get('statementOfScaleCartographic', array('cultureFallback' => true))), array('fieldLabel' => 'statementOfScale')) ?>

  <?php echo render_show(__('Statement of projection (cartographic)'), render_value($rad->__get('statementOfProjection', array('cultureFallback' => true))), array('fieldLabel' => 'statementOfProjection')) ?>

  <?php echo render_show(__('Statement of coordinates (cartographic)'), render_value($rad->__get('statementOfCoordinates', array('cultureFallback' => true))), array('fieldLabel' => 'statementOfCoordinates')) ?>

  <?php echo render_show(__('Statement of scale (architectural)'), render_value($rad->__get('statementOfScaleArchitectural', array('cultureFallback' => true))), array('fieldLabel' => 'statementOfScale')) ?>

  <?php echo render_show(__('Issuing jurisdiction and denomination (philatelic)'), render_value($rad->__get('issuingJurisdictionAndDenomination', array('cultureFallback' => true))), array('fieldLabel' => 'issuingJurisdictionAndDenomination')) ?>
</section> <!-- /section#classOfMaterialSpecificDetailsArea -->

<section class="section" id="datesOfCreationArea">

  <?php if (check_field_visibility('app_element_visibility_rad_dates_of_creation_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Dates of creation area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'datesOfCreationArea', 'title' => __('Edit dates of creation area'))) ?>
  <?php endif; ?>

  <div class="datesOfCreation">
    <?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>
  </div>

</section> <!-- /section#datesOfCreationArea -->

<section id="physicalDescriptionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_physical_description_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Physical description area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'physicalDescriptionArea', 'title' => __('Edit physical description area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Physical description'), render_value($resource->getCleanExtentAndMedium(array('cultureFallback' => true))), array('fieldLabel' => 'physicalDescription')) ?>

</section> <!-- /section#physicalDescriptionArea -->

<section id="publishersSeriesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_publishers_series_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Publisher\'s series area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'publishersSeriesArea', 'title' => __('Edit publisher\'s series area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Title proper of publisher\'s series'), render_value($rad->__get('titleProperOfPublishersSeries', array('cultureFallback' => true))), array('fieldLabel' => 'titleProperOfPublishersSeries')) ?>

  <?php echo render_show(__('Parallel titles of publisher\'s series'), render_value($rad->__get('parallelTitleOfPublishersSeries', array('cultureFallback' => true))), array('fieldLabel' => 'parallelTitleOfPublishersSeries')) ?>

  <?php echo render_show(__('Other title information of publisher\'s series'), render_value($rad->__get('otherTitleInformationOfPublishersSeries', array('cultureFallback' => true))), array('fieldLabel' => 'otherTitleInformationOfPublishersSeries')) ?>

  <?php echo render_show(__('Statement of responsibility relating to publisher\'s series'), render_value($rad->__get('statementOfResponsibilityRelatingToPublishersSeries', array('cultureFallback' => true))), array('fieldLabel' => 'statementOfResponsibilityRelatingToPublishersSeries')) ?>

  <?php echo render_show(__('Numbering within publisher\'s series'), render_value($rad->__get('numberingWithinPublishersSeries', array('cultureFallback' => true))), array('fieldLabel' => 'numberingWithinPublishersSeries')) ?>

  <?php echo render_show(__('Note on publisher\'s series'), render_value($rad->__get('noteOnPublishersSeries', array('cultureFallback' => true))), array('fieldLabel' => 'noteOnPublishersSeries')) ?>

</section> <!-- /section#publishersSeriesArea -->

<section id="archivalDescriptionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_archival_description_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Archival description area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'archivalDescriptionArea', 'title' => __('Edit archival description area'))) ?>
  <?php endif; ?>

  <?php echo get_component('informationobject', 'creatorDetail', array(
    'resource' => $resource,
    'creatorHistoryLabels' => $creatorHistoryLabels)) ?>

  <?php if (check_field_visibility('app_element_visibility_rad_archival_history')): ?>
    <?php echo render_show(__('Custodial history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true))), array('fieldLabel' => 'custodialHistory')) ?>
  <?php endif; ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true))), array('fieldLabel' => 'scopeAndContent')) ?>

</section> <!-- /section#archivalDescriptionArea -->

<section class="section" id="notesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_notes_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'notesArea', 'title' => __('Edit notes area'))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_physical_condition')): ?>
    <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true))), array('fieldLabel' => 'physicalCondition')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_immediate_source')): ?>
    <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getAcquisition(array('cultureFallback' => true))), array('fieldLabel' => 'immediateSourceOfAcquisition')) ?>
  <?php endif; ?>

  <?php echo render_show(__('Arrangement'), render_value($resource->getArrangement(array('cultureFallback' => true))), array('fieldLabel' => 'arrangement')) ?>

  <div class="field">
    <h3><?php echo __('Language of material') ?></h3>
    <div class="languageOfMaterial">
      <ul>
        <?php foreach ($resource->language as $code): ?>
          <li><?php echo format_language($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Script of material') ?></h3>
    <div class="scriptOfMaterial">
      <ul>
        <?php foreach ($resource->script as $code): ?>
          <li><?php echo format_script($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID)) as $item): ?>
    <?php echo render_show(__('Language and script note'), render_value($item->getContent(array('cultureFallback' => true))), array('fieldLabel' => 'languageAndScriptNote')) ?>
  <?php endforeach; ?>

  <?php echo render_show(__('Location of originals'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true))), array('fieldLabel' => 'locationOfOriginals')) ?>

  <?php echo render_show(__('Availability of other formats'), render_value($resource->getLocationOfCopies(array('cultureFallback' => true))), array('fieldLabel' => 'availabilityOfOtherFormats')) ?>

  <?php echo render_show(__('Restrictions on access'), render_value($resource->getAccessConditions(array('cultureFallback' => true))), array('fieldLabel' => 'restrictionsOnAccess')) ?>

  <?php echo render_show(__('Terms governing use, reproduction, and publication'), render_value($resource->getReproductionConditions(array('cultureFallback' => true))), array('fieldLabel' => 'termsGoverningUseReproductionAndPublication')) ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(array('cultureFallback' => true))), array('fieldLabel' => 'findingAids')) ?>

  <?php echo get_component('informationobject', 'findingAidLink', array('resource' => $resource)) ?>

  <?php echo render_show(__('Associated materials'), render_value($resource->getRelatedUnitsOfDescription(array('cultureFallback' => true))), array('fieldLabel' => 'associatedMaterials')) ?>

  <div class="relatedMaterialDescriptions">
    <?php echo get_partial('informationobject/relatedMaterialDescriptions', array('resource' => $resource, 'template' => 'rad')) ?>
  </div>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(array('cultureFallback' => true))), array('fieldLabel' => 'accruals')) ?>

  <?php if (check_field_visibility('app_element_visibility_rad_general_notes')): ?>
    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('General note'), render_value($item->getContent(array('cultureFallback' => true))), array('fieldLabel' => 'generalNote')) ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::RAD_NOTE_ID)) as $item): ?>

    <?php $type = $item->getType(array('sourceCulture' => true)) ?>

    <?php if ('Conservation' == $type && !check_field_visibility('app_element_visibility_rad_conservation_notes')): ?>
      <?php continue; ?>
    <?php endif; ?>

    <?php if ('Rights' == $type && !check_field_visibility('app_element_visibility_rad_rights_notes')): ?>
      <?php continue; ?>
    <?php endif; ?>

    <div class="field">
      <h3><?php echo __(render_value_inline($item->type)) ?></h3>
      <div class="radNote">
        <?php echo render_value($item->getContent(array('cultureFallback' => true))) ?>
      </div>
    </div>

  <?php endforeach; ?>

  <div class="alternativeIdentifiers">
    <?php echo get_partial('informationobject/alternativeIdentifiersIndex', array('resource' => $resource)) ?>
  </div>

</section> <!-- /section#notesArea -->

<section id="standardNumberArea">

  <?php if (check_field_visibility('app_element_visibility_rad_standard_number_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Standard number area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'standardNumberArea', 'title' => __('Edit standard number area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Standard number'), render_value($rad->__get('standardNumber', array('cultureFallback' => true))), array('fieldLabel' => 'standardNumber')) ?>

</section> <!-- /section#standardNumberArea -->

<section id="accessPointsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_access_points_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'accessPointsArea', 'title' => __('Edit access points'))) ?>
  <?php endif; ?>

  <div class="subjectAccessPoints">
    <?php echo get_partial('object/subjectAccessPoints', array('resource' => $resource)) ?>
  </div>

  <div class="placeAccessPoints">
    <?php echo get_partial('object/placeAccessPoints', array('resource' => $resource)) ?>
  </div>

  <div class="nameAccessPoints">
    <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>
  </div>

  <div class="genreAccessPoints">
    <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource)) ?>
  </div>

</section> <!-- /section#accessPointsArea -->

<section class="section" id="descriptionControlArea">

  <?php if (check_field_visibility('app_element_visibility_rad_description_control_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'descriptionControlArea', 'title' => __('Edit control area'))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_description_identifier')): ?>
    <?php echo render_show(__('Description record identifier'), $resource->descriptionIdentifier, array('fieldLabel' => 'descriptionRecordIdentifier')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_institution_identifier')): ?>
    <?php echo render_show(__('Institution identifier'), $resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true)), array('fieldLabel' => 'institutionIdentifier')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_rules_conventions')): ?>
    <?php echo render_show(__('Rules or conventions'), render_value($resource->getRules(array('cultureFallback' => true))), array('fieldLabel' => 'rulesOrConventions')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_status')): ?>
    <?php echo render_show(__('Status'), render_value($resource->descriptionStatus), array('fieldLabel' => 'status')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_level_of_detail')): ?>
    <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail), array('fieldLabel' => 'levelOfDetail')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_dates')): ?>
    <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true))), array('fieldLabel' => 'datesOfCreationRevisionAndDeletion')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_language')): ?>
    <div class="field">
      <h3><?php echo __('Language of description') ?></h3>
      <div class="languageOfDescription">
        <ul>
          <?php foreach ($resource->languageOfDescription as $code): ?>
            <li><?php echo format_language($code) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_script')): ?>
    <div class="field">
      <h3><?php echo __('Script of description') ?></h3>
      <div class="scriptOfDescription">
        <ul>
          <?php foreach ($resource->scriptOfDescription as $code): ?>
            <li><?php echo format_script($code) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_sources')): ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(array('cultureFallback' => true))), array('fieldLabel' => 'sources')) ?>
  <?php endif; ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <section id="rightsArea">

    <h2><?php echo __('Rights area') ?> </h2>

    <div class="relatedRights">
      <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>
    </div>

  </section> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)): ?>
  <div class="digitalObjectMetadata">
    <?php echo get_component('digitalobject', 'metadata', array('resource' => $resource->digitalObjectsRelatedByobjectId[0], 'object' => $resource)) ?>
  </div>
  <div class="digitalObjectRights">
    <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjectsRelatedByobjectId[0])) ?>
  </div>
<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <div class="accessions">
    <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>
  </div>
</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>
<?php end_slot() ?>

<?php echo get_component('object', 'gaInstitutionsDimension', array('resource' => $resource)) ?>
