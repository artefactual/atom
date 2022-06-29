<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Visible elements'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'visibleElements']), ['method' => 'post']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="global-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#global-collapse" aria-expanded="false" aria-controls="global-collapse">
            <?php echo __('Global'); ?>
          </button>
        </h2>
        <div id="global-collapse" class="accordion-collapse collapse" aria-labelledby="global-heading">
          <div class="accordion-body">
            <?php foreach ([
                'global_login_button' => __('Login button'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="isad-headings-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#isad-headings-collapse" aria-expanded="false" aria-controls="isad-headings-collapse">
            <?php echo __('ISAD template - area headings'); ?>
          </button>
        </h2>
        <div id="isad-headings-collapse" class="accordion-collapse collapse" aria-labelledby="isad-headings-heading">
          <div class="accordion-body">
            <?php foreach ([
                'isad_identity_area' => __('Identity area'),
                'isad_context_area' => __('Context area'),
                'isad_content_and_structure_area' => __('Content and structure area'),
                'isad_conditions_of_access_use_area' => __('Conditions of access and use area'),
                'isad_allied_materials_area' => __('Allied materials area'),
                'isad_notes_area' => __('Notes area'),
                'isad_access_points_area' => __('Access points'),
                'isad_description_control_area' => __('Description control area'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="isad-elements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#isad-elements-collapse" aria-expanded="false" aria-controls="isad-elements-collapse">
            <?php echo __('ISAD template - elements'); ?>
          </button>
        </h2>
        <div id="isad-elements-collapse" class="accordion-collapse collapse" aria-labelledby="isad-elements-heading">
          <div class="accordion-body">
            <?php foreach ([
                'isad_archival_history' => __('Archival history'),
                'isad_immediate_source' => __('Immediate source of acquisition or transfer'),
                'isad_appraisal_destruction' => __('Appraisal, destruction and scheduling information'),
                'isad_notes' => __('Notes'),
                'isad_physical_condition' => __('Physical characteristics and technical requirements'),
                'isad_control_description_identifier' => __('Description identifier'),
                'isad_control_institution_identifier' => __('Institution identifier'),
                'isad_control_rules_conventions' => __('Rules or conventions'),
                'isad_control_status' => __('Status'),
                'isad_control_level_of_detail' => __('Level of detail'),
                'isad_control_dates' => __('Dates of creation, revision and deletion'),
                'isad_control_languages' => __('Language(s)'),
                'isad_control_scripts' => __('Script(s)'),
                'isad_control_sources' => __('Sources'),
                'isad_control_archivists_notes' => __('Archivist\'s notes'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="rad-headings-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rad-headings-collapse" aria-expanded="false" aria-controls="rad-headings-collapse">
            <?php echo __('RAD template - area headings'); ?>
          </button>
        </h2>
        <div id="rad-headings-collapse" class="accordion-collapse collapse" aria-labelledby="rad-headings-heading">
          <div class="accordion-body">
            <?php foreach ([
                'rad_title_responsibility_area' => __('Title and statement of responsibility area'),
                'rad_edition_area' => __('Edition area'),
                'rad_material_specific_details_area' => __('Class of material specific details area'),
                'rad_dates_of_creation_area' => __('Dates of creation area'),
                'rad_physical_description_area' => __('Physical description area'),
                'rad_publishers_series_area' => __('Publisher\'s series area'),
                'rad_archival_description_area' => __('Archival description area'),
                'rad_notes_area' => __('Notes area'),
                'rad_standard_number_area' => __('Standard number area'),
                'rad_access_points_area' => __('Access points'),
                'rad_description_control_area' => __('Control area'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="rad-elements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rad-elements-collapse" aria-expanded="false" aria-controls="rad-elements-collapse">
            <?php echo __('RAD template - elements'); ?>
          </button>
        </h2>
        <div id="rad-elements-collapse" class="accordion-collapse collapse" aria-labelledby="rad-elements-heading">
          <div class="accordion-body">
            <?php foreach ([
                'rad_archival_history' => __('Custodial history'),
                'rad_physical_condition' => __('Physical condition'),
                'rad_immediate_source' => __('Immediate source of acquisition'),
                'rad_general_notes' => __('General note(s)'),
                'rad_conservation_notes' => __('Conservation note(s)'),
                'rad_rights_notes' => __('Rights note(s)'),
                'rad_control_description_identifier' => __('Description identifier'),
                'rad_control_institution_identifier' => __('Institution identifier'),
                'rad_control_rules_conventions' => __('Rules or conventions'),
                'rad_control_status' => __('Status'),
                'rad_control_level_of_detail' => __('Level of detail'),
                'rad_control_dates' => __('Dates of creation, revision and deletion'),
                'rad_control_language' => __('Language'),
                'rad_control_script' => __('Script'),
                'rad_control_sources' => __('Sources'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="dacs-headings-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dacs-headings-collapse" aria-expanded="false" aria-controls="dacs-headings-collapse">
            <?php echo __('DACS template - area headings'); ?>
          </button>
        </h2>
        <div id="dacs-headings-collapse" class="accordion-collapse collapse" aria-labelledby="dacs-headings-heading">
          <div class="accordion-body">
            <?php foreach ([
                'dacs_identity_area' => __('Identity area'),
                'dacs_content_area' => __('Content and structure area'),
                'dacs_conditions_of_access_area' => __('Conditions of access and use area'),
                'dacs_acquisition_area' => __('Acquisition and appraisal area'),
                'dacs_materials_area' => __('Related materials area'),
                'dacs_notes_area' => __('Notes area'),
                'dacs_control_area' => __('Description control area'),
                'dacs_access_points_area' => __('Access points'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="dacs-elements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dacs-elements-collapse" aria-expanded="false" aria-controls="dacs-elements-collapse">
            <?php echo __('DACS template - elements'); ?>
          </button>
        </h2>
        <div id="dacs-elements-collapse" class="accordion-collapse collapse" aria-labelledby="dacs-elements-heading">
          <div class="accordion-body">
            <?php foreach ([
                'dacs_physical_access' => __('Physical access'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="original-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#original-collapse" aria-expanded="false" aria-controls="original-collapse">
            <?php echo __('%1% metadata - original file', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?>
          </button>
        </h2>
        <div id="original-collapse" class="accordion-collapse collapse" aria-labelledby="original-heading">
          <div class="accordion-body">
            <?php foreach ([
                'digital_object_preservation_system_original_file_name' => __('File name'),
                'digital_object_preservation_system_original_format_name' => __('Format name'),
                'digital_object_preservation_system_original_format_version' => __('Format version'),
                'digital_object_preservation_system_original_format_registry_key' => __('Format registry key'),
                'digital_object_preservation_system_original_format_registry_name' => __('Format registry name'),
                'digital_object_preservation_system_original_file_size' => __('File size'),
                'digital_object_preservation_system_original_ingested' => __('Ingested'),
                'digital_object_preservation_system_original_permissions' => __('Permissions'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="preservation-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#preservation-collapse" aria-expanded="false" aria-controls="preservation-collapse">
            <?php echo __('%1% metadata - preservation copy', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?>
          </button>
        </h2>
        <div id="preservation-collapse" class="accordion-collapse collapse" aria-labelledby="preservation-heading">
          <div class="accordion-body">
            <?php foreach ([
                'digital_object_preservation_system_preservation_file_name' => __('File name'),
                'digital_object_preservation_system_preservation_file_size' => __('File size'),
                'digital_object_preservation_system_preservation_normalized' => __('Normalized'),
                'digital_object_preservation_system_preservation_permissions' => __('Permissions'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="master-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#master-collapse" aria-expanded="false" aria-controls="master-collapse">
            <?php echo __('%1% metadata - master file', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?>
          </button>
        </h2>
        <div id="master-collapse" class="accordion-collapse collapse" aria-labelledby="master-heading">
          <div class="accordion-body">
            <?php foreach ([
                'digital_object_url' => __('URL'),
                'digital_object_file_name' => __('File name'),
                'digital_object_geolocation' => __('Latitude and longitude'),
                'digital_object_media_type' => __('Media type'),
                'digital_object_mime_type' => __('MIME type'),
                'digital_object_file_size' => __('File size'),
                'digital_object_uploaded' => __('Uploaded'),
                'digital_object_permissions' => _('Permissions'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="reference-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reference-collapse" aria-expanded="false" aria-controls="reference-collapse">
            <?php echo __('%1% metadata - reference copy', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?>
          </button>
        </h2>
        <div id="reference-collapse" class="accordion-collapse collapse" aria-labelledby="reference-heading">
          <div class="accordion-body">
            <?php foreach ([
                'digital_object_reference_file_name' => __('File name'),
                'digital_object_reference_media_type' => __('Media type'),
                'digital_object_reference_mime_type' => __('MIME type'),
                'digital_object_reference_file_size' => __('File size'),
                'digital_object_reference_uploaded' => __('Uploaded'),
                'digital_object_reference_permissions' => _('Permissions'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="thumbnail-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#thumbnail-collapse" aria-expanded="false" aria-controls="thumbnail-collapse">
            <?php echo __('%1% metadata - thumbnail copy', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]); ?>
          </button>
        </h2>
        <div id="thumbnail-collapse" class="accordion-collapse collapse" aria-labelledby="thumbnail-heading">
          <div class="accordion-body">
            <?php foreach ([
                'digital_object_thumbnail_file_name' => __('File name'),
                'digital_object_thumbnail_media_type' => __('Media type'),
                'digital_object_thumbnail_mime_type' => __('MIME type'),
                'digital_object_thumbnail_file_size' => __('File size'),
                'digital_object_thumbnail_uploaded' => __('Uploaded'),
                'digital_object_thumbnail_permissions' => _('Permissions'), ] as $key => $value) { ?>

              <?php echo render_field($form[$key]->label($value)); ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="storage-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#storage-collapse" aria-expanded="false" aria-controls="storage-collapse">
            <?php echo __('Physical storage'); ?>
          </button>
        </h2>
        <div id="storage-collapse" class="accordion-collapse collapse" aria-labelledby="storage-heading">
          <div class="accordion-body">
            <div class="form-item form-item-checkbox">
              <?php echo render_field($form['physical_storage']->label(__('Physical storage'))); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
