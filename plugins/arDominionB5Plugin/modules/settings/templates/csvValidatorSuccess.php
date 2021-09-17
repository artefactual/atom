<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('CSV Validator'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'csvValidator'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="validator-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#validator-collapse" aria-expanded="true" aria-controls="validator-collapse">
            <?php echo __('CSV Validator settings'); ?>
          </button>
        </h2>
        <div id="validator-collapse" class="accordion-collapse collapse show" aria-labelledby="validator-heading">
          <div class="accordion-body">
            <?php echo render_field($form->csv_validator_default_import_behaviour->label(__('CSV Validator default behaviour when CSV Import is run'))); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
