<section class="card mb-3">
  <h2 class="h5 p-3 mb-0">
    <?php echo $menu->getLabel(); ?>
  </h2>
  <div class="list-group list-group-flush">
    <?php foreach ($menu->getChildren() as $item) { ?>
      <a
        class="list-group-item list-group-item-action"
        href="<?php echo url_for($item->getPath(['getUrl' => true, 'resolveAlias' => true])); ?>">
        <?php echo $item->getLabel(['cultureFallback' => true]); ?>
      </a>
    <?php } ?>
  </div>
</section>
