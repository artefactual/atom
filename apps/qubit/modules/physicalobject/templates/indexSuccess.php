<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <div class="multiline-header">
    <?php echo link_to(image_tag('printer-icon.png', array('alt' => __('Print'))), array($resource, 'module' => 'physicalobject', 'action' => 'boxList'), array('id' => 'print-button', 'title' => __('Print'))) ?>
    <h1><?php echo render_title($resource) ?></h1>
    <span class="sub"><?php echo __('View %1%', array('%1%' => sfConfig::get('app_ui_label_physicalobject'))) ?></span>
  </div>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>
<?php end_slot() ?>

<?php echo render_show(__('Type'), $resource->type) ?>

<?php echo render_show(__('Location'), $resource->getLocation(array('cultureFallback' => true))) ?>

<div class="field">
  <h3><?php echo __('Related resources') ?></h3>
  <div>
    <ul>
      <?php foreach (QubitRelation::getRelatedObjectsBySubjectId('QubitInformationObject', $resource->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID)) as $item): ?>
        <li><?php echo link_to(esc_entities(render_title($item)), array($item, 'module' => 'informationobject')) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'physicalobject', 'action' => 'edit'), array('class' => 'c-btn')) ?></li>
      <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'physicalobject', 'action' => 'delete', 'next' => $sf_request->getReferer()), array('class' => 'c-btn c-btn-delete')) ?></li>
    </ul>
  </section>
<?php end_slot() ?>
