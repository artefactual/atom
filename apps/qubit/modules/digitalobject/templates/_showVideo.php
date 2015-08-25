<?php use_helper('Text') ?>

<?php if ($usageType == QubitTerm::MASTER_ID): ?>

  <?php if ($link == null): ?>
    <?php echo image_tag($representation->getFullPath(), array('alt' => '')) ?>
  <?php else: ?>
    <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
  <?php endif; ?>

<?php elseif ($usageType == QubitTerm::REFERENCE_ID): ?>

  <?php if ($showFlashPlayer): ?>
    <a class="flowplayer" href="<?php echo public_path($representation->getFullPath()) ?>"></a>
  <?php else: ?>
    <div style="text-align: center">
      <?php echo image_tag($representation->getFullPath(), array('style' => 'border: #999 1px solid', 'alt' => '')) ?>
    </div>
  <?php endif;?>

  <!-- link to download master -->
  <?php if ($link != null): ?>
    <?php echo link_to(__('Download movie'), $link, array('class' => 'download')) ?>
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
