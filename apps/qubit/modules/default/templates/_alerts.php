<?php foreach($conditionalAlerts as $alertType => $message): ?>
  <div class="alert alert-<?php echo $message['type'] ?>">
    <?php if (isset($message['deleteUrl'])): ?>
      <a href="<?php echo sfOutputEscaper::unescape($message['deleteUrl']) ?>"><button type="button" class="close">&times;</button></a>
    <?php else: ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    <?php endif; ?>
    <?php echo sfOutputEscaper::unescape($message['message']) ?>
  </div>
<?php endforeach; ?>

<?php foreach(array('notice', 'info', 'error', 'success') as $alertType): ?>
  <?php if ($sf_user->hasFlash($alertType)): ?>
    <div class="alert alert-<?php echo $alertType ?>">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo $sf_user->getFlash($alertType, ESC_RAW) ?>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
