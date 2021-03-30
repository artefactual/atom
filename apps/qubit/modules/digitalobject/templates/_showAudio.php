<?php use_helper('Text'); ?>

<?php if (QubitTerm::CHAPTERS_ID == $usageType) { ?>

  <?php if ($showMediaPlayer) { ?>
    <track kind="chapters" src="<?php echo public_path($representation->getFullPath()); ?>" srclang="">
  <?php } ?>

<?php } elseif (QubitTerm::REFERENCE_ID == $usageType) { ?>

  <?php if ($showMediaPlayer) { ?>
    <audio class="mediaelement-player" src="<?php echo public_path($representation->getFullPath()); ?>">
      <?php echo get_component('digitalobject', 'show', ['resource' => $resource, 'usageType' => QubitTerm::CHAPTERS_ID]); ?>
    </audio>
  <?php } else { ?>
    <div style="text-align: center">
      <?php echo image_tag($representation->getFullPath(), ['style' => 'border: #999 1px solid', 'alt' => '']); ?>
    </div>
  <?php }?>

  <?php if (isset($link) && QubitAcl::check($resource->object, 'readMaster')) { ?>
    <?php echo link_to(__('Download audio'), $link, ['class' => 'download']); ?>
  <?php } ?>

<?php } elseif (QubitTerm::THUMBNAIL_ID == $usageType && isset($link)) { ?>

  <?php if ($iconOnly) { ?>

    <?php echo link_to(image_tag('play', ['alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')])]), $link); ?>

  <?php } else { ?>

    <div class="resource">

      <div class="resourceRep">
        <?php echo link_to(image_tag('play', ['alt' => __($resource->getDigitalObjectAltText() ?: 'Open original %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')])]), $link); ?>
      </div>

      <div class="resourceDesc">
        <?php echo wrap_text($resource->name, 18); ?>
      </div>

    </div>

  <?php } ?>

<?php } else { ?>

  <div class="resource">

    <?php echo image_tag('play', ['alt' => __($resource->getDigitalObjectAltText() ?: 'Original %1% not accessible', ['%1%' => sfConfig::get('app_ui_label_digitalobject')])]); ?>

  </div>

<?php } ?>
