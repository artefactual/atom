<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($rad) ?></h1>

  <?php echo get_partial('informationobject/printPreviewBar', array('resource' => $resource)) ?>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
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

  <?php if (false): ?>
  <?php $repository = $resource->getRepository(array('inherit' => true)) ?>
  <?php if (null !== $repository && null !== ($contactInformations = $repository->contactInformations)): ?>
    <section>

      <h4><?php echo __('How to access to this content?') ?></h4>

      <div class="content">
        <?php echo __('Contact the archivist at %1%', array('%1%' => $repository->__toString())) ?>
        <a href="#contact-modal" class="btn btn-small" role="button" data-target="#contact-modal" data-backdrop="true" data-toggle="modal"><?php echo __('Show details') ?></a>
      </div>

      <div class="modal hide fade" id="contact-modal" tabindex="-1" role="dialog" aria-labelledby="contact-modal-label" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 id="contact-modal-label"><?php echo __('How to access to this content?') ?></h3>
        </div>
        <div class="modal-body">
         <?php foreach ($contactInformations as $contactItem): ?>
            <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo __('Close') ?></button>
        </div>
      </div>

    </section>
  <?php endif; ?>
  <?php endif; ?>

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php if (check_field_visibility('app_element_visibility_physical_storage')): ?>
    <?php echo get_component('physicalobject', 'contextMenu', array('resource' => $resource)) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php if (0 < count($resource->digitalObjects)): ?>
  <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
<?php endif; ?>

<section id="titleAndStatementOfResponsibilityArea">

  <?php if (check_field_visibility('app_element_visibility_rad_title_responsibility_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Title and statement of responsibility area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'titleAndStatementOfResponsibilityArea', 'title' => __('Edit title and statement of responsibility area'))) ?>
  <?php endif; ?>
  <?php echo render_show(__('Title proper'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('General material designation') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getMaterialTypes() as $materialType): ?>
          <li><?php echo $materialType->term ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Parallel title'), render_value($resource->getAlternateTitle(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Other title information'), render_value($rad->__get('otherTitleInformation', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Title statements of responsibility'), render_value($rad->__get('titleStatementOfResponsibility', array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Title notes') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::RAD_TITLE_NOTE_ID)) as $item): ?>
          <li><?php echo $item->type ?>: <?php echo $item->getContent(array('cultureFallback' => true)) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription)) ?>

  <?php echo render_show_repository(__('Repository'), $resource) ?>

  <?php echo render_show(__('Reference code'), render_value($rad->__get('referenceCode', array('cultureFallback' => true)))) ?>

</section> <!-- /section#titleAndStatementOfResponsibilityArea -->

<section id="editionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_edition_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Edition area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'editionArea', 'title' => __('Edit edition area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Edition statement'), render_value($resource->getEdition(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Edition statement of responsibility'), render_value($rad->__get('editionStatementOfResponsibility', array('cultureFallback' => true)))) ?>

</section> <!-- /section#editionArea -->

<section class="section" id="classOfMaterialSpecificDetailsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_material_specific_details_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Class of material specific details area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'classOfMaterialSpecificDetailsArea', 'title' => __('Edit class of material specific details area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Statement of scale (cartographic)'), render_value($rad->__get('statementOfScaleCartographic', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Statement of projection (cartographic)'), render_value($rad->__get('statementOfProjection', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Statement of coordinates (cartographic)'), render_value($rad->__get('statementOfCoordinates', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Statement of scale (architectural)'), render_value($rad->__get('statementOfScaleArchitectural', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Issuing jurisdiction and denomination (philatelic)'), render_value($rad->__get('issuingJurisdictionAndDenomination', array('cultureFallback' => true)))) ?>

</section> <!-- /section#classOfMaterialSpecificDetailsArea -->

<section class="section" id="datesOfCreationArea">

  <?php if (check_field_visibility('app_element_visibility_rad_dates_of_creation_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Dates of creation area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'datesOfCreationArea', 'title' => __('Edit dates of creation area'))) ?>
  <?php endif; ?>

  <?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>

</section> <!-- /section#datesOfCreationArea -->

<section id="physicalDescriptionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_physical_description_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Physical description area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'physicalDescriptionArea', 'title' => __('Edit physical description area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Physical description'), render_value($resource->getExtentAndMedium(array('cultureFallback' => true)))) ?>

</section> <!-- /section#physicalDescriptionArea -->

<section id="publishersSeriesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_publishers_series_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Publisher\'s series area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'publishersSeriesArea', 'title' => __('Edit publisher\'s series area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Title proper of publisher\'s series'), render_value($rad->__get('titleProperOfPublishersSeries', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Parallel titles of publisher\'s series'), render_value($rad->__get('parallelTitleOfPublishersSeries', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Other title information of publisher\'s series'), render_value($rad->__get('otherTitleInformationOfPublishersSeries', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Statement of responsibility relating to publisher\'s series'), render_value($rad->__get('statementOfResponsibilityRelatingToPublishersSeries', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Numbering within publisher\'s series'), render_value($rad->__get('numberingWithinPublishersSeries', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Note on publisher\'s series'), render_value($rad->__get('noteOnPublishersSeries', array('cultureFallback' => true)))) ?>

</section> <!-- /section#publishersSeriesArea -->

<section id="archivalDescriptionArea">

  <?php if (check_field_visibility('app_element_visibility_rad_archival_description_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Archival description area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'archivalDescriptionArea', 'title' => __('Edit archival description area'))) ?>
  <?php endif; ?>

  <?php echo get_component('informationobject', 'creatorDetail', array('resource' => $resource)) ?>

  <?php if (check_field_visibility('app_element_visibility_rad_archival_history')): ?>
    <?php echo render_show(__('Custodial history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

</section> <!-- /section#archivalDescriptionArea -->

<section class="section" id="notesArea">

  <?php if (check_field_visibility('app_element_visibility_rad_notes_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'notesArea', 'title' => __('Edit notes area'))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_physical_condition')): ?>
    <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_immediate_source')): ?>
    <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getAcquisition(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Arrangement'), render_value($resource->getArrangement(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Language of material') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->language as $code): ?>
          <li><?php echo format_language($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Script of material') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->script as $code): ?>
          <li><?php echo format_script($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID)) as $item): ?>
    <?php echo render_show(__('Language and script note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
  <?php endforeach; ?>

  <?php echo render_show(__('Location of originals'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Availability of other formats'), render_value($resource->getLocationOfCopies(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Restrictions on access'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Terms governing use, reproduction, and publication'), render_value($resource->getReproductionConditions(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Associated materials'), render_value($resource->getRelatedUnitsOfDescription(array('cultureFallback' => true)))) ?>

  <?php echo get_partial('informationobject/relatedMaterialDescriptions', array('resource' => $resource, 'template' => 'rad')) ?>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Other notes') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::RAD_NOTE_ID)) as $item): ?>

          <?php $type = $item->getType(array('sourceCulture' => true)) ?>

          <?php if ('General note' == $type && !check_field_visibility('app_element_visibility_rad_general_notes')): ?>
            <?php continue; ?>
          <?php endif; ?>

          <?php if ('Conservation' == $type && !check_field_visibility('app_element_visibility_rad_conservation_notes')): ?>
            <?php continue; ?>
          <?php endif; ?>

          <li><?php echo $item->type ?>: <?php echo render_value($item->getContent(array('cultureFallback' => true))) ?></li>

        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo get_partial('informationobject/alternativeIdentifiersIndex', array('resource' => $resource)) ?>

</section> <!-- /section#notesArea -->

<section id="standardNumberArea">

  <?php if (check_field_visibility('app_element_visibility_rad_standard_number_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Standard number area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'standardNumberArea', 'title' => __('Edit standard number area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Standard number'), render_value($rad->__get('standardNumber', array('cultureFallback' => true)))) ?>

</section> <!-- /section#standardNumberArea -->

<section id="accessPointsArea">

  <?php if (check_field_visibility('app_element_visibility_rad_access_points_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'accessPointsArea', 'title' => __('Edit access points'))) ?>
  <?php endif; ?>

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource)) ?>

</section> <!-- /section#accessPointsArea -->

<section class="section" id="descriptionControlArea">

  <?php if (check_field_visibility('app_element_visibility_rad_description_control_area')): ?>
    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'descriptionControlArea', 'title' => __('Edit control area'))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_description_identifier')): ?>
    <?php echo render_show(__('Description record identifier'), render_value($resource->descriptionIdentifier)) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_institution_identifier')): ?>
    <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_rules_conventions')): ?>
    <?php echo render_show(__('Rules or conventions'), render_value($resource->getRules(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_status')): ?>
    <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_level_of_detail')): ?>
    <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_dates')): ?>
    <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_language')): ?>
    <div class="field">
      <h3><?php echo __('Language of description') ?></h3>
      <div>
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
      <div>
        <ul>
          <?php foreach ($resource->scriptOfDescription as $code): ?>
            <li><?php echo format_script($code) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_rad_control_sources')): ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <section id="rightsArea">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </section> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>

  <?php echo get_partial('digitalobject/metadata', array('resource' => $resource->digitalObjects[0])) ?>

  <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>
<?php end_slot() ?>
