<?php $sf_response->addJavaScript('renameModal'); ?>

<div class="modal hide" id="renameModal">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3><?php echo __('Rename') ?></h3>
  </div>

  <form>

  <div class="modal-body">
    <div>
      <div style="float:right"><input id="renameModalEnableTitle" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
      <label><?php echo __('Description title') ?></label>
      <input id="renameModalTitle" type="text" value="<?php echo esc_entities($resource->title) ?>" />
      <p>Original: <?php echo esc_entities($resource->title) ?></p>
    </div>

    <div>
      <div style="float:right"><input id="renameModalEnableSlug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
      <label><?php echo __('Slug') ?></label>
      <input id="renameModalSlug" type="text" value="<?php echo esc_entities($resource->slug) ?>" />
      <p>Original: <?php echo esc_entities($resource->slug) ?></p>
    </div>

<?php if (count($resource->digitalObjects) > 0): ?>
    <div>
      <div style="float:right"><input id="renameModalEnableFilename" type="checkbox" /> <?php echo __('Update filename') ?></div>
      <label><?php echo __('File name') ?></label>
      <input id="renameModalFilename" type="text" value="<?php echo esc_entities($resource->digitalObjects[0]->name) ?>" />
      <p>Original: <?php echo esc_entities($resource->digitalObjects[0]->name) ?></p>
    </div>
<?php endif; ?>

  </div>

  <div class="modal-footer">
    <section class="actions">
      <ul>
        <li><a id="renameModalSubmit" class="c-btn c-btn-submit"><?php echo __('Update') ?></a></li>
        <li><a id="renameModalCancel" class="c-btn c-btn-delete" data-dismiss="modal"><?php echo __('Cancel') ?></a></li>
      </ul>
    </section>
  </div>

  </form>
</div>
