<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header">
    <?php echo link_to(image_tag('printer-icon.png', ['alt' => __('Print')]), [$resource, 'module' => 'physicalobject', 'action' => 'boxList'], ['id' => 'print-button', 'title' => __('Print')]); ?>
    <h1><?php echo render_title($resource); ?></h1>
    <span class="sub"><?php echo __('View %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?></span>
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
  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'physicalobject', 'action' => 'edit'], ['class' => 'c-btn']); ?></li>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'physicalobject', 'action' => 'delete', 'next' => $sf_request->getReferer()], ['class' => 'c-btn c-btn-delete']); ?></li>
    </ul>
  </section>
<?php end_slot(); ?>
