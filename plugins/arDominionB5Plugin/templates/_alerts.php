<?php $b5AlertsMap = [
    'notice' => 'warning',
    'info' => 'info',
    'error' => 'danger',
    'success' => 'success',
]; ?>
<?php foreach (['notice', 'info', 'error', 'success'] as $alertType) { ?>
  <?php if ($sf_user->hasFlash($alertType)) { ?>
    <div class="alert alert-<?php echo $b5AlertsMap[$alertType]; ?> alert-dismissible fade show" role="alert">
      <?php echo render_value_html($sf_user->getFlash($alertType)); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo __('Close'); ?>"></button>
    </div>
  <?php } ?>
<?php } ?>
