<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex align-items-center mb-3">
    <a href="<?php echo url_for([$resource, 'module' => 'physicalobject', 'action' => 'boxList']); ?>" class="text-reset">
      <i class="fas fa-3x fa-print me-3" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Print'); ?></span>
    </a>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="results-label">
        <?php echo render_title($resource); ?>
      </h1>
      <span class="small" id="results-label"><?php echo __('View %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?></span>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php echo render_show(__('Type'), render_value($resource->type)); ?>

<?php echo render_show(__('Location'), render_value($resource->getLocation(['cultureFallback' => true]))); ?>

<div class="field">
  <h3><?php echo __('Related resources'); ?></h3>
  <div>
    <ul>
      <?php foreach (QubitRelation::getRelatedObjectsBySubjectId('QubitInformationObject', $resource->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]) as $item) { ?>
        <li><?php echo link_to(render_title($item), [$item, 'module' => 'informationobject']); ?></li>
      <?php } ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Related accessions'); ?></h3>
  <div>
    <ul>
      <?php foreach (QubitRelation::getRelatedObjectsBySubjectId('QubitAccession', $resource->id, ['typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID]) as $item) { ?>
        <li><?php echo link_to(render_title($item), [$item, 'module' => 'accession']); ?></li>
      <?php } ?>
    </ul>
  </div>
</div>

<?php slot('after-content'); ?>
  <ul class="actions nav gap-2">
    <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'physicalobject', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'physicalobject', 'action' => 'delete', 'next' => $sf_request->getReferer()], ['class' => 'btn atom-btn-outline-danger']); ?></li>
  </ul>
<?php end_slot(); ?>
