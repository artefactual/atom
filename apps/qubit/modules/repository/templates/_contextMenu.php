<?php if (isset($resource) && ($resource->getRawValue() instanceof QubitRepository) && sfConfig::get('app_enable_institutional_scoping')): ?>
  <?php include_component('repository', 'holdingsInstitution', array('resource' => $resource)) ?>
  <?php include_component('repository', 'holdingsList', array('resource' => $resource)) ?>
  <?php if (QubitAcl::check($resource, 'update')): ?>
    <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
  <?php endif; ?>
<?php else: ?>
  <?php echo get_component('repository', 'logo') ?>

  <?php if ($resource->getRawValue() instanceof QubitRepository): ?>
    <?php if (QubitAcl::check($resource, 'update')): ?>
      <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
    <?php endif; ?>

    <?php include_component('repository', 'holdings', array('resource' => $resource)) ?>
    <?php include_component('repository', 'holdingsList', array('resource' => $resource)) ?>
  <?php endif; ?>
<?php endif; ?>
