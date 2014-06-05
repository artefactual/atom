<?php use_helper('Text') ?>

<div class="digitalObject">

  <div class="digitalObjectRep">
    <?php echo image_tag($representation->getFullPath()) ?>
  </div>

  <div class="digitalObjectDesc">
    <?php echo wrap_text($resource->name, 18) ?>
  </div>

</div>