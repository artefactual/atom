<?php use_helper('Text'); ?>

<div class="digitalObject">

  <div class="digitalObjectRep">
    <?php if (isset($link) && $canReadMaster) { ?>
      <?php echo link_to(image_tag($representation->getFullPath(), ['alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')])]), $link, ['target' => '_blank']); ?>
    <?php } else { ?>
      <?php echo image_tag($representation->getFullPath(), ['alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', ['%1%' => sfConfig::get('app_ui_label_digitalobject')])]); ?>
    <?php } ?>
  </div>

  <div class="digitalObjectDesc">
    <?php echo wrap_text($resource->name, 18); ?>
  </div>

</div>
