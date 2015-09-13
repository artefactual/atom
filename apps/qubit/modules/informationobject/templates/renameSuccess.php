<?php decorate_with('layout_2col.php') ?>

<?php $sf_response->addJavaScript('renameModal'); ?>
<?php $sf_response->addJavaScript('description'); ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Rename') ?>: <?php echo $resource->title ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

<div id="content" class="yui-panel">

  <div class="fieldset-wrapper">

    <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug)), array('id' => 'rename-form')) ?>

      <div class="alert"><?php echo __('Use this interface to update the description title, slug (permalink), and/or digital object filename.') ?></div>

      <div class="rename-form-field-toggle"><input id="rename_enable_title" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
    <br />
 
      <?php echo render_field($form->title
      ->label(__('Title'))
      ->help(__('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.')), $resource, array('class' => 'resizable')) ?>

      <div class="rename-form-field-toggle"><input id="rename_enable_slug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
      <br />

      <?php echo render_field($form->slug
        ->label(__('Slug'))
        ->help(__('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.')), $resource, array('class' => 'resizable')) ?>

      <?php if (count($resource->digitalObjects) > 0): ?>

        <div class="rename-form-field-toggle"><input id="rename_enable_filename" type="checkbox" /> <?php echo __('Update filename') ?></div>
        <br />

        <?php echo render_field($form->filename
          ->label(__('Filename'))
          ->help(__('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.')), $resource, array('class' => 'resizable')) ?>

      <?php endif; ?>

      <section class="actions">
        <ul>
          <li><a href="#" id="rename-form-submit" class="c-btn c-btn-submit"><?php echo __('Update') ?></a></li>
          <li><?php echo link_to(__('Cancel'), array('module' => 'informationobject', 'action' => 'browse'), array('class' => 'c-btn c-btn-delete')) ?></li>
        </ul>
      </section>

    </form>
  </div>
</div>

<?php end_slot() ?>
