<div id="quick-links-menu" data-toggle="tooltip" data-title="<?php echo __('Quick links') ?>">

  <a class="top-item" data-toggle="dropdown" data-target="#" href="#"><?php echo __('Quick links') ?></a>

  <div class="top-dropdown-container">

    <div class="top-dropdown-arrow">
      <div class="arrow"></div>
    </div>

    <div class="top-dropdown-header">
      <h3><?php echo __('Quick links') ?></h3>
    </div>

    <div class="top-dropdown-body">
      <ul>
        <?php foreach ($quickLinksMenu->getChildren() as $child): ?>
          <?php if ('login' != $child->getName() && 'logout' != $child->getName() && 'myProfile' != $child->getName()): ?>
            <li<?php if ($child->isSelected()): ?> class="active"<?php endif; ?>><?php echo link_to($child->getLabel(array('cultureFallback' => true)), $child->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="top-dropdown-bottom"></div>

  </div>

</div>
