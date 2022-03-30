<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Default template'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $defaultTemplateForm->renderGlobalErrors(); ?>

  <?php echo $defaultTemplateForm->renderFormTag(url_for(['module' => 'settings', 'action' => 'template'])); ?>

    <?php echo $defaultTemplateForm->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="default-template-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#default-template-collapse" aria-expanded="true" aria-controls="default-template-collapse">
            <?php echo __('Default template settings'); ?>
          </button>
        </h2>
        <div id="default-template-collapse" class="accordion-collapse collapse show" aria-labelledby="default-template-heading">
          <div class="accordion-body">
            <?php echo render_field($defaultTemplateForm->informationobject); ?>

            <?php echo render_field($defaultTemplateForm->actor); ?>

            <?php echo render_field($defaultTemplateForm->repository); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
