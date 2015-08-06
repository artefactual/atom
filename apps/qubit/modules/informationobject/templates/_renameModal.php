<?php $sf_response->addJavaScript('renameModal'); ?>

<div class="modal hide" id="renameModal">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3><?php echo __('Rename') ?></h3>
  </div>

  <form id="renameModalForm" action="<?php echo url_for(array('module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug)) ?>" method="POST">

  <div class="modal-body">
    <div>
      <div style="float:right"><input id="renameModalEnableTitle" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
      <label><?php echo __('Description title') ?></label>
      <input id="renameModalTitle" name="title" type="text" value="<?php echo $resource->title ?>" />
      <p><?php echo __('Original title') ?>: <em><?php echo $resource->title ?></em></p>
    </div>

    <div>
      <div style="float:right"><input id="renameModalEnableSlug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
      <label><?php echo __('Slug') ?></label>
      <input id="renameModalSlug" name="slug" type="text" value="<?php echo $resource->slug ?>" />
      <p><?php echo __('Original slug') ?>: <em><?php echo $resource->slug ?></em></p>
    </div>

    <?php if (count($resource->digitalObjects) > 0): ?>
    <div>
      <div style="float:right"><input id="renameModalEnableFilename" type="checkbox" /> <?php echo __('Update filename') ?></div>
      <label><?php echo __('File name') ?></label>
      <input id="renameModalFilename" name="filename" type="text" value="<?php echo $resource->digitalObjects[0]->name ?>" />
      <p><?php echo __('Original filename') ?>: <em><?php echo $resource->digitalObjects[0]->name ?></em></p>
    </div>
    <?php endif; ?>

  </div>

  <div class="modal-footer">
    <section class="actions">
      <ul>
        <li><a href="#" id="renameModalSubmit" class="c-btn c-btn-submit"><?php echo __('Update') ?></a></li>
        <li><a href="#" id="renameModalCancel" class="c-btn c-btn-delete" data-dismiss="modal"><?php echo __('Cancel') ?></a></li>
      </ul>
    </section>
  </div>

  </form>
</div>
