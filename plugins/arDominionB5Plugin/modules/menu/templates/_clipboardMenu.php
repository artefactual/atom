<li class="nav-item dropdown d-flex flex-column">
  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="<?php echo $menu->getName(); ?>-menu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-2x fa-paperclip" aria-hidden="true"></i>
    <span class="d-lg-none ms-2" aria-hidden="true"><?php echo $menu->getLabel(['cultureFallback' => true]); ?></span>
    <span class="visually-hidden"><?php echo $menu->getLabel(['cultureFallback' => true]); ?></span>
  </a>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="<?php echo $menu->getName(); ?>-menu">
    <li>
      <h6 class="dropdown-header">
        <?php echo __('Clipboard'); ?>
      </h6>
    </li>
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
