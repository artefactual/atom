<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Inventory'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'inventory'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="inventory-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#inventory-collapse" aria-expanded="true" aria-controls="inventory-collapse">
            <?php echo __('Inventory settings'); ?>
          </button>
        </h2>
        <div id="inventory-collapse" class="accordion-collapse collapse show" aria-labelledby="inventory-heading">
          <div class="accordion-body">
            <?php if (!empty($unknownValueDetected)) { ?>
              <div class="alert alert-danger" role="alert">
                <?php echo __('Unknown value detected.'); ?><br />
              </div>
            <?php } ?>

            <?php echo render_field(
                $form->levels
                    ->label(__('Levels of description'))
                    ->help(__('Select the levels of description to be included in the inventory list. If no levels are selected, the inventory list link will not be displayed. You can use the control (Mac ⌘) and/or shift keys to multi-select values from the Levels of description menu.')),
                null,
                ['class' => 'form-autocomplete'],
            ); ?>

            <br />
            <?php $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID); ?>
            <?php echo link_to(__('Review the current terms in the Levels of description taxonomy.'), [$taxonomy, 'module' => 'taxonomy']); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
