<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Visible elements') ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'visibleElements')), array('method' => 'post')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <fieldset class="collapsible collapsed">
        <legend><?php echo __('ISAD template - area headings') ?></legend>

        <?php foreach (array(
          'isad_identity_area' => __('Identity area'),
          'isad_context_area' => __('Context area'),
          'isad_content_and_structure_area' => __('Content and structure area'),
          'isad_conditions_of_access_use_area' => __('Conditions of access and use area'),
          'isad_allied_materials_area' => __('Allied materials area'),
          'isad_notes_area' => __('Notes area'),
          'isad_access_points_area' => __('Access points'),
          'isad_description_control_area' => __('Description control area')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>

      <fieldset class="collapsible collapsed">

        <legend><?php echo __('ISAD template - elements') ?></legend>

        <?php foreach (array(
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
          'isad_control_archivists_notes' => __('Archivist\'s notes')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>


      <fieldset class="collapsible collapsed">
        <legend><?php echo __('RAD template - area headings') ?></legend>

        <?php foreach (array(
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
          'rad_description_control_area' => __('Control area')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>

      <fieldset class="collapsible collapsed">

        <legend><?php echo __('RAD template - elements') ?></legend>

        <?php foreach (array(
          'rad_archival_history' => __('Custodial history'),
          'rad_physical_condition' => __('Physical condition'),
          'rad_immediate_source' => __('Immediate source of acquisition'),
          'rad_general_notes' => __('General note(s)'),
          'rad_conservation_notes' => __('Conservation note(s)'),
          'rad_control_description_identifier' => __('Description identifier'),
          'rad_control_institution_identifier' => __('Institution identifier'),
          'rad_control_rules_conventions' => __('Rules or conventions'),
          'rad_control_status' => __('Status'),
          'rad_control_level_of_detail' => __('Level of detail'),
          'rad_control_dates' => __('Dates of creation, revision and deletion'),
          'rad_control_language' => __('Language'),
          'rad_control_script' => __('Script'),
          'rad_control_sources' => __('Sources')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>

      <fieldset class="collapsible collapsed">

        <legend><?php echo __('%1% metadata area', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))) ?></legend>

        <?php foreach (array(
          'digital_object_url' => __('URL'),
          'digital_object_file_name' => __('File name'),
          'digital_object_geolocation' => __('Latitude and longitude'),
          'digital_object_media_type' => __('Media type'),
          'digital_object_mime_type' => __('MIME type'),
          'digital_object_file_size' => __('File size'),
          'digital_object_uploaded' => __('Uploaded')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>

      <fieldset class="collapsible collapsed">

        <legend><?php echo __('Physical storage') ?></legend>

        <div class="form-item form-item-checkbox">
          <?php echo $form['physical_storage'] ?>
          <?php echo $form['physical_storage']
            ->label('Physical storage')
            ->renderLabel() ?>
        </div>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), '@homepage', array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
