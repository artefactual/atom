<ul>
	<?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID); ?>
  <?php echo QubitMenu::displayHierarchyAsList($browseMenu, 0) ?>
</ul>