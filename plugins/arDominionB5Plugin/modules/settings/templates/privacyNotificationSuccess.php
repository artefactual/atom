<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Privacy Notification'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'privacyNotification'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="privacy-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#privacy-collapse" aria-expanded="true" aria-controls="privacy-collapse">
            <?php echo __('Privacy Notification Settings'); ?>
          </button>
        </h2>
        <div id="privacy-collapse" class="accordion-collapse collapse show" aria-labelledby="privacy-heading">
          <div class="accordion-body">
            <?php echo render_field($form->privacy_notification_enabled
                ->label(__('Display Privacy Notification on first visit to site'))); ?>

            <?php echo render_field(
                $form->privacy_notification
                    ->label(__('Privacy Notification Message')),
                $settings['privacy_notification']); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
