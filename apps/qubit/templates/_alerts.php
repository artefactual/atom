<?php foreach (['notice', 'info', 'error', 'success'] as $alertType) { ?>
  <?php if ($sf_user->hasFlash($alertType)) { ?>
    <div class="alert alert-<?php echo $alertType; ?>">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo render_value_html($sf_user->getFlash($alertType)); ?>
    </div>
  <?php } ?>
<?php } ?>
