<a 
  href="<?php echo url_for(
      ['module' => $module, 'action' => $action]
      + $sf_data->getRaw('getParams')
  ); ?>"
  class="btn btn-sm atom-btn-white align-self-start mw-100 filter-tag d-flex">
  <span class="visually-hidden">
    <?php echo __('Remove filter:'); ?>
  </span>
  <span class="text-truncate d-inline-block">
    <?php echo $label ?: render_title($object); ?>
  </span>
  <i aria-hidden="true" class="fas fa-times ms-2 align-self-center"></i>
</a>
