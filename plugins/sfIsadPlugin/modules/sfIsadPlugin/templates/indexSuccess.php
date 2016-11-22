<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($isad) ?></h1>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error->getMessage(ESC_RAW) ?></li>
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

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

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

  <?php if (check_field_visibility('app_element_visibility_isad_identity_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Identity area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Reference code'), render_value($isad->referenceCode), array('fieldLabel' => 'referenceCode')) ?>

  <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true))), array('fieldLabel' => 'title')) ?>

  <div class="field">
    <h3><?php echo __('Date(s)') ?></h3>
    <div class="creationDates">
      <ul>
        <?php foreach ($resource->getDates() as $item): ?>
          <li>
            <?php echo Qubit::renderDateStartEnd($item->getDate(array('cultureFallback' => true)), $item->startDate, $item->endDate) ?> (<?php echo $item->getType(array('cultureFallback' => true)) ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription), array('fieldLabel' => 'levelOfDescription')) ?>

  <?php echo render_show(__('Extent and medium'), render_value($resource->getCleanExtentAndMedium(array('cultureFallback' => true))), array('fieldLabel' => 'extentAndMedium')) ?>
</section> <!-- /section#identityArea -->

<section id="contextArea">

  <?php if (check_field_visibility('app_element_visibility_isad_context_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Context area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'contextArea', 'title' => __('Edit context area'))) ?>
  <?php endif; ?>

  <div class="creatorHistories">
    <?php echo get_component('informationobject', 'creatorDetail', array(
      'resource' => $resource,
      'creatorHistoryLabels' => $creatorHistoryLabels)) ?>
  </div>

  <div class="relatedFunctions">
    <?php foreach ($functionRelations as $item): ?>
      <div class="field">
        <h3><?php echo __('Related function')?></h3>
        <div>
          <?php echo link_to(render_title($item->subject->getLabel()), array($item->subject, 'module' => 'function')) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="repository">
    <?php echo render_show_repository(__('Repository'), $resource) ?>
  </div>

  <?php if (check_field_visibility('app_element_visibility_isad_archival_history')): ?>
    <?php echo render_show(__('Archival history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true))), array('fieldLabel' => 'archivalHistory')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_immediate_source')): ?>
    <?php echo render_show(__('Immediate source of acquisition or transfer'), render_value($resource->getAcquisition(array('cultureFallback' => true))), array('fieldLabel' => 'immediateSourceOfAcquisitionOrTransfer')) ?>
  <?php endif; ?>

</section> <!-- /section#contextArea -->

<section id="contentAndStructureArea">

  <?php if (check_field_visibility('app_element_visibility_isad_content_and_structure_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Content and structure area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'contentAndStructureArea', 'title' => __('Edit content and structure area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true))), array('fieldLabel' => 'scopeAndContent')) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_appraisal_destruction')): ?>
    <?php echo render_show(__('Appraisal, destruction and scheduling'), render_value($resource->getAppraisal(array('cultureFallback' => true))), array('fieldLabel' => 'appraisalDestructionAndScheduling')) ?>
  <?php endif; ?>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(array('cultureFallback' => true))), array('fieldLabel' => 'accruals')) ?>

  <?php echo render_show(__('System of arrangement'), render_value($resource->getArrangement(array('cultureFallback' => true))), array('fieldLabel' => 'systemOfArrangement')) ?>
</section> <!-- /section#contentAndStructureArea -->

<section id="conditionsOfAccessAndUseArea">

  <?php if (check_field_visibility('app_element_visibility_isad_conditions_of_access_use_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Conditions of access and use area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'conditionsOfAccessAndUseArea', 'title' => __('Edit conditions of access and use area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Conditions governing access'), render_value($resource->getAccessConditions(array('cultureFallback' => true))), array('fieldLabel' => 'conditionsGoverningAccess')) ?>

  <?php echo render_show(__('Conditions governing reproduction'), render_value($resource->getReproductionConditions(array('cultureFallback' => true))), array('fieldLabel' => 'conditionsGoverningReproduction')) ?>

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

  <?php echo render_show(__('Language and script notes'), render_value($isad->languageNotes), array('fieldLabel' => 'languageAndScriptNotes')) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_physical_condition')): ?>
    <?php echo render_show(__('Physical characteristics and technical requirements'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true))), array('fieldLabel' => 'physicalCharacteristics')) ?>
  <?php endif; ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(array('cultureFallback' => true))), array('fieldLabel' => 'findingAids')) ?>

  <?php echo get_component('informationobject', 'findingAidLink', array('resource' => $resource)) ?>

</section> <!-- /section#conditionsOfAccessAndUseArea -->

<section id="alliedMaterialsArea">

  <?php if (check_field_visibility('app_element_visibility_isad_allied_materials_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Allied materials area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'alliedMaterialsArea', 'title' => __('Edit alied materials area'))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Existence and location of originals'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true))), array('fieldLabel' => 'existenceAndLocationOfOriginals')) ?>

  <?php echo render_show(__('Existence and location of copies'), render_value($resource->getLocationOfCopies(array('cultureFallback' => true))), array('fieldLabel' => 'existenceAndLocationOfCopies')) ?>

  <?php echo render_show(__('Related units of description'), render_value($resource->getRelatedUnitsOfDescription(array('cultureFallback' => true))), array('fieldLabel' => 'relatedUnitsOfDescription')) ?>

  <div class="relatedMaterialDescriptions">
    <?php echo get_partial('informationobject/relatedMaterialDescriptions', array('resource' => $resource, 'template' => 'isad')) ?>
  </div>

  <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID)) as $item): ?>
    <?php echo render_show(__('Publication note'), render_value($item->getContent(array('cultureFallback' => true))), array('fieldLabel' => 'publicationNote')) ?>
  <?php endforeach; ?>
</section> <!-- /section#alliedMaterialsArea -->

<section id="notesArea">

  <?php if (check_field_visibility('app_element_visibility_isad_notes_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'notesArea', 'title' => __('Edit notes area'))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_notes')): ?>
    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('Note'), render_value($item->getContent(array('cultureFallback' => true))), array('fieldLabel' => 'generalNote')) ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="alternativeIdentifiers">
    <?php echo get_partial('informationobject/alternativeIdentifiersIndex', array('resource' => $resource)) ?>
  </div>
</section> <!-- /section#notesArea -->

<section id="accessPointsArea">

  <?php if (check_field_visibility('app_element_visibility_isad_access_points_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'accessPointsArea', 'title' => __('Edit access points'))) ?>
  <?php endif; ?>

  <div class="subjectAccessPoints">
    <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource)) ?>
  </div>

  <div class="placeAccessPoints">
    <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource)) ?>
  </div>

  <div class="nameAccessPoints">
    <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>
  </div>

  <div class="genreAccessPoints">
    <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource)) ?>
  </div>
</section> <!-- /section#accessPointsArea -->

<section id="descriptionControlArea">

  <?php if (check_field_visibility('app_element_visibility_isad_description_control_area')): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Description control area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'descriptionControlArea', 'title' => __('Edit description control area'))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_description_identifier')): ?>
    <?php echo render_show(__('Description identifier'), render_value($resource->getDescriptionIdentifier(array('cultureFallback' => true))), array('fieldLabel' => 'descriptionIdentifier')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_institution_identifier')): ?>
    <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true))), array('fieldLabel' => 'institutionIdentifier')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_rules_conventions')): ?>
    <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(array('cultureFallback' => true))), array('fieldLabel' => 'rulesAndOrConventionsUsed')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_status')): ?>
    <?php echo render_show(__('Status'), render_value($resource->descriptionStatus), array('fieldLabel' => 'descriptionStatus')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_level_of_detail')): ?>
    <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail), array('fieldLabel' => 'levelOfDetail')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_dates')): ?>
    <?php echo render_show(__('Dates of creation revision deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true))), array('fieldLabel' => 'datesOfCreationRevisionDeletion')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_languages')): ?>
    <div class="field">
      <h3><?php echo __('Language(s)') ?></h3>
      <div class="languages">
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
      <div class="scripts">
        <ul>
          <?php foreach ($resource->scriptOfDescription as $code): ?>
            <li><?php echo format_script($code) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_sources')): ?>
    <?php echo render_show(__('Sources'), render_value($resource->getSources(array('cultureFallback' => true))), array('fieldLabel' => 'sources')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_archivists_notes')): ?>
    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('Archivist\'s note'), render_value($item->getContent(array('cultureFallback' => true))), array('fieldLabel' => 'archivistNote')) ?>
    <?php endforeach; ?>
  <?php endif; ?>

</section> <!-- /section#descriptionControlArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <div class="section" id="rightsArea">

    <h2><?php echo __('Rights area') ?> </h2>

    <div class="relatedRights">
      <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>
    </div>

  </div> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>

  <div class="digitalObjectMetadata">
    <?php echo get_component('digitalobject', 'metadata', array('resource' => $resource->digitalObjects[0], 'infoObj' => $resource)) ?>
  </div>

  <div class="digitalObjectRights">
    <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>
  </div>

<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <div class="accessions">
    <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>
  </div>

</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource, 'renameForm' => $renameForm)) ?>
<?php end_slot() ?>
