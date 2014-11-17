<?php use_helper('Text') ?>

<div class="digitalObject">

  <div class="digitalObjectRep">
    <?php if (isset($link) && $canReadMaster): ?>
      <?php echo link_to(image_tag($representation->getFullPath()), $link, array('target' => '_blank')) ?>
    <?php else: ?>
      <?php echo image_tag($representation->getFullPath()) ?>
    <?php endif; ?>
  </div>

  <div class="digitalObjectDesc">
    <?php echo wrap_text($resource->name, 18) ?>
  </div>

</div>
