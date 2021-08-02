<div class="field <?php echo render_b5_show_field_css_classes(); ?>">

  <?php echo render_b5_show_label(__('Alternative identifier(s)')); ?>

  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <?php foreach ($resource->getAlternativeIdentifiers() as $item) { ?>
      <?php echo render_show(render_value_inline($item->getType(['cultureFallback' => true])), $item->getName(['cultureFallback' => true]), ['isSubField' => true]); ?>
      <?php if (!empty($note = $item->getNote(['cultureFallback' => true]))) { ?>
        <?php echo render_value($note); ?>
      <?php } ?>
    <?php } ?>
  </div>

</div>
