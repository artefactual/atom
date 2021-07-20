<div class="dropdown my-2 me-3">
  <button class="btn btn-sm atom-btn-secondary dropdown-toggle" type="button" id="browse-menu" data-bs-toggle="dropdown" aria-expanded="false">
    <?php echo $browseMenu->getLabel(['cultureFallback' => true]); ?>
  </button>
  <ul class="dropdown-menu mt-2" aria-labelledby="browse-menu">
    <li>
      <h6 class="dropdown-header">
        <?php echo $browseMenu->getLabel(['cultureFallback' => true]); ?>
      </h6>
    </li>
    <?php foreach ($browseMenu->getChildren() as $child) { ?>
      <?php if ($child->checkUserAccess()) { ?>
        <li <?php echo isset($child->name) ? 'id="node_'.$child->name.'"' : ''; ?>>
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
