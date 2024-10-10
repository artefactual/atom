<?php if (QubitAcl::check($resource, ['update', 'translate', 'delete', 'create'])) { ?>

<ul class="actions mb-3 nav gap-2">

  <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
    <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'actor', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
  <?php } ?>

  <?php if (QubitAcl::check($resource, 'delete')) { ?>
    <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'actor', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
  <?php } ?>

  <?php if (QubitAcl::check($resource, 'create')) { ?>
    <li><?php echo link_to(__('Add new'), ['module' => 'actor', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
  <?php } ?>

  <?php if (QubitAcl::check($resource, 'update') || sfContext::getInstance()->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) { ?>
    
    <li><?php echo link_to(__('Rename'), [$resource, 'module' => 'actor', 'action' => 'rename'], ['class' => 'btn atom-btn-outline-light']); ?></li>

    <li>
      <div class="dropup">
        <button type="button" class="btn atom-btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <?php echo __('More'); ?>
        </button>

        <ul class="dropdown-menu mb-2">
          <?php if (0 < count($resource->digitalObjectsRelatedByobjectId) && QubitDigitalObject::isUploadAllowed()) { ?>
            <li><?php echo link_to(__('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource->digitalObjectsRelatedByobjectId[0], 'module' => 'digitalobject', 'action' => 'edit'], ['class' => 'dropdown-item']); ?></li>
          <?php } elseif (QubitDigitalObject::isUploadAllowed()) { ?>
            <li><?php echo link_to(__('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource, 'module' => 'object', 'action' => 'addDigitalObject'], ['class' => 'dropdown-item']); ?></li>
          <?php } ?>
        </ul>
      </div>
    </li>
  <?php } ?>

</ul>

<?php } ?>