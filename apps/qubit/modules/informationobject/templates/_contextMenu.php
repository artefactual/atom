<?php if ($sf_user->getAttribute('search-realm') && sfConfig::get('app_enable_institutional_scoping')): ?>
  <?php include_component('repository', 'holdingsInstitution', array('resource' => QubitRepository::getById($sf_user->getAttribute('search-realm')))) ?>
<?php else: ?>
  <?php echo get_component('repository', 'logo') ?>
<?php endif; ?>

<?php echo get_component('informationobject', 'treeView') ?>

<?php echo get_component('menu', 'staticPagesMenu') ?>
