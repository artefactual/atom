<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Storage Service settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(
    ['module' => 'arStorageServiceSettings', 'action' => 'settings']
  )); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="credentials-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#credentials-collapse" aria-expanded="true" aria-controls="credentials-collapse">
            <?php echo __('Storage Service credentials'); ?>
          </button>
        </h2>
        <div id="credentials-collapse" class="accordion-collapse collapse show" aria-labelledby="credentials-heading">
          <div class="accordion-body">
            <?php echo render_field($form->storage_service_api_url->label(__(
                'Storage Service API endpoint, e.g. "http://localhost:62081/api/v2"'
            ))); ?>

            <?php echo render_field($form->storage_service_username->label(__(
                'Storage Service username, e.g. "atom"'
            ))); ?>

            <?php echo render_field($form->storage_service_api_key->label(__(
                'Storage Service API key, e.g. "2ef7bde608ce5404e97d5f042f95f89f1c232871"'
            ))); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="aip-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#aip-collapse" aria-expanded="false" aria-controls="aip-collapse">
            <?php echo __('Storage Service credentials'); ?>
          </button>
        </h2>
        <div id="aip-collapse" class="accordion-collapse collapse" aria-labelledby="aip-heading">
          <div class="accordion-body">
            <?php echo render_field($form->download_aip_enabled->label(__('Enable AIP download'))->help(__(
                'Allow authorized users to download a linked AIP or AIP file from'
                .' the configured Archivematica Storage Service'
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
