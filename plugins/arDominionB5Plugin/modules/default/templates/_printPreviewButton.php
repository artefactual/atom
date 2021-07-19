<a
  class="btn btn-sm atom-btn-white"
  href="<?php echo url_for(array_merge(
      $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
      ['media' => 'print']
  )); ?>">
  <i class="fas fa-print me-1" aria-hidden="true"></i>
  <?php echo __('Print preview'); ?>
</a>
