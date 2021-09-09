<?php use_helper('Text'); ?>

<?php if (QubitTerm::CHAPTERS_ID == $usageType) { ?>

  <?php if ($showMediaPlayer) { ?>
    <track kind="chapters" src="<?php echo public_path($representation->getFullPath()); ?>" srclang="">
  <?php } ?>

<?php } elseif (QubitTerm::REFERENCE_ID == $usageType) { ?>

  <?php if ($showMediaPlayer) { ?>
    <audio class="mw-100" src="<?php echo public_path($representation->getFullPath()); ?>">
      <?php echo get_component('digitalobject', 'show', ['resource' => $resource, 'usageType' => QubitTerm::CHAPTERS_ID]); ?>
    </audio>
  <?php } else { ?>
    <div class="text-center">
      <?php echo image_tag($representation->getFullPath(), ['class' => 'img-thumbnail', 'alt' => '']); ?>
    </div>
  <?php }?>

  <?php if (isset($link) && QubitAcl::check($resource->object, 'readMaster')) { ?>
    <a href="<?php echo $link; ?>" class="btn btn-sm atom-btn-white mt-3">
      <i class="fas fa-download me-1" aria-hidden="true"></i>
      <?php echo __('Download audio'); ?>
    </a>
  <?php } ?>

<?php } elseif (QubitTerm::THUMBNAIL_ID == $usageType && isset($link)) { ?>

  <?php if ($iconOnly) { ?>

    <?php echo link_to(image_tag('play', ['alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]), 'class' => 'img-thumbnail']), $link); ?>

  <?php } else { ?>

    <div class="resource">

      <div class="resourceRep">
        <?php echo link_to(image_tag('play', ['alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]), 'class' => 'img-thumbnail']), $link); ?>
      </div>

      <div class="resourceDesc">
        <?php echo wrap_text($resource->name, 18); ?>
      </div>

    </div>

  <?php } ?>

<?php } else { ?>

  <div class="resource">

    <?php echo image_tag('play', ['alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]), 'class' => 'img-thumbnail']); ?>

  </div>

<?php } ?>
