<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('View accession record') ?>
    <span class="sub"><?php echo render_title($resource) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php echo render_show(__('Accession number'), render_value($resource->identifier)) ?>

<?php echo render_show(__('Acquisition date'), render_value(Qubit::renderDate($resource->date))) ?>

<?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getSourceOfAcquisition(array('cultureFallback' => true)))) ?>

<?php echo render_show(__('Location information'), render_value($resource->getLocationInformation(array('cultureFallback' => true)))) ?>

<div class="section" id="donorArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Donor/Transferring body area').'</h2>', array($resource, 'module' => 'accession', 'action' => 'edit'), array('anchor' => 'donorArea', 'title' => __('Edit donor/transferring body area'))) ?>

  <?php foreach (QubitRelation::getRelationsBySubjectId($resource->id, array('typeId' => QubitTerm::DONOR_ID)) as $item): ?>

    <?php echo render_show(__('Related donor'), link_to(esc_specialchars(render_title($item->object)), array($item->object, 'module' => 'donor'))) ?>

    <?php foreach ($item->object->contactInformations as $contactItem): ?>
      <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
    <?php endforeach; ?>

  <?php endforeach; ?>

</div> <!-- /.section#donorArea -->

<div class="section" id="administrativeArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Administrative area').'</h2>', array($resource, 'module' => 'accession', 'action' => 'edit'), array('anchor' => 'administrativeArea', 'title' => __('Edit administrative area'))) ?>

  <?php echo render_show(__('Acquisition type'), render_value($resource->acquisitionType)) ?>

  <?php echo render_show(__('Resource type'), render_value($resource->resourceType)) ?>

  <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Creators') ?></h3>
   <div>
     <ul>
       <?php $actorsShown = array(); ?>
       <?php foreach (QubitRelation::getRelationsByObjectId($resource->id, array('typeId' => QubitTerm::CREATION_ID)) as $item): ?>
         <?php if (!isset($actorsShown[$item->subject->id])): ?>
           <li><?php echo link_to(render_title($item->subject), array($item->subject, 'module' => 'actor')) ?></li>
         <?php endif; ?>
         <?php $actorsShown[$item->subject->id] = true; ?>
       <?php endforeach; ?>
     </ul>
   </div>
  </div>

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

  <?php echo render_show(__('Archival/Custodial history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Appraisal, destruction and scheduling'), render_value($resource->getAppraisal(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Received extent units'), render_value($resource->getReceivedExtentUnits(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Processing status'), render_value($resource->processingStatus)) ?>

  <?php echo render_show(__('Processing priority'), render_value($resource->processingPriority)) ?>

  <?php echo render_show(__('Processing notes'), render_value($resource->getProcessingNotes(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Accruals') ?></h3>
    <div>
      <ul>
        <?php foreach (QubitRelation::getRelationsByObjectId($resource->id, array('typeId' => QubitTerm::ACCRUAL_ID)) as $item): ?>
          <li><?php echo link_to(render_title($item->subject), array($item->subject, 'module' => 'accession')) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Accrual to') ?></h3>
    <div>
      <ul>
        <?php foreach (QubitRelation::getRelationsBySubjectId($resource->id, array('typeId' => QubitTerm::ACCRUAL_ID)) as $item): ?>
          <li><?php echo link_to(render_title($item->object), array($item->object, 'module' => 'accession')) ?></li>
          <?php $accrued = true ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

</div> <!-- /.section#administrativeArea -->

<div class="section" id="rightsArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'accession', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

  <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

</div> <!-- /.section#rightsArea -->

<div class="section" id="informationObjectArea">

  <h2><?php echo __('%1% area', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?></h2>

  <?php foreach (QubitRelation::getRelationsByObjectId($resource->id, array('typeId' => QubitTerm::ACCESSION_ID)) as $item): ?>

    <div class="field">
      <h3><?php echo sfConfig::get('app_ui_label_informationobject') ?></h3>
      <div>
        <?php echo link_to(esc_specialchars(render_title($item->subject)), array($item->subject, 'module' => 'informationobject')) ?>
      </div>
    </div>

  <?php endforeach; ?>

</div> <!-- /.section#deaccessionArea -->

<div class="section" id="deaccessionArea">

  <h2><?php echo __('Deaccession area') ?></h2>

  <?php foreach ($resource->deaccessions as $item): ?>

    <div class="field">
      <h3><?php echo __('Deaccession') ?></h3>
      <div>
        <?php echo link_to(render_title($item), array($item, 'module' => 'deaccession')) ?>
      </div>
    </div>

  <?php endforeach; ?>

</div> <!-- /.section#deaccessionArea -->

<?php slot('after-content') ?>
  <section class="actions">
    <ul>

      <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'accession', 'action' => 'edit'), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'accession', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
      <?php endif; ?>

      <li><?php echo link_to(__('Deaccession'), array('module' => 'deaccession', 'action' => 'add', 'accession' => $resource->id), array('class' => 'c-btn')) ?></li>

      <?php if (!isset($accrued)): ?>
        <li><?php echo link_to(__('Add accrual'), array('module' => 'accession', 'action' => 'add', 'accession' => url_for(array($resource, 'module' => 'accession'))), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>

      <li><?php echo link_to(__('Create %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))), array($resource, 'module' => 'accession', 'action' => 'addInformationObject'), array('class' => 'c-btn')) ?></li>
      <li>
        <div class="btn-group dropup">
          <a class="c-btn dropdown-toggle" data-toggle="dropdown" href="#">
            <?php echo __('More') ?>
            <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><?php echo link_to(__('Create new rights'), array($resource,  'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'edit')) ?></li>
          </ul>
        </div>
      </li>
    </ul>
  </section>
<?php end_slot() ?>
