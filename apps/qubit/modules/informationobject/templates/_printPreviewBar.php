<?php if ('print' == $sf_request->getParameter('media')): ?>
<div id="preview-message">
  <?php echo __('Print preview') ?>
  <?php echo link_to('Close', array($resource, 'module' => 'informationobject')) ?>
</div>
<?php endif; ?>
