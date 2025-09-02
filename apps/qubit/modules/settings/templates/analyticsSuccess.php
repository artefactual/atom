<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Web analytics'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div class="alert alert-info">
    <?php echo __('Please clear the cache and restart PHP-FPM after adding tracking ID.'); ?>
  </div>

  <?php if (!empty(sfConfig::get('app_google_analytics_api_key')) && '' == QubitSetting::getByName('google_analytics')) { ?>
    <div class="alert alert-info">
      <?php echo __('Google analytics is currently set in the app.yml.'); ?>
    </div>
  <?php } ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'analytics'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="analytics-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#analytics-collapse" aria-expanded="true" aria-controls="analytics-collapse">
            <?php echo __('Web analytics'); ?>
          </button>
        </h2>
        <div id="analytics-collapse" class="accordion-collapse collapse show" aria-labelledby="analytics-heading">
          <div class="accordion-body">
            <?php echo render_field($form->google_analytics); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
