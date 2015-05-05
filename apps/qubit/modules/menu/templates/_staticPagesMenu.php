<section>
  <h3><?php echo $menu->getLabel() ?></h3>
  <ul>
    <?php foreach ($menu->getChildren() as $item): ?>
      <li>
        <a href="<?php echo url_for($item->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?>">
          <?php echo $item->getLabel(array('cultureFallback' => true)) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
