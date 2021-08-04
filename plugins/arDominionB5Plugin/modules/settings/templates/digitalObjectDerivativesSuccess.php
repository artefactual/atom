<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('%1% derivatives', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'digitalObjectDerivatives'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="derivatives-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#derivatives-collapse" aria-expanded="true" aria-controls="derivatives-collapse">
            <?php echo __('%1% derivatives settings', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?>
          </button>
        </h2>
        <div id="derivatives-collapse" class="accordion-collapse collapse show" aria-labelledby="derivatives-heading">
          <div class="accordion-body">
            <?php if ($pdfinfoAvailable) { ?>
              <?php echo $form->digital_object_derivatives_pdf_page_number
                ->label(__('PDF page number for image derivative'))
                ->help(__('If the page number does not exist, the derivative will be generated from the previous closest one.'))
                ->renderRow(); ?>
            <?php } else { ?>
             <div class="messages error alert alert-danger" role="alert">
                <?php echo __('The pdfinfo tool is required to use this functionality. Please contact your system administrator.'); ?>
              </div>
            <?php } ?><br />

            <?php echo $form->reference_image_maxwidth
                ->label(__('Maximum length on longest edge (pixels)'))
                ->help(__('The maximum number of pixels on the longest edge for derived reference images.'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
    </div>

    <?php if ($pdfinfoAvailable) { ?>
      <section class="actions">
        <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
      </section>
    <?php } ?>

  </form>

<?php end_slot(); ?>
