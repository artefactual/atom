<div class="navbar navbar-inverse navbar-fixed-top" id="header-admin">
  <div class="navbar-inner">
    <ul class="nav">
      <?php // Using $sf_user->hasGroup() since it relies on database,
            // $sf_user->hasCredential('administrator') relies on session storage
            // This adds more db access but we are caching anyways
            // See also issue 2266
            $isAdministrator = $sf_user->hasGroup(QubitAclGroup::ADMINISTRATOR_ID) ?>
      <?php echo QubitMenu::displayHierarchyAsList($mainMenu, 0, array('overrideVisibility' => array('admin' => $isAdministrator))) ?>
    </ul>
  </div>
</div>
