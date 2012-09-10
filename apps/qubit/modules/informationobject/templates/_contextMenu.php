<div id="navigation">

  <?php if (true || sfConfig::get('app_multi_repository')): ?>
    <div id="institution">
      <div class="institution maxi">
        <h2><?php echo render_show_repository(sfConfig::get('app_ui_label_repository'), $resource) ?></h2>
      </div>
    </div>
  <?php endif; ?>

  <ul class="nav nav-tabs">
    <li class="active"><a href="#treeview" data-toggle="tab">Treeview</a></li>
    <li><a href="#facets" data-toggle="tab">Browse</a></li>
  </ul>

  <div class="tab-content">
    <h2 class="visible-phone widebtn btn-huge indent" data-toggle="collapse" data-target="#treeview">Treeview</h2>
    <?php echo get_component('informationobject', 'treeView') ?>
    <div class="span5 tree tab-pane" id="facets">
      <p>Working in progress...</p>
    </div>
 </div>

</div>

<?php if (false): ?>

<div>

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

</div>

<?php endif; ?>




