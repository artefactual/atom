<div>

	<a href="javascript:void(0);" class="top-item top-dropdown"><?php echo $browseMenu->getLabel(array('cultureFallback' => true)) ?></a>

	<div class="top-dropdown-container">

		<div class="top-dropdown-header">
			<?php echo $browseMenu->getLabel(array('cultureFallback' => true)) ?>
		</div>

		<div class="top-dropdown-body">
			<ul>
		    <?php echo QubitMenu::displayHierarchyAsList($browseMenu, 0) ?>
		  </ul>
		</div>

		<div class="top-dropdown-bottom">
			<p>Bottom</p>
		</div>

	</div>

</div>