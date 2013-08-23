<div id="quick-links-menu">

  <a class="top-item top-dropdown" data-toggle="dropdown" data-target="#" ><?php echo __('Quick links') ?></a>

  <div class="top-dropdown-container">

    <div class="top-dropdown-arrow">
      <div class="arrow"></div>
    </div>

    <div class="top-dropdown-header">
      <?php echo __('Quick links') ?>
    </div>

    <div class="top-dropdown-body">
      <ul>
        <?php foreach ($quickLinksMenu->getChildren() as $child): ?>
          <?php if (($sf_user->isAuthenticated()
              && 'login' != $child->getName())
            || (!$sf_user->isAuthenticated()
              && 'logout' != $child->getName()
              && 'myProfile' != $child->getName())): ?>
            <li<?php if ($child->isSelected()): ?> class="active"<?php endif; ?>><?php echo link_to($child->getLabel(array('cultureFallback' => true)), $child->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="top-dropdown-bottom"></div>

  </div>

</div>
