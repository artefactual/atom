<section>
  <h3><?php echo $menu->getLabel(); ?></h3>
  <ul>
    <?php foreach ($menu->getChildren() as $item) { ?>
      <li>
        <a href="<?php echo url_for($item->getPath(['getUrl' => true, 'resolveAlias' => true])); ?>">
          <?php echo $item->getLabel(['cultureFallback' => true]); ?>
        </a>
      </li>
    <?php } ?>
  </ul>
</section>
