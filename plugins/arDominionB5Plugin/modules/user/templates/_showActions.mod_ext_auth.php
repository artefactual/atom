<?php if (QubitAcl::check($resource, ['create', 'update', 'delete', 'list'])) { ?>
  <ul class="actions mb-3 nav gap-2">

    <?php if (QubitAcl::check($resource, 'update')) { ?>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'user', 'action' => str_replace('index', 'edit', $sf_context->getActionName())], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <?php } ?>

    <?php if ($sf_user->user != $resource && 0 == count($resource->notes) && QubitAcl::check($resource, 'delete')) { ?>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'user', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
    <?php } ?>

    <?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'user', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'list')) { ?>
      <li><?php echo link_to(__('Return to user list'), ['module' => 'user', 'action' => 'list'], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <?php } ?>

  </ul>
<?php } ?>
