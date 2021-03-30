<?php if (QubitAcl::check($group, ['create', 'update', 'delete'])) { ?>

  <section class="actions">

    <ul>

        <?php if (QubitAcl::check($group, 'update')) { ?>
          <li><?php echo link_to(__('Edit'), [$group, 'module' => 'aclGroup', 'action' => str_replace('index', 'edit', $sf_context->getActionName())], ['class' => 'c-btn']); ?></li>
        <?php } ?>

        <?php if (QubitAcl::check($group, 'delete')) { ?>
          <li><?php echo link_to(__('Delete'), [$group, 'module' => 'aclGroup', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
        <?php } ?>

        <?php if (QubitAcl::check($group, 'create')) { ?>
          <li><?php echo link_to(__('Add new'), ['module' => 'aclGroup', 'action' => 'add'], ['class' => 'c-btn']); ?></li>
        <?php } ?>
        
        <?php if (QubitAcl::check($group, 'list')) { ?>
          <li><?php echo link_to(__('Return to group list'), ['module' => 'aclGroup', 'action' => 'list'], ['class' => 'c-btn']); ?></li>
        <?php } ?>

    </ul>

  </section>

<?php } ?>
