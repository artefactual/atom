<span class="search-filter">
  <?php if (!empty($label)) { ?>
    <?php echo $label; ?>
  <?php } else { ?>
    <?php echo render_title($object); ?>
  <?php } ?>
  <a href="<?php echo url_for(['module' => $module, 'action' => $action] + $sf_data->getRaw('getParams')); ?>" class="remove-filter"><i class="fa fa-times"></i></a>
</span>
