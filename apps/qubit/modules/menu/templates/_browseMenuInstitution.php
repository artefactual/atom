<div class="dropdown d-grid">
  <button class="btn atom-btn-white dropdown-toggle text-wrap" type="button" id="browse-menu-institution-button" data-bs-toggle="dropdown" aria-expanded="false">
    <?php echo $browseMenuInstitution->getLabel(['cultureFallback' => true]); ?>
  </button>
  <ul class="dropdown-menu mt-2" aria-labelledby="browse-menu-institution-button">
    <?php echo QubitMenu::displayHierarchyAsList($browseMenuInstitution, 0, ['anchorClasses' => 'dropdown-item']); ?>
  </ul>
</div>
