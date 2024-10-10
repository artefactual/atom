<section class="actions">

  <ul>

    <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'actor', 'action' => 'edit'], ['class' => 'c-btn c-btn-submit', 'title' => __('Edit')]); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'delete')) { ?>
      <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'actor', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete', 'title' => __('Delete')]); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'create')) { ?>
      <li><?php echo link_to(__('Add new'), ['module' => 'actor', 'action' => 'add'], ['class' => 'c-btn', 'title' => __('Add new')]); ?></li>
    <?php } ?>

    <?php if (QubitAcl::check($resource, 'update') || sfContext::getInstance()->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) { ?>

      <li><?php echo link_to(__('Rename'), [$resource, 'module' => 'actor', 'action' => 'rename'], ['class' => 'c-btn', 'title' => __('Rename')]); ?></li>

      <li class="divider"></li>

      <li>
        <div class="btn-group dropup">
          <a class="c-btn dropdown-toggle" data-toggle="dropdown" href="#">
            <?php echo __('More'); ?>
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">

            <?php if (0 < count($resource->digitalObjectsRelatedByobjectId) && QubitDigitalObject::isUploadAllowed()) { ?>
              <li><?php echo link_to(__('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource->digitalObjectsRelatedByobjectId[0], 'module' => 'digitalobject', 'action' => 'edit']); ?></li>
            <?php } elseif (QubitDigitalObject::isUploadAllowed()) { ?>
              <li><?php echo link_to(__('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource, 'module' => 'object', 'action' => 'addDigitalObject']); ?></li>
            <?php } ?>
          </ul>
        </div>
      </li>
    <?php } ?>

  </ul>

</section>
