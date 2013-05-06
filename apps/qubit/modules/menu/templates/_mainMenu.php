<div class="section" id="mainMenu">

  <h2 class="element-invisible"><?php echo __('Main menu') ?></h2>

  <div class="content">
    <?php $isAdministrator = $sf_user->isAdministrator() ?>
    <?php echo QubitMenu::displayHierarchyAsList($mainMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator, 'import' => $isAdministrator))) ?>
  </div>


</div>
