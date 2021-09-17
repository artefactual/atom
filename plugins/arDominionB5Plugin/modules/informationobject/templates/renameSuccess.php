<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug]), ['id' => 'rename-form']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="rename-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#rename-collapse" aria-expanded="true" aria-controls="rename-collapse">
            <?php echo __('Rename'); ?>
          </button>
        </h2>
        <div id="rename-collapse" class="accordion-collapse collapse show" aria-labelledby="rename-heading">
          <div class="accordion-body">
            <p><?php echo __('Use this interface to update the description title, slug (permalink), and/or %1% filename.', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?></p>
            <hr />

            <div class="rename-form-field-toggle form-check mb-4">
              <input class="form-check-input" type="checkbox" id="rename_enable_title" checked>
              <label class="form-check-label" for="rename_enable_title">
                <?php echo __('Update title'); ?>
              </label>
            </div>
            <?php echo render_field($form->title
                ->label(__('Title'))
                ->help(__('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.')), $resource); ?>
            <p><?php echo __('Original title'); ?>: <em><?php echo $resource->title; ?></em></p>
            <hr />

            <div id="rename-slug-warning" class="alert alert-danger" role="alert" style="display: none;">
              <?php echo __('A slug based on this title already exists so a number has been added to pad the slug.'); ?>
            </div>
            <div class="rename-form-field-toggle form-check mb-4">
              <input class="form-check-input" type="checkbox" id="rename_enable_slug" checked>
              <label class="form-check-label" for="rename_enable_slug">
                <?php echo __('Update slug'); ?>
              </label>
            </div>
            <?php echo render_field($form->slug
                ->label(__('Slug'))
                ->help(__('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.')), $resource); ?>
            <p><?php echo __('Original slug'); ?>: <em><?php echo $resource->slug; ?></em></p>

            <?php if (count($resource->digitalObjectsRelatedByobjectId) > 0) { ?>
              <hr />
              <div class="rename-form-field-toggle form-check mb-4">
                <input class="form-check-input" type="checkbox" id="rename_enable_filename" checked>
                <label class="form-check-label" for="rename_enable_filename">
                  <?php echo __('Update filename'); ?>
                </label>
              </div>
              <?php echo render_field($form->filename
                  ->label(__('Filename'))
                  ->help(__('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.')), $resource); ?>
              <p><?php echo __('Original filename'); ?>: <em><?php echo $resource->digitalObjectsRelatedByobjectId[0]->name; ?></em></p>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" id="rename-form-submit" type="submit" value="<?php echo __('Update'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
