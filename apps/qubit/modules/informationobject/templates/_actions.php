<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'informationobject', 'action' => 'edit')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'informationobject', 'action' => 'delete'), array('class' => 'delete')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'informationobject', 'action' => 'add', 'parent' => url_for(array($resource, 'module' => 'informationobject')))) ?></li>
        <li><?php echo link_to(__('Duplicate'), array('module' => 'informationobject', 'action' => 'copy', 'source' => $resource->id)) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'update')): ?>

        <li><?php echo link_to(__('Move'), array($resource, 'module' => 'default', 'action' => 'move')) ?></li>

          <?php if (0 < count($resource->digitalObjects) && QubitDigitalObject::isUploadAllowed()): ?>

            <li><?php echo link_to(__('Edit digital object'), array($resource->digitalObjects[0], 'module' => 'digitalobject', 'action' => 'edit')) ?></li>

          <?php elseif (QubitDigitalObject::isUploadAllowed()): ?>

            <li><?php echo link_to(__('Link digital object'), array($resource, 'module' => 'informationobject', 'action' => 'addDigitalObject')) ?></li>

          <?php endif; // has digital object ?>

        <?php if ((null === $resource->repository || 0 != $resource->repository->uploadLimit) && QubitDigitalObject::isUploadAllowed()): ?>

          <li><?php echo link_to(__('Import digital objects'), array($resource, 'module' => 'informationobject', 'action' => 'multiFileUpload')) ?></li>

        <?php endif; // upload quota is non-zero ?>

        <li><?php echo link_to(__('Link physical storage'), array($resource, 'module' => 'informationobject', 'action' => 'editPhysicalObjects')) ?></li>

      <?php endif; // user has update permission ?>

    </ul>
  </div>

</div>
