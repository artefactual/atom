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

      <div style="float:right"><input id="rename_enable_title" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
      <?php echo render_field($renameForm->title, $resource) ?>
      <p><?php echo __('Original title') ?>: <em><?php echo $resource->title ?></em></p>

      <div style="float:right"><input id="rename_enable_slug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
      <?php echo render_field($renameForm->slug, $resource) ?>
      <p><?php echo __('Original slug') ?>: <em><?php echo $resource->slug ?></em></p>

      <?php if (count($resource->digitalObjects) > 0): ?>
        <div style="float:right"><input id="rename_enable_filename" type="checkbox" /> <?php echo __('Update filename') ?></div>
        <?php echo render_field($renameForm->filename, $resource) ?>
        <p><?php echo __('Original filename') ?>: <em><?php echo $resource->digitalObjects[0]->name ?></em></p>
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
