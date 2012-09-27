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

</div>
