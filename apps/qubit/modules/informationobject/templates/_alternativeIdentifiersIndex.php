<div class="field">

  <h3><?php echo __('Alternative identifier(s)') ?></h3>

  <div>
    <?php foreach ($resource->getProperties(null, 'alternativeIdentifiers') as $item): ?>
      <?php echo render_show(render_value_inline($item->name), render_value_inline($item->getValue(array('cultureFallback' => true)))) ?>
    <?php endforeach; ?>
  </div>

</div>
