<section class="actions">
  <ul>

      <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('class' => 'c-btn c-btn-submit')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'informationobject', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'informationobject', 'action' => 'add', 'parent' => url_for(array($resource, 'module' => 'informationobject'))), array('class' => 'c-btn')) ?></li>
        <li><?php echo link_to(__('Duplicate'), array('module' => 'informationobject', 'action' => 'copy', 'source' => $resource->id), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'update')): ?>

        <li><?php echo link_to(__('Move'), array($resource, 'module' => 'default', 'action' => 'move'), array('class' => 'c-btn')) ?></li>

        <li class="divider"></li>

        <li>
          <div class="btn-group dropup">
            <a class="c-btn dropdown-toggle" data-toggle="dropdown" href="#">
              <?php echo __('More') ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">

              <li><?php echo link_to(__('Rename'), array($resource, 'module' => 'informationobject', 'action' => 'rename')) ?></li>

              <?php if (QubitAcl::check($resource, 'publish')): ?>
                <li><?php echo link_to(__('Update publication status'), array($resource, 'module' => 'informationobject', 'action' => 'updatePublicationStatus')) ?></li>
              <?php endif; ?>

              <li class="divider"></li>

              <li><?php echo link_to(__('Link physical storage'), array($resource, 'module' => 'informationobject', 'action' => 'editPhysicalObjects')) ?></li>

              <li class="divider"></li>

              <?php if (0 < count($resource->digitalObjects) && QubitDigitalObject::isUploadAllowed()): ?>
                <li><?php echo link_to(__('Edit %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))), array($resource->digitalObjects[0], 'module' => 'digitalobject', 'action' => 'edit')) ?></li>
              <?php elseif (QubitDigitalObject::isUploadAllowed()): ?>
                <li><?php echo link_to(__('Link %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))), array($resource, 'module' => 'informationobject', 'action' => 'addDigitalObject')) ?></li>
              <?php endif; // has digital object ?>

              <?php if ((null === $resource->repository || 0 != $resource->repository->uploadLimit) && QubitDigitalObject::isUploadAllowed()): ?>
                <li><?php echo link_to(__('Import digital objects'), array($resource, 'module' => 'informationobject', 'action' => 'multiFileUpload')) ?></li>
              <?php endif; // upload quota is non-zero ?>

              <li class="divider"></li>

              <li><?php echo link_to(__('Create new rights'), array($resource,  'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'edit')) ?></li>
              <?php if ($resource->hasChildren()): ?>
                <li><?php echo link_to(__('Manage rights inheritance'), array($resource,  'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'manage')) ?></li>
              <?php endif; ?>
            </ul>
          </div>
        </li>

      <?php endif; // user has update permission ?>

  </ul>

</section>
