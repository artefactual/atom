<?php
  // If $resource is not a repository, just show the repository logo
  if ($class !== 'QubitRepository'):
?>
  <?php include_component('repository', 'logo') ?>
<?php else: ?>

  <?php if (sfConfig::get('app_enable_institutional_scoping')): ?>
    <?php include_component('repository', 'holdingsInstitution', array('resource' => $resource)) ?>
    <?php include_component('repository', 'holdingsList', array('resource' => $resource)) ?>
    <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
  <?php else: ?>
    <?php include_component('repository', 'logo') ?>
    <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
    <?php include_component('repository', 'holdings', array('resource' => $resource)) ?>
    <?php include_component('repository', 'holdingsList', array('resource' => $resource)) ?>
  <?php endif; // sfConfig::get('app_enable_institutional_scoping') ?>

<?php endif; // $class !== 'QubitRepository' ?>
