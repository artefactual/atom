<?php if ($sf_user->getAttribute('search-realm') && sfConfig::get('app_enable_institutional_scoping')): ?>
  <?php include_component('repository', 'holdingsInstitution', array('resource' => QubitRepository::getById($sf_user->getAttribute('search-realm')))) ?>
<?php else: ?>
  <?php echo get_component('repository', 'logo') ?>
<?php endif; ?>

<?php echo get_component('informationobject', 'treeView') ?>

<?php echo get_component('menu', 'staticPagesMenu') ?>

<?php // echo get_component('informationobject', 'creator', array('resource' => $resource)) ?>

<?php // echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>
