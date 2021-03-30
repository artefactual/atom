<?php foreach (['notice', 'info', 'error', 'success'] as $alertType) { ?>
  <?php if ($sf_user->hasFlash($alertType)) { ?>
    <div class="alert alert-<?php echo $alertType; ?>">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo $sf_user->getFlash($alertType, ESC_RAW); ?>
    </div>
  <?php } ?>
<?php } ?>
