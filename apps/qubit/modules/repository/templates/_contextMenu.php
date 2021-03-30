<?php if ('QubitRepository' !== $class) { ?>
  <?php include_component('repository', 'logo'); ?>
<?php } else { ?>

  <?php if (sfConfig::get('app_enable_institutional_scoping')) { ?>
    <?php include_component('repository', 'holdingsInstitution', ['resource' => $resource]); ?>
    <?php include_component('repository', 'holdingsList', ['resource' => $resource]); ?>
    <?php include_component('repository', 'uploadLimit', ['resource' => $resource]); ?>
  <?php } else { ?>
    <?php include_component('repository', 'logo'); ?>
    <?php include_component('repository', 'uploadLimit', ['resource' => $resource]); ?>
    <?php include_component('repository', 'holdings', ['resource' => $resource]); ?>
    <?php include_component('repository', 'holdingsList', ['resource' => $resource]); ?>
  <?php } ?>

<?php } ?>
