<?php if (sfConfig::get('app_multi_repository')): ?>
  <?php echo render_show_repository(sfConfig::get('app_ui_label_repository'), $resource) ?>
<?php endif; ?>

<?php echo get_component('informationobject', 'creator', array('resource' => $resource)) ?>

<?php echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>

<div>
  <h3>
    <?php if (isset($resource->getCollectionRoot()->levelOfDescription)): ?>
      <?php echo $resource->getCollectionRoot()->levelOfDescription ?>
    <?php else: ?>
      <?php echo sfConfig::get('app_ui_label_collection') ?>
    <?php endif; ?>
  </h3>
  <div>
    <?php echo get_component('informationobject', 'treeView') ?>
  </div>
</div>

<div class="section">

  <h2><?php echo __('Import') ?></h2>

  <div class="content">
    <ul class="clearfix">

      <li><?php echo link_to(__('XML'), array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml')) ?></li>
      <li><?php echo link_to(__('CSV'), array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv')) ?></li>

    </ul>
  </div>

</div>

<?php echo get_partial('informationobject/format', array('resource' => $resource)) ?>

<?php if (check_field_visibility('app_element_visibility_physical_storage')): ?>
  <?php echo get_component('physicalobject', 'contextMenu', array('resource' => $resource)) ?>
<?php endif; ?>
