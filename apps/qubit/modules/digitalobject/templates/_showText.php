<?php if (isset($link)): ?>
  <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link, array('target' => '_blank')) ?>
<?php else: ?>
  <?php echo image_tag($representation->getFullPath(), array('alt' => '')) ?>
<?php endif; ?>
