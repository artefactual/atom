<div class="dropdown me-2 mb-2 mb-lg-0">
  <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="browse-menu" data-bs-toggle="dropdown" aria-expanded="false">
    <?php echo $browseMenu->getLabel(['cultureFallback' => true]); ?>
  </button>
  <ul class="dropdown-menu" aria-labelledby="browse-menu">
    <li>
      <h5 class="dropdown-item-text">
        <?php echo $browseMenu->getLabel(['cultureFallback' => true]); ?>
      </h5>
    </li>
    <li><hr class="dropdown-divider"></li>
    <?php foreach ($browseMenu->getChildren() as $child) { ?>
      <?php if ($child->checkUserAccess()) { ?>
        <li>
          <?php echo link_to(
              $child->getLabel(['cultureFallback' => true]),
              $child->getPath(['getUrl' => true, 'resolveAlias' => true]),
              ['class' => 'dropdown-item']
          ); ?>
        </li>
      <?php } ?>
    <?php } ?>
  </ul>
</div>
