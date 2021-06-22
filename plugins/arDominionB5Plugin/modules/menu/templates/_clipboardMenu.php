<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="<?php echo $menu->getName(); ?>-menu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-2x fa-paperclip" aria-hidden="true"></i>
    <span class="sr-only"><?php echo $menu->getLabel(['cultureFallback' => true]); ?></span>
  </a>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="<?php echo $menu->getName(); ?>-menu">
    <li>
      <h5 class="dropdown-item-text">
        <?php echo __('Clipboard'); ?>
      </h5>
    </li>
    <li><hr class="dropdown-divider"></li>
    <?php foreach ($menu->getChildren() as $child) { ?>
      <?php if ($child->checkUserAccess()) { ?>
        <li>
          <?php echo link_to(
              $child->getLabel(['cultureFallback' => true]),
              $child->getPath(['getUrl' => true, 'resolveAlias' => true]),
              ['class' => 'dropdown-item']
          ); ?>
        </li>
      <?php } ?>
    <?php } ?>
  </ul>
</li>
