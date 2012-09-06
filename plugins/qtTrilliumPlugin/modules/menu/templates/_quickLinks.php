<a class="menu" href="#"><?php echo __('Quick links') ?></a>

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
