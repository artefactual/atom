<?php if (QubitAcl::check($resource, ['create', 'update', 'delete', 'translate'])) { ?>
  
  <ul class="actions mb-3 nav gap-2">

    <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'delete')) { ?>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'informationobject', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'create')) { ?>
      <li><?php echo link_to(__('Add new'), ['module' => 'informationobject', 'action' => 'add', 'parent' => $resource->slug], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <li><?php echo link_to(__('Duplicate'), ['module' => 'informationobject', 'action' => 'copy', 'source' => $resource->id], ['class' => 'btn atom-btn-outline-light']); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'update') || sfContext::getInstance()->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) { ?>

      <li><?php echo link_to(__('Move'), [$resource, 'module' => 'default', 'action' => 'move'], ['class' => 'btn atom-btn-outline-light']); ?></li>

      <li>
        <div class="dropup">
          <button type="button" class="btn atom-btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo __('More'); ?>
          </button>
          <ul class="dropdown-menu mb-2">

            <li><?php echo link_to(__('Rename'), [$resource, 'module' => 'informationobject', 'action' => 'rename'], ['class' => 'dropdown-item']); ?></li>

            <?php if (QubitAcl::check($resource, 'publish')) { ?>
              <li><?php echo link_to(
                  __('Update publication status'),
                  [
                      $resource,
                      'module' => 'informationobject',
                      'action' => 'updatePublicationStatus',
                  ],
                  [
                      'id' => 'update-publication-status',
                      'data-cy' => 'update-publication-status',
                      'class' => 'dropdown-item',
                  ]
                ); ?></li>
            <?php } ?>

            <li><hr class="dropdown-divider"></li>

            <li><?php echo link_to(__('Link physical storage'), [$resource, 'module' => 'object', 'action' => 'editPhysicalObjects'], ['class' => 'dropdown-item']); ?></li>

            <li><hr class="dropdown-divider"></li>

            <?php if (0 < count($resource->digitalObjectsRelatedByobjectId) && QubitDigitalObject::isUploadAllowed()) { ?>
              <li><?php echo link_to(__('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource->digitalObjectsRelatedByobjectId[0], 'module' => 'digitalobject', 'action' => 'edit'], ['class' => 'dropdown-item']); ?></li>
            <?php } elseif (QubitDigitalObject::isUploadAllowed()) { ?>
              <li><?php echo link_to(__('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource, 'module' => 'object', 'action' => 'addDigitalObject'], ['class' => 'dropdown-item']); ?></li>
            <?php } ?>

            <?php if ((null === $resource->repository || 0 != $resource->repository->uploadLimit) && QubitDigitalObject::isUploadAllowed()) { ?>
              <li><?php echo link_to(__('Import digital objects'), [$resource, 'module' => 'informationobject', 'action' => 'multiFileUpload'], ['class' => 'dropdown-item']); ?></li>
            <?php } ?>

            <li><hr class="dropdown-divider"></li>

            <li><?php echo link_to(__('Create new rights'), [$resource, 'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'edit'], ['class' => 'dropdown-item']); ?></li>
            <?php if ($resource->hasChildren()) { ?>
              <li><?php echo link_to(__('Manage rights inheritance'), [$resource, 'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'manage'], ['class' => 'dropdown-item']); ?></li>
            <?php } ?>

            <?php if (sfConfig::get('app_audit_log_enabled', false)) { ?>
              <li><hr class="dropdown-divider"></li>

              <li><?php echo link_to(__('View modification history'), [$resource, 'module' => 'informationobject', 'action' => 'modifications'], ['class' => 'dropdown-item']); ?></li>
            <?php } ?>
          </ul>
        </div>
      </li>

    <?php } ?>

  </ul>

<?php } ?>
