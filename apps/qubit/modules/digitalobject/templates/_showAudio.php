<?php use_helper('Text') ?>

<?php if (QubitTerm::REFERENCE_ID == $usageType): ?>

  <?php if ($showFlashPlayer): ?>
    <a class="flowplayer audio" href="<?php echo public_path($representation->getFullPath()) ?>"></a>
  <?php else: ?>
    <div style="text-align: center">
      <?php echo image_tag($representation->getFullPath(), array('style' => 'border: #999 1px solid')) ?>
    </div>
  <?php endif;?>

  <?php if (isset($link) && QubitAcl::check($resource->informationObject, 'readMaster')): ?>
    <?php echo link_to(__('Download audio'), $link, array('class' => 'download')) ?>
  <?php endif; ?>

<?php elseif (QubitTerm::THUMBNAIL_ID == $usageType): ?>

  <?php if ($iconOnly): ?>

    <?php echo link_to(image_tag('play'), $link) ?>

  <?php else: ?>

    <div class="resource">

      <div class="resourceRep">
        <?php echo link_to(image_tag('play'), $link) ?>
      </div>

      <div class="resourceDesc">
        <?php echo wrap_text($resource->name, 18) ?>
      </div>

    </div>

  <?php endif; ?>

<?php endif; ?>
