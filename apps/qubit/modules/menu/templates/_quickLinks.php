<div class="section" id="userMenu">

  <h2 class="element-invisible"><?php echo __('User menu') ?></h2>

  <div class="content">
    <ul class="clearfix links">
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

</div>
