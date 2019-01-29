<?php use_helper('Text') ?>

<div class="section" id="coverflow">

  <div class="imageflow clearfix" id="imageflow">
    <?php foreach ($thumbnails as $item): ?>
      <?php echo image_tag($item->getFullPath(), array('longdesc' => url_for(array($item->parent->object, 'module' => 'informationobject')), 'alt' => truncate_text(strip_markdown($item->parent->object), 100))) ?>
    <?php endforeach; ?>
  </div>

  <?php if (isset($limit) && $limit < $total): ?>
    <div class="result-count">
      <?php echo __('Results %1% to %2% of %3%', array('%1%' => 1, '%2%' => $limit, '%3%' => $total)) ?>
      <a href="<?php echo url_for(array(
        'module' => 'informationobject',
        'action' => 'browse',
        'ancestor' => $resource->id,
        'topLod' => false,
        'view' => 'card',
        'onlyMedia' => true)) ?>"><?php echo __('Show all') ?></a>
    </div>
  <?php endif ?>

</div>
