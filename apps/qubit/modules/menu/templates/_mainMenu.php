<?php foreach (array($adminMenu, $importMenu, $manageMenu, $addMenu) as $menu): ?>

  <div id="<?php echo $menu->getName() ?>-menu">

    <a class="top-item" data-toggle="dropdown" data-target="#"><?php echo $menu->getLabel(array('cultureFallback' => true)) ?></a>

    <div class="top-dropdown-container">

      <div class="top-dropdown-arrow">
        <div class="arrow"></div>
      </div>

      <div class="top-dropdown-header">
        <?php echo $menu->getLabel(array('cultureFallback' => true)) ?>
      </div>

      <div class="top-dropdown-body">
        <ul>
          <?php echo QubitMenu::displayHierarchyAsList($menu, 0, array('overrideVisibility' => array('admin' => $sf_user->isAdministrator()))) ?>
        </ul>
      </div>

      <div class="top-dropdown-bottom"></div>

    </div>

  </div>

<?php endforeach; ?>
