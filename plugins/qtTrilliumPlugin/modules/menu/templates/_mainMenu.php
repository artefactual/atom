<?php // Using $sf_user->hasGroup() since it relies on database,
      // $sf_user->hasCredential('administrator') relies on session storage
      // This adds more db access but we are caching anyways
      // See also issue 2266
      $isAdministrator = $sf_user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID) ?>

<li class="menu add">
  <a href="#" class="menu"><?php echo $addMenu->getLabel(array('cultureFallback' => true)) ?></a>
  <?php echo QubitMenu::displayHierarchyAsList($addMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>
</li>

<li class="menu">
  <a href="#" class="menu"><?php echo $manageMenu->getLabel(array('cultureFallback' => true)) ?></a>
  <?php echo QubitMenu::displayHierarchyAsList($manageMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>
</li>

<li class="menu">
  <a href="#" class="menu"><?php echo $importMenu->getLabel(array('cultureFallback' => true)) ?></a>
  <?php echo QubitMenu::displayHierarchyAsList($importMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>
</li>

<?php if ($isAdministrator): ?>
  <li class="menu">
    <a href="#" class="menu"><?php echo $adminMenu->getLabel(array('cultureFallback' => true)) ?></a>
    <?php echo QubitMenu::displayHierarchyAsList($adminMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>
  </li>
<?php endif; ?>
