<nav>
  <div id="browse-menu">

    <button class="top-item top-dropdown" data-toggle="dropdown" data-target="#" aria-expanded="false"><?php echo $browseMenuInstitution->getLabel(array('cultureFallback' => true)) ?></button>

    <div class="top-dropdown-container top-dropdown-container-right">

      <div class="top-dropdown-arrow">
        <div class="arrow"></div>
      </div>

      <div class="top-dropdown-header">
        <h2><?php echo $browseMenuInstitution->getLabel(array('cultureFallback' => true)) ?></h2>
      </div>

      <div class="top-dropdown-body">
        <ul>
          <?php echo QubitMenu::displayHierarchyAsList($browseMenuInstitution, 0) ?>
        </ul>
      </div>

      <div class="top-dropdown-bottom"></div>

    </div>
  </div>
</nav>
