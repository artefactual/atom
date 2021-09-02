<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Global settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $globalForm->renderGlobalErrors(); ?>

  <?php echo $globalForm->renderFormTag(url_for(['module' => 'settings', 'action' => 'global'])); ?>

    <?php echo $globalForm->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="global-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#global-collapse" aria-expanded="true" aria-controls="global-collapse">
            <?php echo __('Global settings'); ?>
          </button>
        </h2>
        <div id="global-collapse" class="accordion-collapse collapse show" aria-labelledby="global-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->version); ?>

            <?php echo render_field($globalForm->check_for_updates); ?>

            <?php echo render_field($globalForm->hits_per_page); ?>

            <?php echo render_field($globalForm->escape_queries); ?>

            <?php echo render_field($globalForm->sort_browser_user); ?>

            <?php echo render_field($globalForm->sort_browser_anonymous); ?>

            <?php echo render_field($globalForm->default_repository_browse_view); ?>

            <?php echo render_field($globalForm->default_archival_description_browse_view); ?>

            <?php echo render_field($globalForm->multi_repository); ?>

            <?php echo render_field($globalForm->audit_log_enabled); ?>

            <?php echo render_field($globalForm->slug_basis_informationobject); ?>

            <?php echo render_field($globalForm->permissive_slug_creation); ?>

            <?php echo render_field($globalForm->show_tooltips); ?>

            <?php echo render_field($globalForm->defaultPubStatus); ?>

            <?php echo render_field($globalForm->draft_notification_enabled); ?>

            <?php echo render_field($globalForm->sword_deposit_dir); ?>

            <?php echo render_field($globalForm->google_maps_api_key); ?>

            <?php echo render_field($globalForm->generate_reports_as_pub_user); ?>

            <?php echo render_field($globalForm->enable_institutional_scoping); ?>

            <?php echo render_field($globalForm->cache_xml_on_save); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
