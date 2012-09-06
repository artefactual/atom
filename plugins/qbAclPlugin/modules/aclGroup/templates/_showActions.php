<?php if (QubitAcl::check($group, array('create', 'update', 'delete'))): ?>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (QubitAcl::check($group, 'update')): ?>
        <li><?php echo link_to (__('Edit'), array($group, 'module' => 'aclGroup', 'action' => str_replace('index', 'edit', $sf_context->getActionName()))) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($group, 'delete')): ?>
        <li><?php echo link_to (__('Delete'), array($group, 'module' => 'aclGroup', 'action' => 'delete'), array('class' => 'delete')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($group, 'create')): ?>
        <li><?php echo link_to (__('Add new'), array('module' => 'aclGroup', 'action' => 'add')) ?></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
<?php endif; ?>
