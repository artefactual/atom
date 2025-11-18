<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Site information'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'siteInformation'])); ?>
    
    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="site-information-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#site-information-collapse" aria-expanded="true" aria-controls="site-information-collapse">
            <?php echo __('Site information settings'); ?>
          </button>
        </h2>
        <div id="site-information-collapse" class="accordion-collapse collapse show" aria-labelledby="site-information-heading">
          <div class="accordion-body">
            <?php echo render_field(
                $form->siteTitle
                    ->label(__('Site title'))
                    ->help(__('The name of the website for display in the header')),
                $settings['siteTitle']); ?>

            <?php echo render_field(
                $form->siteDescription
                    ->label(__('Site description'))
                    ->help(__('A brief site description or &quot;tagline&quot; for the header')),
                $settings['siteDescription']); ?>

            <?php echo render_field(
                $form->siteBaseUrl
                    ->label(__('Site base URL (used in MODS and EAD exports)'))
                    ->help(__('Used to create absolute URLs, pointing to resources, in XML exports')),
                $settings['siteBaseUrl']); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
