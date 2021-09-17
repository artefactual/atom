<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Identifier-related'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div class="alert alert-info" role="alert">
    <p><?php echo __('Please clear the application cache and rebuild the search index if you are changing the reference code separator setting.'); ?></p>
    <pre>$ php symfony cc</pre>
    <pre>$ php symfony search:populate</pre>
  </div>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'identifier'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="identifier-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#identifier-collapse" aria-expanded="true" aria-controls="identifier-collapse">
            <?php echo __('Identifier settings'); ?>
          </button>
        </h2>
        <div id="identifier-collapse" class="accordion-collapse collapse show" aria-labelledby="identifier-heading">
          <div class="accordion-body">
            <?php echo render_field($form->accession_mask_enabled
                ->label(__('Accession mask enabled'))); ?>

            <?php echo render_field($form->accession_mask
                ->label(__('Accession mask'))); ?>

            <?php echo render_field($form->accession_counter
                ->label(__('Accession counter')), null, ['type' => 'number']); ?>

            <?php echo render_field($form->identifier_mask_enabled
                ->label(__('Identifier mask enabled'))); ?>

            <?php echo render_field($form->identifier_mask
                ->label(__('Identifier mask'))); ?>

          <?php echo render_field($form->identifier_counter
              ->label(__('Identifier counter')), null, ['type' => 'number']); ?>

            <?php echo render_field($form->separator_character
                ->label(__('Reference code separator'))); ?>

            <?php echo render_field($form->inherit_code_informationobject
                ->label(__('Inherit reference code (information object)'))); ?>

            <?php echo render_field($form->inherit_code_dc_xml
                ->label(__('Inherit reference code (DC XML)'))); ?>

            <?php echo render_field($form->prevent_duplicate_actor_identifiers
                ->label(__(
                  '%1% identifiers: prevent entry/import of duplicates',
                  ['%1%' => sfConfig::get('app_ui_label_actor')]
                ))); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
