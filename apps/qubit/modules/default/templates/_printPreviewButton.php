<a <?php echo isset($class) ? 'class="'. $class . '"' : '' ?> href="<?php echo url_for(array_merge($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('media' => 'print'))) ?>">
  <i class="icon-print"></i>
  <?php echo __('Print preview') ?>
</a>
