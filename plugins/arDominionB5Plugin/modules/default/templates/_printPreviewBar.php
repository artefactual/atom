<?php if ('print' == $sf_request->getParameter('media')) { ?>
  <div id="preview-message" class="fixed-top bg-light border border-bottom p-1 text-center">
    <?php echo __('Print preview'); ?>
    <?php echo link_to(__('Close'), array_diff($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), ['media' => 'print']), ['class' => 'float-end px-2']); ?>
  </div>
<?php } ?>
