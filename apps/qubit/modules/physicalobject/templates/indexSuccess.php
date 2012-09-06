<h1><?php echo __('View %1%', array('%1%' => sfConfig::get('app_ui_label_physicalobject'))) ?></h1>

<h1 class="label printable">
  <?php echo render_title($resource) ?>
  <?php echo link_to(image_tag('printer-icon.png', array('alt' => __('Print'))), array($resource, 'module' => 'physicalobject', 'action' => 'boxList'), array('id' => 'print-button', 'title' => __('Print'))) ?>
</h1>

<div class="section">

  <?php echo render_show(__('Type'), $resource->type) ?>

  <?php echo render_show(__('Location'), $resource->getLocation(array('cultureFallback' => true))) ?>

  <div class="field">
    <h3><?php echo __('Related resources') ?></h3>
    <div>
      <ul>
        <?php foreach (QubitRelation::getRelatedObjectsBySubjectId('QubitInformationObject', $resource->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID)) as $item): ?>
          <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

</div>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'physicalobject', 'action' => 'edit')) ?></li>
      <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'physicalobject', 'action' => 'delete', 'next' => $sf_request->getReferer()), array('class' => 'delete')) ?></li>
    </ul>
  </div>

</div>
