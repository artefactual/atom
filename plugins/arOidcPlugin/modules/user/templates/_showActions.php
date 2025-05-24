<section class="actions">

  <ul>

    <?php if (QubitAcl::check($resource, 'update')) { ?>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'user', 'action' => str_replace('index', 'edit', $sf_context->getActionName())], ['class' => 'c-btn']); ?></li>
    <?php } ?>

    <?php if ($sf_user->user != $resource && 0 == count($resource->notes) && QubitAcl::check($resource, 'delete')) { ?>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'user', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
    <?php } ?>
    
    <?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'user', 'action' => 'add'], ['class' => 'c-btn']); ?></li>
      <?php } ?>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'list')) { ?>
      <li><?php echo link_to(__('Return to user list'), ['module' => 'user', 'action' => 'list'], ['class' => 'c-btn']); ?></li>
    <?php } ?>

  </ul>

</section>
