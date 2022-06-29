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

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="version-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#version-collapse" aria-expanded="true" aria-controls="version-collapse">
            <?php echo __('Version'); ?>
          </button>
        </h2>
        <div id="version-collapse" class="accordion-collapse collapse show" aria-labelledby="version-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->version); ?>

            <?php echo render_field($globalForm->check_for_updates); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="search-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#search-collapse" aria-expanded="false" aria-controls="search-collapse">
            <?php echo __('Search and browse'); ?>
          </button>
        </h2>
        <div id="search-collapse" class="accordion-collapse collapse" aria-labelledby="search-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->hits_per_page); ?>

            <?php echo render_field($globalForm->sort_browser_user); ?>

            <?php echo render_field($globalForm->sort_browser_anonymous); ?>

            <?php echo render_field($globalForm->default_archival_description_browse_view); ?>

            <?php echo render_field($globalForm->default_repository_browse_view); ?>

            <?php echo render_field($globalForm->escape_queries); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="presentation-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#presentation-collapse" aria-expanded="false" aria-controls="presentation-collapse">
            <?php echo __('Presentation'); ?>
          </button>
        </h2>
        <div id="presentation-collapse" class="accordion-collapse collapse" aria-labelledby="presentation-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->show_tooltips); ?>

            <?php echo render_field($globalForm->draft_notification_enabled); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="multirepo-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#multirepo-collapse" aria-expanded="false" aria-controls="multirepo-collapse">
            <?php echo __('Multi-repository'); ?>
          </button>
        </h2>
        <div id="multirepo-collapse" class="accordion-collapse collapse" aria-labelledby="multirepo-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->multi_repository); ?>

            <?php echo render_field($globalForm->enable_institutional_scoping); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="permalinks-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#permalinks-collapse" aria-expanded="false" aria-controls="permalinks-collapse">
            <?php echo __('Permalinks'); ?>
          </button>
        </h2>
        <div id="permalinks-collapse" class="accordion-collapse collapse" aria-labelledby="permalinks-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->slug_basis_informationobject); ?>

            <?php echo render_field($globalForm->permissive_slug_creation); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="system-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#system-collapse" aria-expanded="false" aria-controls="system-collapse">
            <?php echo __('System'); ?>
          </button>
        </h2>
        <div id="system-collapse" class="accordion-collapse collapse" aria-labelledby="system-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->audit_log_enabled); ?>

            <?php echo render_field($globalForm->generate_reports_as_pub_user); ?>

            <?php echo render_field($globalForm->cache_xml_on_save); ?>

            <?php echo render_field($globalForm->defaultPubStatus); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="integrations-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#integrations-collapse" aria-expanded="false" aria-controls="integrations-collapse">
            <?php echo __('Integrations'); ?>
          </button>
        </h2>
        <div id="integrations-collapse" class="accordion-collapse collapse" aria-labelledby="integrations-heading">
          <div class="accordion-body">
            <?php echo render_field($globalForm->google_maps_api_key); ?>

            <?php echo render_field($globalForm->sword_deposit_dir); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
