<section class="actions">
  <ul>

      <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'informationobject', 'action' => 'edit'], ['class' => 'c-btn c-btn-submit']); ?></li>
      <?php } ?>

      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'informationobject', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
      <?php } ?>

      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'informationobject', 'action' => 'add', 'parent' => $resource->slug], ['class' => 'c-btn']); ?></li>
        <li><?php echo link_to(__('Duplicate'), ['module' => 'informationobject', 'action' => 'copy', 'source' => $resource->id], ['class' => 'c-btn']); ?></li>
      <?php } ?>

      <?php if (QubitAcl::check($resource, 'update') || sfContext::getInstance()->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) { ?>

        <li><?php echo link_to(__('Move'), [$resource, 'module' => 'default', 'action' => 'move'], ['class' => 'c-btn']); ?></li>

        <li class="divider"></li>

        <li>
          <div class="btn-group dropup">
            <a class="c-btn dropdown-toggle" data-toggle="dropdown" href="#">
              <?php echo __('More'); ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">

              <li><?php echo link_to(__('Rename'), [$resource, 'module' => 'informationobject', 'action' => 'rename']); ?></li>

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
                  ]
                ); ?></li>
              <?php } ?>

              <li class="divider"></li>

              <li><?php echo link_to(__('Link physical storage'), [$resource, 'module' => 'object', 'action' => 'editPhysicalObjects']); ?></li>

              <li class="divider"></li>

              <?php if (0 < count($resource->digitalObjectsRelatedByobjectId) && QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource->digitalObjectsRelatedByobjectId[0], 'module' => 'digitalobject', 'action' => 'edit']); ?></li>
              <?php } elseif (QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource, 'module' => 'object', 'action' => 'addDigitalObject']); ?></li>
              <?php } ?>

              <?php if ((null === $resource->repository || 0 != $resource->repository->uploadLimit) && QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Import digital objects'), [$resource, 'module' => 'informationobject', 'action' => 'multiFileUpload']); ?></li>
              <?php } ?>

              <li class="divider"></li>

              <li><?php echo link_to(__('Create new rights'), [$resource, 'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'edit']); ?></li>
              <?php if ($resource->hasChildren()) { ?>
                <li><?php echo link_to(__('Manage rights inheritance'), [$resource, 'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'manage']); ?></li>
              <?php } ?>

              <?php if (sfConfig::get('app_audit_log_enabled', false)) { ?>
                <li class="divider"></li>

                <li><?php echo link_to(__('View modification history'), [$resource, 'module' => 'informationobject', 'action' => 'modifications']); ?></li>
              <?php } ?>
            </ul>
          </div>
        </li>

      <?php } ?>

  </ul>
</section>
