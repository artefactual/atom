<?php // Using $sf_user->hasGroup() since it relies on database,
      // $sf_user->hasCredential('administrator') relies on session storage
      // This adds more db access but we are caching anyways
      // See also issue 2266
      $isAdministrator = $sf_user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID) ?>

<?php foreach (array($addMenu, $manageMenu, $importMenu, $adminMenu) as $menu): ?>

  <div id="main-menu">

    <a class="top-item top-dropdown" data-toggle="dropdown" data-target="#"><?php echo $menu->getLabel(array('cultureFallback' => true)) ?></a>

    <div class="top-dropdown-container">

      <div class="top-dropdown-arrow">
        <div class="arrow"></div>
      </div>

      <div class="top-dropdown-header">
        <?php echo $menu->getLabel(array('cultureFallback' => true)) ?>
      </div>

      <div class="top-dropdown-body">
        <ul>
          <?php echo QubitMenu::displayHierarchyAsList($menu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>
        </ul>
      </div>

      <div class="top-dropdown-bottom"></div>

    </div>

  </div>

<?php endforeach; ?>
