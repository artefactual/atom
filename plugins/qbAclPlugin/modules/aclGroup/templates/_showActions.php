<?php if (QubitAcl::check($group, array('create', 'update', 'delete'))): ?>

  <section class="actions">

    <ul>

        <?php if (QubitAcl::check($group, 'update')): ?>
          <li><?php echo link_to (__('Edit'), array($group, 'module' => 'aclGroup', 'action' => str_replace('index', 'edit', $sf_context->getActionName())), array('class' => 'c-btn')) ?></li>
        <?php endif; ?>

        <?php if (QubitAcl::check($group, 'delete')): ?>
          <li><?php echo link_to (__('Delete'), array($group, 'module' => 'aclGroup', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
        <?php endif; ?>

        <?php if (QubitAcl::check($group, 'create')): ?>
          <li><?php echo link_to (__('Add new'), array('module' => 'aclGroup', 'action' => 'add'), array('class' => 'c-btn')) ?></li>
        <?php endif; ?>
        
        <?php if (QubitAcl::check($group, 'list')): ?>
          <li><?php echo link_to (__('Return to group list'), array('module' => 'aclGroup', 'action' => 'list'), array('class' => 'c-btn')) ?></li>
        <?php endif; ?>

    </ul>

  </section>

<?php endif; ?>
