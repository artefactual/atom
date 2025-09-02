<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>

  <div class="alert alert-info">
    <?php echo __('Enter the ID of the saved clipboard you would like to load.'); ?>
    <?php echo __('In the "Action" selector, indicate whether you want to <strong>merge</strong> the saved clipboard with the entries on the current clipboard or <strong>replace</strong> (overwrite) the current clipboard with the saved one.'); ?>
  </div>

  <h1><?php echo __('Load clipboard'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'clipboard', 'action' => 'load']), ['id' => 'clipboard-load-form']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="load-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#load-collapse" aria-expanded="true" aria-controls="load-collapse">
            <?php echo __('Load options'); ?>
          </button>
        </h2>
        <div id="load-collapse" class="accordion-collapse collapse show" aria-labelledby="load-heading">
          <div class="accordion-body">
            <?php echo render_field($form->clipboardPassword->label(__('Clipboard ID'))); ?>
            <?php echo render_field($form->mode->label(__('Action'))); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><input class="btn atom-btn-outline-light" type="submit" name="load" value="<?php echo __('Load'); ?>"/></li>
      <li><input class="btn atom-btn-outline-light" type="submit" name="loadView" value="<?php echo __('Load and view'); ?>"/></li>
    </ul>

  </form>

<?php end_slot(); ?>
