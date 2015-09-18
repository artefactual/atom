<?php if ('print' == $sf_request->getParameter('media')): ?>
  <div id="preview-message">
    <?php echo __('Print preview') ?>
    <?php echo link_to(__('Close'), array_diff($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('media' => 'print'))) ?>
  </div>
<?php endif; ?>
