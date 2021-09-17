<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Default page elements'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'pageElements'])); ?>
    
    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="default-page-elements-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#default-page-elements-collapse" aria-expanded="true" aria-controls="default-page-elements-collapse">
            <?php echo __('Default page elements settings'); ?>
          </button>
        </h2>
        <div id="default-page-elements-collapse" class="accordion-collapse collapse show" aria-labelledby="default-page-elements-heading">
          <div class="accordion-body">
            <p><?php echo __('Enable or disable the display of certain page elements. Unless they have been overridden by a specific theme, these settings will be used site wide.'); ?></p>

            <?php echo render_field($form->toggleLogo->label('Logo')); ?>

            <?php echo render_field($form->toggleTitle->label('Title')); ?>

            <?php echo render_field($form->toggleDescription
                ->label('Description')); ?>

            <?php echo render_field($form->toggleLanguageMenu
                ->label('Language menu')); ?>

            <?php echo render_field($form->toggleIoSlider
                ->label('Digital object carousel')); ?>

            <?php $help = $googleMapsApiKeySet
                ? null
                : __('This feature will not work until a Google Maps API key is specified on the %1%global%2% settings page.', ['%1%' => '<a href="'.url_for('settings/global').'">', '%2%' => '</a>']); ?>
            <?php echo render_field($form->toggleDigitalObjectMap
                    ->label('Digital object map')->help($help)); ?>

            <?php echo render_field($form->toggleCopyrightFilter
                ->label('Copyright status filter')); ?>

            <?php echo render_field($form->toggleMaterialFilter
                ->label('General material designation filter')); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
