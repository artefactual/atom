<div class="dropdown mb-2 d-grid">
  <button class="btn atom-btn-white dropdown-toggle" type="button" id="browse-menu-institution-button" data-bs-toggle="dropdown" aria-expanded="false">
    <?php echo $browseMenuInstitution->getLabel(['cultureFallback' => true]); ?>
  </button>
  <ul class="dropdown-menu" aria-labelledby="browse-menu-institution-button">
    <?php echo QubitMenu::displayHierarchyAsList($browseMenuInstitution, 0, ['anchorClasses' => 'dropdown-item']); ?>
  </ul>
</div>
