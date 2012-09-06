<?php use_helper('Text') ?>

<div class="section">
  <h3><?php echo sfConfig::get('app_ui_label_digitalobject') ?></h3>
  <div class="imageflow" id="imageflow">
    <?php foreach ($thumbnails as $item): ?>
      <?php echo image_tag($item->getFullPath(), array('longdesc' => url_for(array($item->parent->informationObject, 'module' => 'informationobject')), 'alt' => render_title(truncate_text($item->parent->informationObject, 28)))) ?>
    <?php endforeach; ?>
  </div>

  <?php if (isset($limit) && $limit < $total): ?>
    <div class="result-count">
      <?php echo __('Results %1% to %2% of %3%', array('%1%' => 1, '%2%' => $limit, '%3%' => $total)) ?>
    </div><div>
      <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'showFullImageflow' => 'true')) ?>"><?php echo __('See all') ?></a>
    </div>
  <?php endif ?>
</div>
