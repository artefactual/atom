<div id="navigation">

  <?php if (sfConfig::get('app_multi_repository') && null !== $repository = $resource->getRepository(array('inherit' => true))): ?>
    <div class="logo">
      <a href="<?php echo url_for(array($repository, 'module' => 'repository')) ?>">
        <?php if (file_exists(sfConfig::get('sf_upload_dir').'/r/'.$repository->slug.'/conf/logo.png')): ?>
          <?php echo image_tag('/uploads/r/'.$repository->slug.'/conf/logo.png') ?>
        <?php else: ?>
          <h2><?php echo render_title($repository) ?></h2>
        <?php endif; ?>
      </a>
    </div>
  <?php endif; ?>

  <?php echo get_component('informationobject', 'treeView') ?>

  <?php // echo get_component('informationobject', 'creator', array('resource' => $resource)) ?>

  <?php // echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>

  <?php if (false): ?>
  <div>
    <h4>
      <?php if (isset($resource->getCollectionRoot()->levelOfDescription)): ?>
        <?php echo $resource->getCollectionRoot()->levelOfDescription ?>
      <?php else: ?>
        <?php echo sfConfig::get('app_ui_label_collection') ?>
      <?php endif; ?>
    </h4>
  </div>
  <?php endif; ?>

  <div class="section">
    <h3><?php echo __('Import') ?></h3>
    <div class="content">
      <ul>
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
