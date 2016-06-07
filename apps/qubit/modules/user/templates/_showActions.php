<section class="actions">

  <ul>

    <?php if (QubitAcl::check($resource, 'update')): ?>
      <li><?php echo link_to (__('Edit'), array($resource, 'module' => 'user', 'action' => str_replace('index', 'edit', $sf_context->getActionName())), array('class' => 'c-btn')) ?></li>
    <?php endif; ?>

    <?php if ($sf_user->user != $resource && 0 == count($resource->notes) && QubitAcl::check($resource, 'delete')): ?>
      <li><?php echo link_to (__('Delete'), array($resource, 'module' => 'user', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
    <?php endif; ?>

    <?php if (QubitAcl::check($resource, 'create')): ?>
      <li><?php echo link_to (__('Add new'), array('module' => 'user', 'action' => 'add'), array('class' => 'c-btn')) ?></li>
    <?php endif; ?>
    
    <?php if (QubitAcl::check($resource, 'list')): ?>
      <li><?php echo link_to (__('Return to user list'), array('module' => 'user', 'action' => 'list'), array('class' => 'c-btn')) ?></li>
    <?php endif; ?>

  </ul>

</section>
