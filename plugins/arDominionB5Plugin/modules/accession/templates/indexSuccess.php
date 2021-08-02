<?php decorate_with('layout_3col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('View accession record'); ?>
    <span class="sub"><?php echo render_title($resource); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <?php if (check_field_visibility('app_element_visibility_physical_storage')) { ?>
    <?php echo get_component('physicalobject', 'contextMenu', ['resource' => $resource]); ?>
  <?php } ?>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <?php if (isset($errorSchema)) { ?>
    <div class="messages error alert alert-danger" role="alert">
      <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
        <?php foreach ($errorSchema as $error) { ?>
          <?php $error = sfOutputEscaper::unescape($error); ?>
          <li><?php echo $error->getMessage(); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div id="content" class="p-0">

    <div class="section border-bottom" id="basicInfo">

      <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('Basic info')), [$resource, 'module' => 'accession', 'action' => 'edit'], ['anchor' => 'basic-collapse', 'title' => __('Edit basic info'), 'class' => 'text-primary']); ?>

      <?php echo render_show(__('Accession number'), $resource->identifier); ?>

      <?php echo get_partial('accession/alternativeIdentifiersIndex', ['resource' => $resource]); ?>

      <?php echo render_show(__('Acquisition date'), render_value_inline(Qubit::renderDate($resource->date))); ?>

      <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getSourceOfAcquisition(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('Location information'), render_value($resource->getLocationInformation(['cultureFallback' => true]))); ?>

    </div> <!-- /.section#basicInfo -->

    <div class="section border-bottom" id="donorArea">

      <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('Donor/Transferring body area')), [$resource, 'module' => 'accession', 'action' => 'edit'], ['anchor' => 'donor-collapse', 'title' => __('Edit donor/transferring body area'), 'class' => 'text-primary']); ?>

      <?php foreach (QubitRelation::getRelationsBySubjectId($resource->id, ['typeId' => QubitTerm::DONOR_ID]) as $item) { ?>

        <?php echo render_show(__('Related donor'), link_to(esc_specialchars(render_title($item->object)), [$item->object, 'module' => 'donor'])); ?>

        <?php foreach ($item->object->contactInformations as $contactItem) { ?>
          <?php echo get_partial('contactinformation/contactInformation', ['contactInformation' => $contactItem]); ?>
        <?php } ?>

      <?php } ?>

    </div> <!-- /.section#donorArea -->

    <div class="section border-bottom" id="administrativeArea">

      <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('Administrative area')), [$resource, 'module' => 'accession', 'action' => 'edit'], ['anchor' => 'admin-collapse', 'title' => __('Edit administrative area'), 'class' => 'text-primary']); ?>

      <?php echo render_show(__('Acquisition type'), render_value_inline($resource->acquisitionType)); ?>

      <?php echo render_show(__('Resource type'), render_value_inline($resource->resourceType)); ?>

      <?php echo render_show(__('Title'), render_value_inline($resource->getTitle(['cultureFallback' => true]))); ?>

      <?php
          $actorsShown = [];
          $creators = [];
          foreach (QubitRelation::getRelationsByObjectId($resource->id, ['typeId' => QubitTerm::CREATION_ID]) as $item) {
              if (!isset($actorsShown[$item->subject->id])) {
                  $creators[] = link_to(render_title($item->subject), [$item->subject, 'module' => 'actor']);
              }
              $actorsShown[$item->subject->id] = true;
          }
          echo render_show(__('Creators'), $creators);
      ?>

      <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
        <?php echo render_b5_show_label(__('Date(s)')); ?>
        <div class="<?php echo render_b5_show_value_css_classes(); ?>">
          <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
            <?php foreach ($resource->getDates() as $item) { ?>
              <li>
                <?php echo render_value_inline(Qubit::renderDateStartEnd($item->getDate(['cultureFallback' => true]), $item->startDate, $item->endDate)); ?> (<?php echo $item->getType(['cultureFallback' => true]); ?>)
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>

      <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
        <?php echo render_b5_show_label(__('Event(s)')); ?>
        <div class="<?php echo render_b5_show_value_css_classes(); ?>">
          <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
            <?php foreach ($resource->accessionEvents as $event) { ?>
              <li>
                <?php echo $event->getDate(); ?> (<?php echo $event->type->getName(['cultureFallback' => true]); ?>): <?php echo $event->getAgent(['cultureFallback' => true]); ?>
                <?php $note = $event->getNote(); ?>
                <?php if (null !== $note && !empty($noteText = $note->getContent(['cultureFallback' => true]))) { ?>
                  <p><?php echo $noteText; ?></p>
                <?php } ?>
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>

      <?php echo render_show(__('Archival/Custodial history'), render_value($resource->getArchivalHistory(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('Appraisal, destruction and scheduling'), render_value($resource->getAppraisal(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('Received extent units'), render_value($resource->getReceivedExtentUnits(['cultureFallback' => true]))); ?>

      <?php echo render_show(__('Processing status'), render_value_inline($resource->processingStatus)); ?>

      <?php echo render_show(__('Processing priority'), render_value_inline($resource->processingPriority)); ?>

      <?php echo render_show(__('Processing notes'), render_value($resource->getProcessingNotes(['cultureFallback' => true]))); ?>

      <?php
          $accruals = [];
          foreach (QubitRelation::getRelationsByObjectId($resource->id, ['typeId' => QubitTerm::ACCRUAL_ID]) as $item) {
              $accruals[] = link_to(render_title($item->subject), [$item->subject, 'module' => 'accession']);
          }
          echo render_show(__('Accruals'), $accruals);
      ?>

      <?php
          $accrualsTo = [];
          foreach (QubitRelation::getRelationsBySubjectId($resource->id, ['typeId' => QubitTerm::ACCRUAL_ID]) as $item) {
              $accrualsTo[] = link_to(render_title($item->object), [$item->object, 'module' => 'accession']);
              $accrued = true;
          }
          echo render_show(__('Accrual to'), $accrualsTo);
      ?>

    </div> <!-- /.section#administrativeArea -->

    <div class="section border-bottom" id="rightsArea">

      <?php echo render_b5_section_label(__('Rights area')); ?>

      <?php echo get_component('right', 'relatedRights', ['resource' => $resource]); ?>

    </div> <!-- /.section#rightsArea -->

    <div class="section border-bottom" id="informationObjectArea">

      <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('%1% area', ['%1%' => sfConfig::get('app_ui_label_informationobject')])), [$resource, 'module' => 'accession', 'action' => 'edit'], ['anchor' => 'io-collapse', 'title' => __('Edit %1% area', ['%1%' => sfConfig::get('app_ui_label_informationobject')]), 'class' => 'text-primary']); ?>

      <?php foreach (QubitRelation::getRelationsByObjectId($resource->id, ['typeId' => QubitTerm::ACCESSION_ID]) as $item) { ?>

        <?php echo render_show(sfConfig::get('app_ui_label_informationobject'), link_to(esc_specialchars(render_title($item->subject)), [$item->subject, 'module' => 'informationobject'])); ?>

      <?php } ?>

    </div> <!-- /.section#deaccessionArea -->

    <div class="section border-bottom" id="deaccessionArea">

      <?php echo render_b5_section_label(__('Deaccession area')); ?>

      <?php foreach ($resource->deaccessions as $item) { ?>

        <?php echo render_show(__('Deaccession'), link_to(render_title($item, false), [$item, 'module' => 'deaccession'])); ?>

      <?php } ?>

    </div> <!-- /.section#deaccessionArea -->

  </div>

<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <ul class="actions nav gap-2">
    <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))) { ?>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'accession', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'delete')) { ?>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'accession', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
    <?php } ?>

    <li><?php echo link_to(__('Deaccession'), ['module' => 'deaccession', 'action' => 'add', 'accession' => $resource->id], ['class' => 'btn atom-btn-outline-light']); ?></li>

    <?php if (!isset($accrued)) { ?>
      <li><?php echo link_to(__('Add accrual'), ['module' => 'accession', 'action' => 'add', 'accession' => url_for([$resource, 'module' => 'accession'])], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <?php } ?>

    <li><?php echo link_to(__('Create %1%', ['%1%' => sfConfig::get('app_ui_label_informationobject')]), [$resource, 'module' => 'accession', 'action' => 'addInformationObject'], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <li>
      <div class="btn-group dropup">
        <button type="button" class="btn atom-btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <?php echo __('More'); ?>
        </button>
        <ul class="dropdown-menu">
          <li><?php echo link_to(__('Create new rights'), [$resource,  'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'edit'], ['class' => 'dropdown-item']); ?></li>
          <li><?php echo link_to(__('Link physical storage'), [$resource, 'module' => 'object', 'action' => 'editPhysicalObjects'], ['class' => 'dropdown-item']); ?></li>
        </ul>
      </div>
    </li>
  </ul>
<?php end_slot(); ?>
