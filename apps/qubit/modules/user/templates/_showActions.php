<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (QubitAcl::check($resource, 'update')): ?>
        <li><?php echo link_to (__('Edit'), array($resource, 'module' => 'user', 'action' => str_replace('index', 'edit', $sf_context->getActionName()))) ?></li>
      <?php endif; ?>

      <?php if ($sf_user->user != $resource && 0 == count($resource->notes) && QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to (__('Delete'), array($resource, 'module' => 'user', 'action' => 'delete'), array('class' => 'delete')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to (__('Add new'), array('module' => 'user', 'action' => 'add')) ?></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
