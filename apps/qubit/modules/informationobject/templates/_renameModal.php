<?php $sf_response->addJavaScript('renameModal'); ?>
<?php $sf_response->addJavaScript('description'); ?>

<div class="modal hide" id="renameModal">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3><?php echo __('Rename') ?></h3>
  </div>

  <form id="renameModalForm" action="<?php echo url_for(array('module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug)) ?>" method="POST">

  <div class="modal-body">
    <div class="alert"><?php echo __('Use this interface to update the description title, slug (permalink), and/or digital object filename.') ?></div>

    <div>
      <div style="float:right"><input id="renameModalEnableTitle" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
      <label><?php echo __('Description title') ?></label>
      <input id="renameModalTitle" name="title" type="text" value="<?php echo $resource->title ?>" />
      <div class="description">
        <?php echo __('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.') ?>
      </div>
      <p><?php echo __('Original title') ?>: <em><?php echo $resource->title ?></em></p>
    </div>

    <div>
      <div style="float:right"><input id="renameModalEnableSlug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
      <label><?php echo __('Slug') ?></label>
      <input id="renameModalSlug" name="slug" type="text" value="<?php echo $resource->slug ?>" />
      <div class="description"><?php echo __('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.') ?></div>
      <p><?php echo __('Original slug') ?>: <em><?php echo $resource->slug ?></em></p>
    </div>

    <?php if (count($resource->digitalObjects) > 0): ?>
    <div>
      <div style="float:right"><input id="renameModalEnableFilename" type="checkbox" /> <?php echo __('Update filename') ?></div>
      <label><?php echo __('File name') ?></label>
      <input id="renameModalFilename" name="filename" type="text" value="<?php echo $resource->digitalObjects[0]->name ?>" />
      <div class="description"><?php echo __('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.') ?></div>
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
