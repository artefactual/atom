<?php use_helper('Text') ?>

<?php if (QubitTerm::REFERENCE_ID == $usageType): ?>

  <?php if ($showMediaPlayer): ?>
    <audio class="mediaelement-player" src="<?php echo public_path($representation->getFullPath()) ?>"></audio>
  <?php else: ?>
    <div style="text-align: center">
      <?php echo image_tag($representation->getFullPath(), array('style' => 'border: #999 1px solid', 'alt' => '')) ?>
    </div>
  <?php endif;?>

  <?php if (isset($link) && QubitAcl::check($resource->object, 'readMaster')): ?>
    <?php echo link_to(__('Download audio'), $link, array('class' => 'download')) ?>
  <?php endif; ?>

<?php elseif (QubitTerm::THUMBNAIL_ID == $usageType && isset($link)): ?>

  <?php if ($iconOnly): ?>

    <?php echo link_to(image_tag('play', array('alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>

  <?php else: ?>

    <div class="resource">

      <div class="resourceRep">
        <?php echo link_to(image_tag('play', array('alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
      </div>

      <div class="resourceDesc">
        <?php echo wrap_text($resource->name, 18) ?>
      </div>

    </div>

  <?php endif; ?>

<?php else: ?>

  <div class="resource">

    <?php echo image_tag('play', array('alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>

  </div>

<?php endif; ?>
