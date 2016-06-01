<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo $resource->title ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <div class="modal hide" id="rename-slug-warning">
    <div class="modal-header">
      <a class="close" data-dismiss="modal">×</a>
      <h3><?php echo __('Slug in use') ?></h3>
    </div>

    <div class="modal-body">
      <?php echo __('A slug based on this title already exists so a number has been added to pad the slug.') ?>
    </div>

    <div class="modal-footer">
      <section class="actions">
        <ul>
          <li><a href="#" id="renameModalCancel" class="c-btn c-btn-submit" data-dismiss="modal"><?php echo __('Close') ?></a></li>
        </ul>
      </section>
    </div>
  </div>

  <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug)), array('id' => 'rename-form')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Rename') ?></legend>

        <p><?php echo __('Use this interface to update the description title, slug (permalink), and/or %1% filename.', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?></p>

        <div class="rename-form-field-toggle"><input id="rename_enable_title" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
        <br />
 
        <?php echo render_field($form->title
          ->label(__('Title'))
          ->help(__('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.'))) ?>

        <p><?php echo __('Original title') ?>: <em><?php echo $resource->title ?></em></p>

        <div class="rename-form-field-toggle"><input id="rename_enable_slug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
        <br />

        <?php echo render_field($form->slug
          ->label(__('Slug'))
          ->help(__('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.')), $resource) ?>

        <p><?php echo __('Original slug') ?>: <em><?php echo $resource->slug ?></em></p>

        <?php if (count($resource->digitalObjects) > 0): ?>

          <div class="rename-form-field-toggle"><input id="rename_enable_filename" type="checkbox" /> <?php echo __('Update filename') ?></div>
          <br />

          <?php echo render_field($form->filename
          ->label(__('Filename'))
          ->help(__('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.')), $resource) ?>

          <p><?php echo __('Original filename') ?>: <em><?php echo $resource->digitalObjects[0]->name ?></em></p>

        <?php endif; ?>

      </fieldset>
    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" id="rename-form-submit" type="submit" value="<?php echo __('Update') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
