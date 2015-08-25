<?php use_helper('Text') ?>

<div class="digitalObject">

  <div class="digitalObjectRep">
    <?php if (isset($link) && $canReadMaster): ?>
      <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link, array('target' => '_blank')) ?>
    <?php else: ?>
      <?php echo image_tag($representation->getFullPath(), array('alt' => '')) ?>
    <?php endif; ?>
  </div>

  <div class="digitalObjectDesc">
    <?php echo wrap_text($resource->name, 18) ?>
  </div>

</div>
