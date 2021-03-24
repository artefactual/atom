<?php use_helper('Text') ?>

<?php if ($usageType == QubitTerm::MASTER_ID): ?>

  <?php if (isset($link)): ?>
    <?php echo image_tag($representation->getFullPath(), array('alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
  <?php else: ?>
    <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
  <?php endif; ?>

<?php elseif ($usageType == QubitTerm::CHAPTERS_ID): ?>

  <?php if ($showMediaPlayer): ?>
    <track kind="chapters" src="<?php echo public_path($representation->getFullPath()) ?>" srclang="">
  <?php endif; ?>

<?php elseif ($usageType == QubitTerm::SUBTITLES_ID): ?>

  <?php if ($showMediaPlayer): ?>
    <?php foreach ($representation as $subtitle): ?>
      <track kind="subtitles" src="<?php echo public_path($subtitle->getFullPath()) ?>" srclang="<?php echo $subtitle->language ?>" label="<?php echo format_language($subtitle->language) ?>">
    <?php endforeach; ?>
  <?php endif; ?>

<?php elseif ($usageType == QubitTerm::REFERENCE_ID): ?>

  <?php if ($showMediaPlayer): ?>
    <video preload="metadata" class="mediaelement-player" src="<?php echo public_path($representation->getFullPath()) ?>">
      <?php echo get_component('digitalobject', 'show', array('resource' => $resource, 'usageType' => QubitTerm::CHAPTERS_ID)) ?>
      <?php echo get_component('digitalobject', 'show', array('resource' => $resource, 'usageType' => QubitTerm::SUBTITLES_ID)) ?>
    </video>
  <?php else: ?>
    <div style="text-align: center">
      <?php echo image_tag($representation->getFullPath(), array('style' => 'border: #999 1px solid', 'alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
    </div>
  <?php endif;?>

  <!-- link to download master -->
  <?php if (isset($link)): ?>
    <?php echo link_to(__('Download movie'), $link, array('class' => 'download')) ?>
  <?php endif; ?>

<?php elseif (QubitTerm::THUMBNAIL_ID == $usageType): ?>

  <?php if ($iconOnly): ?>

    <?php if (isset($link)): ?>
      <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
    <?php else: ?>
      <?php echo image_tag($representation->getFullPath(), array('alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
    <?php endif; ?>

  <?php else: ?>

    <div class="digitalObject">
      <div class="digitalObjectRep">
        <?php if (isset($link)): ?>
          <?php echo link_to(image_tag($representation->getFullPath(), array('alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
        <?php else: ?>
          <?php echo image_tag($representation->getFullPath(), array('alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
        <?php endif; ?>
      </div>
      <div class="digitalObjectDesc">
        <?php echo wrap_text($resource->name, 18) ?>
      </div>
    </div>

  <?php endif; ?>

<?php endif; ?>
