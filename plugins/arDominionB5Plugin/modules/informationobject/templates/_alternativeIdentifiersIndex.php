<div class="field <?php echo render_b5_show_field_css_classes(); ?>">

  <?php echo render_b5_show_label(__('Alternative identifier(s)')); ?>

  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <?php foreach ($resource->getProperties(null, 'alternativeIdentifiers') as $item) { ?>
      <?php echo render_show(render_value_inline($item->name), $item->getValue(['cultureFallback' => true]), ['isSubField' => true]); ?>
    <?php } ?>
  </div>

</div>
