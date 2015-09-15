<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo $resource->title ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug)), array('id' => 'rename-form')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Rename') ?></legend>

        <p><?php echo __('Use this interface to update the description title, slug (permalink), and/or digital object filename.') ?></p>

        <div class="rename-form-field-toggle"><input id="rename_enable_title" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
        <br />
 
        <?php echo render_field($form->title
          ->label(__('Title'))
          ->help(__('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.'))) ?>

        <div class="rename-form-field-toggle"><input id="rename_enable_slug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
        <br />

        <?php echo render_field($form->slug
          ->label(__('Slug'))
          ->help(__('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.')), $resource) ?>

        <?php if (count($resource->digitalObjects) > 0): ?>

          <div class="rename-form-field-toggle"><input id="rename_enable_filename" type="checkbox" /> <?php echo __('Update filename') ?></div>
          <br />

          <?php echo render_field($form->filename
          ->label(__('Filename'))
          ->help(__('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.')), $resource) ?>

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
