<?php if (isset($link)): ?>
  <?php echo link_to(image_tag($representation->getFullPath()), $link) ?>
<?php else: ?>
  <?php echo image_tag($representation->getFullPath()) ?>
<?php endif; ?>
