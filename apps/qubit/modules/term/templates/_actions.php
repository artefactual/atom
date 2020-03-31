<section class="actions">
  <ul>

    <?php if ((QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) && !QubitTerm::isProtected($resource->id)): ?>
      <li><?php echo link_to (__('Edit'), array($resource, 'module' => 'term', 'action' => 'edit'), array('class' => 'c-btn c-btn-submit')) ?></li>
    <?php endif; ?>

    <?php if (QubitAcl::check($resource, 'delete') && !QubitTerm::isProtected($resource->id)): ?>
      <li><?php echo link_to (__('Delete'), array($resource, 'module' => 'term', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
    <?php endif; ?>

    <?php if (QubitAcl::check($resource->taxonomy, 'createTerm')): ?>
      <li><?php echo link_to(__('Add new'), array('module' => 'term', 'action' => 'add', 'parent' => url_for(array($resource, 'module' => 'term')), 'taxonomy' => url_for(array($resource->taxonomy, 'module' => 'taxonomy'))), array('class' => 'c-btn')) ?></li>
    <?php endif; ?>

  </ul>
</section>
