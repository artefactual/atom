<section class="actions">
  <ul>

    <?php if ((QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) && !QubitTerm::isProtected($resource->id)) { ?>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'term', 'action' => 'edit'], ['class' => 'c-btn c-btn-submit']); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'delete') && !QubitTerm::isProtected($resource->id)) { ?>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'term', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource->taxonomy, 'createTerm')) { ?>
      <li><?php echo link_to(__('Add new'), ['module' => 'term', 'action' => 'add', 'parent' => url_for([$resource, 'module' => 'term']), 'taxonomy' => url_for([$resource->taxonomy, 'module' => 'taxonomy'])], ['class' => 'c-btn']); ?></li>
    <?php } ?>

  </ul>
</section>
