<?php use_helper('Text') ?>

<?php if (QubitTerm::REFERENCE_ID == $usageType): ?>

  <?php if ($showFlashPlayer): ?>
    <a class="flowplayer audio" href="<?php echo public_path($representation->getFullPath()) ?>"></a>
  <?php else: ?>
    <div style="text-align: center">
      <?php echo image_tag($representation->getFullPath(), array('style' => 'border: #999 1px solid', 'alt' => '')) ?>
    </div>
  <?php endif;?>

  <?php if (isset($link) && QubitAcl::check($resource->informationObject, 'readMaster')): ?>
    <?php echo link_to(__('Download audio'), $link, array('class' => 'download')) ?>
  <?php endif; ?>

<?php elseif (QubitTerm::THUMBNAIL_ID == $usageType): ?>

  <?php if ($iconOnly): ?>

    <?php echo link_to(image_tag('play', array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>

  <?php else: ?>

    <div class="resource">

      <div class="resourceRep">
        <?php echo link_to(image_tag('play', array('alt' => __('Open original %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))), $link) ?>
      </div>

      <div class="resourceDesc">
        <?php echo wrap_text($resource->name, 18) ?>
      </div>

    </div>

  <?php endif; ?>

<?php endif; ?>
