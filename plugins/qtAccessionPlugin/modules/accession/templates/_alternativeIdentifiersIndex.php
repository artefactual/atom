<div class="field">
  
  <h3><?php echo __('Alternative identifier(s)'); ?></h3>

  <div>
    <?php foreach ($resource->getAlternativeIdentifiers() as $item) { ?>
      <?php echo render_show(render_value_inline($item->getType(['cultureFallback' => true])), $item->getName(['cultureFallback' => true])); ?>
      <?php if (!empty($note = $item->getNote(['cultureFallback' => true]))) { ?>
        <?php echo render_value($note); ?>
      <?php } ?>
    <?php } ?>
  </div>

</div>
