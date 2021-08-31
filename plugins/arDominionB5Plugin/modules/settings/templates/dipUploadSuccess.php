<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('DIP upload settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <form action="<?php echo url_for('settings/dipUpload'); ?>" method="post">

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="dip-upload-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#dip-upload-collapse" aria-expanded="true" aria-controls="dip-upload-collapse">
            <?php echo __('DIP Upload settings'); ?>
          </button>
        </h2>
        <div id="dip-upload-collapse" class="accordion-collapse collapse show" aria-labelledby="dip-upload-heading">
          <div class="accordion-body">
            <?php echo render_field($form->stripExtensions
                ->label(__('Strip file extensions from information object names'))); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
