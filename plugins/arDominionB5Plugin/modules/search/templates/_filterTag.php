<a 
  href="<?php echo url_for(
      ['module' => $module, 'action' => $action]
      + $sf_data->getRaw('getParams')
  ); ?>"
  class="btn btn-sm atom-btn-white">
  <span class="visually-hidden"><?php echo __('Remove filter:'); ?></span>
  <?php echo $label ?: render_title($object); ?>
  <i aria-hidden="true" class="fas fa-times ms-2 text-primary"></i>
</a>
