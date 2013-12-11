<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($isad) ?></h1>

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

<section id="identityArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Identity area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity area'))) ?>

  <?php echo render_show(__('Reference code'), render_value($isad->referenceCode)) ?>

  <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Date(s)') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getDates() as $item): ?>
          <li>
            <?php echo Qubit::renderDateStartEnd($item->getDate(array('cultureFallback' => true)), $item->startDate, $item->endDate) ?> (<?php echo $item->getType(array('cultureFallback' => true)) ?>)
            <?php if (isset($item->actor)): ?>
              <?php echo link_to(render_title($item->actor), array($item->actor, 'module' => 'actor')) ?>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription)) ?>

  <?php echo render_show(__('Extent and medium'), render_value($resource->getExtentAndMedium(array('cultureFallback' => true)))) ?>

</section> <!-- /section#identityArea -->

<section id="contextArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Context area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'contextArea', 'title' => __('Edit context area'))) ?>

  <?php echo get_component('informationobject', 'creatorDetail', array('resource' => $resource)) ?>

  <?php foreach ($functionRelations as $item): ?>
    <div class="field">
      <h3><?php echo __('Related function')?></h3>
      <div>
        <?php echo link_to(render_title($item->subject->getLabel()), array($item->subject, 'module' => 'function')) ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php echo render_show_repository(__('Repository'), $resource) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_archival_history')): ?>
    <?php echo render_show(__('Archival history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_immediate_source')): ?>
    <?php echo render_show(__('Immediate source of acquisition or transfer'), render_value($resource->getAcquisition(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

</section> <!-- /section#contextArea -->

<section id="contentAndStructureArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Content and structure area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'contentAndStructureArea', 'title' => __('Edit content and structure area'))) ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_appraisal_destruction')): ?>
    <?php echo render_show(__('Appraisal, destruction and scheduling'), render_value($resource->getAppraisal(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('System of arrangement'), render_value($resource->getArrangement(array('cultureFallback' => true)))) ?>

</section> <!-- /section#contentAndStructureArea -->

<section id="conditionsOfAccessAndUseArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Conditions of access and use area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'conditionsOfAccessAndUseArea', 'title' => __('Edit conditions of access and use area'))) ?>

  <?php echo render_show(__('Conditions governing access'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Conditions governing reproduction'), render_value($resource->getReproductionConditions(array('cultureFallback' => true)))) ?>

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

  <?php echo render_show(__('Language and script notes'), render_value($isad->languageNotes)) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_physical_condition')): ?>
    <?php echo render_show(__('Physical characteristics and technical requirements'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(array('cultureFallback' => true)))) ?>

</section> <!-- /section#conditionsOfAccessAndUseArea -->

<section id="alliedMaterialsArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Allied materials area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'alliedMaterialsArea', 'title' => __('Edit alied materials area'))) ?>

  <?php echo render_show(__('Existence and location of originals'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Existence and location of copies'), render_value($resource->getLocationOfCopies(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Related units of description'), render_value($resource->getRelatedUnitsOfDescription(array('cultureFallback' => true)))) ?>

  <?php echo get_partial('informationobject/relatedMaterialDescriptions', array('resource' => $resource, 'template' => 'isad')) ?>

  <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID)) as $item): ?>
    <?php echo render_show(__('Publication note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
  <?php endforeach; ?>

</section> <!-- /section#alliedMaterialsArea -->

<section id="notesArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'notesArea', 'title' => __('Edit notes area'))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_notes')): ?>
    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('Note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php echo get_partial('informationobject/alternativeIdentifiersIndex', array('resource' => $resource)) ?>

</section> <!-- /section#notesArea -->

<section id="accessPointsArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'accessPointsArea', 'title' => __('Edit access points'))) ?>

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>

</section> <!-- /section#accessPointsArea -->

<section id="descriptionControlArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Description control area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'descriptionControlArea', 'title' => __('Edit description control area'))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_description_identifier')): ?>
    <?php echo render_show(__('Description identifier'), render_value($resource->getDescriptionIdentifier(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_institution_identifier')): ?>
    <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_rules_conventions')): ?>
    <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_status')): ?>
    <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_level_of_detail')): ?>
    <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_dates')): ?>
    <?php echo render_show(__('Dates of creation revision deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_languages')): ?>
    <div class="field">
      <h3><?php echo __('Language(s)') ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->languageOfDescription as $code): ?>
            <li><?php echo format_language($code) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_scripts')): ?>
    <div class="field">
      <h3><?php echo __('Script(s)') ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->scriptOfDescription as $code): ?>
            <li><?php echo format_script($code) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_sources')): ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_archivists_notes')): ?>
    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('Archivist\'s note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
    <?php endforeach; ?>
  <?php endif; ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <div class="section" id="rightsArea">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </div> <!-- /section#rightsArea -->

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
