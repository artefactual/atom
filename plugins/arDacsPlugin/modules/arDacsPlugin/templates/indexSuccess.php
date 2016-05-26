<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($dacs) ?></h1>

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

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Identity elements').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity elements'))) ?>

  <?php echo render_show(__('Reference code'), render_value($dacs->referenceCode)) ?>

  <?php echo render_show_repository(__('Name and location of repository'), $resource) ?>

  <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription)) ?>

  <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Date(s)') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getDates() as $item): ?>
          <li>
            <?php echo Qubit::renderDateStartEnd($item->getDate(array('cultureFallback' => true)), $item->startDate, $item->endDate) ?> (<?php echo $item->getType(array('cultureFallback' => true)) ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Extent'), render_value($resource->getCleanExtentAndMedium(array('cultureFallback' => true)))) ?>

  <?php echo get_component('informationobject', 'creatorDetail', array(
    'resource' => $resource,
    'creatorHistoryLabels' => $creatorHistoryLabels)) ?>

  <?php foreach ($functionRelations as $item): ?>
    <div class="field">
      <h3><?php echo __('Related function')?></h3>
      <div>
        <?php echo link_to(render_title($item->subject->getLabel()), array($item->subject, 'module' => 'function')) ?>
      </div>
    </div>
  <?php endforeach; ?>

</section> <!-- /section#identityArea -->

<section id="contentAndStructureArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Content and structure elements').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'contentAndStructureArea', 'title' => __('Edit context and structure elements'))) ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('System of arrangement'), render_value($resource->getArrangement(array('cultureFallback' => true)))) ?>

</section> <!-- /section#contentAndStructureArea -->

<section id="conditionsOfAccessAndUseArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Conditions of access and use elements').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'conditionsOfAccessAndUseArea', 'title' => __('Edit conditions of access and use elements'))) ?>

  <?php echo render_show(__('Conditions governing access'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_physical_condition')): ?>
    <?php echo render_show(__('Physical access'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Technical access'), render_value($dacs->__get('technicalAccess', array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Conditions governing reproduction'), render_value($resource->getReproductionConditions(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Languages of the material') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->language as $code): ?>
          <li><?php echo format_language($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Scripts of the material') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->script as $code): ?>
          <li><?php echo format_script($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Language and script notes'), render_value($dacs->languageNotes)) ?>

  <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(array('cultureFallback' => true)))) ?>

  <?php echo get_component('informationobject', 'findingAidLink', array('resource' => $resource)) ?>

</section> <!-- /section#conditionsOfAccessAndUseArea -->

<section id="acquisitionAndAppraisalArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Acquisition and appraisal elements').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'acquisitionAndAppraisalArea', 'title' => __('Edit acquisition and appraisal elements'))) ?>

  <?php echo render_show(__('Custodial history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true)))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_immediate_source')): ?>
    <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getAcquisition(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_appraisal_destruction')): ?>
    <?php echo render_show(__('Appraisal, destruction and scheduling information'), render_value($resource->getAppraisal(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(array('cultureFallback' => true)))) ?>

</section> <!-- /section#acquisitionAndAppraisalArea -->

<section id="alliedMaterialsArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Related materials elements').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'alliedMaterialsArea', 'title' => __('Edit related materials elements'))) ?>

  <?php echo render_show(__('Existence and location of originals'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Existence and location of copies'), render_value($resource->getLocationOfCopies(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Related archival materials'), render_value($resource->getRelatedUnitsOfDescription(array('cultureFallback' => true)))) ?>

  <?php echo get_partial('informationobject/relatedMaterialDescriptions', array('resource' => $resource, 'template' => 'isad')) ?>

  <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID)) as $item): ?>
    <?php echo render_show(__('Publication notes'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
  <?php endforeach; ?>

</section> <!-- /section#alliedMaterialsArea -->

<section id="notesArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes element').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'notesArea', 'title' => __('Edit notes element'))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_notes')): ?>

    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('General note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
    <?php endforeach; ?>

    <div class="field">
      <h3><?php echo __('Specialized notes') ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::DACS_NOTE_ID)) as $item): ?>
            <li><?php echo $item->type ?>: <?php echo $item->getContent(array('cultureFallback' => true)) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  <?php endif; ?>

  <?php echo get_partial('informationobject/alternativeIdentifiersIndex', array('resource' => $resource)) ?>

</section> <!-- /section#notesArea -->

<section id="descriptionControlArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Description control element').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'descriptionControlArea', 'title' => __('Edit description control element'))) ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_rules_conventions')): ?>
    <?php echo render_show(__('Rules or conventions'), render_value($resource->getRules(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_isad_control_sources')): ?>
    <?php echo render_show(__('Sources used'), render_value($resource->getSources(array('cultureFallback' => true)))) ?>
  <?php endif; ?>

  <!-- TODO: Make $archivistsNotesComponent to include ISAD 3.7.3 Date(s) of description as the first note and editable -->

  <?php if (check_field_visibility('app_element_visibility_isad_control_archivists_notes')): ?>
    <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID)) as $item): ?>
      <?php echo render_show(__('Archivist\'s note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
    <?php endforeach; ?>
  <?php endif; ?>

</section> <!-- /section#descriptionControlArea -->

<section id="accessPointsArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'accessPointsArea', 'title' => __('Edit access points'))) ?>

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>

  <?php echo get_partial('informationobject/genreAccessPoints', array('resource' => $resource)) ?>

</section> <!-- /section#accessPointsArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <div class="section" id="rightsArea">

    <?php if (QubitAcl::check($resource, 'update')): ?>
      <h2><?php echo __('Rights area') ?> </h2>
    <?php endif; ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </div> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>

  <?php echo get_component('digitalobject', 'metadata', array('resource' => $resource->digitalObjects[0], 'infoObj' => $resource)) ?>

  <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource, 'renameForm' => $renameForm)) ?>
<?php end_slot() ?>
