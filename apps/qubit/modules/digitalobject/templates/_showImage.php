<?php use_helper('Text') ?>

<?php if (QubitTerm::MASTER_ID == $usageType || QubitTerm::REFERENCE_ID == $usageType): ?>

  <?php if (isset($link)): ?>
    <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link, array('target' => '_blank')) ?>
  <?php else: ?>
    <?php echo image_tag($representation->getFullPath(), array('alt' => '')) ?>
  <?php endif; ?>

<?php elseif (QubitTerm::THUMBNAIL_ID == $usageType): ?>

  <?php if ($iconOnly): ?>

    <?php if (isset($link)): ?>
      <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
    <?php else: ?>
      <?php echo image_tag($representation->getFullPath(), array('alt' => '')) ?>
    <?php endif; ?>

  <?php else: ?>

    <div class="digitalObject">

      <div class="digitalObjectRep">
        <?php if (isset($link)): ?>
          <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
        <?php else: ?>
          <?php echo image_tag($representation->getFullPath(), array('alt' => '')) ?>
        <?php endif; ?>
      </div>

      <div class="digitalObjectDesc">
        <?php echo wrap_text($resource->name, 18) ?>
      </div>

    </div>

  <?php endif; ?>

<?php endif; ?>
