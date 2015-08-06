<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <?php echo $sf_user->getFlash('notice', ESC_RAW) ?>
  </div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
  <div class="alert alert-error">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <?php echo $sf_user->getFlash('error', ESC_RAW) ?>
  </div>
<?php endif; ?>
