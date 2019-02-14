<?php use_helper('Text') ?>

<?php if (QubitTerm::REFERENCE_ID == $usageType): ?>

  <?php if (isset($link)): ?>
    <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __($representation->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
  <?php else: ?>
    <?php echo image_tag($representation->getFullPath(), array('alt' => __($representation->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
  <?php endif; ?>

<?php else: ?>

  <?php if ($iconOnly && isset($link)): ?>

    <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __($representation->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>

  <?php else: ?>

    <div style="width: 100px; text-align: center"/>

      <?php if (isset($link)): ?>
        <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __($representation->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
      <?php else: ?>
        <?php echo image_tag($representation->getFullPath(), array('alt' => __($representation->getDigitalObjectAltText() ?: 'Original %1% is not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
      <?php endif; ?>

      <?php echo wrap_text($digitalObject->name, 15) ?>

    </div>

  <?php endif; ?>

<?php endif; ?>
