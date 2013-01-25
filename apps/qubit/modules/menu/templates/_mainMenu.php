<?php // Using $sf_user->hasGroup() since it relies on database,
      // $sf_user->hasCredential('administrator') relies on session storage
      // This adds more db access but we are caching anyways
      // See also issue 2266
      $isAdministrator = $sf_user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID) ?>
<?php echo QubitMenu::displayHierarchyAsList($mainMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>

<div id="main-menu">

  <a href="javascript:void(0);" class="top-item top-dropdown"><?php echo __('Add') ?></a>

  <div class="top-dropdown-container">

    <div class="top-dropdown-header">
      <?php echo __('Add') ?>
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

    <div class="top-dropdown-bottom">
      <p>Bottom</p>
    </div>

  </div>

</div>
