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

        <legend><?php echo __('ISAD template') ?></legend>

        <?php foreach (array(
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
          'isad_control_archivists_notes' => __('Archivist\'s notes'),
          'isad_archival_history' => __('Archival history')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>

      <fieldset class="collapsible collapsed">

        <legend><?php echo __('RAD template') ?></legend>

        <?php foreach (array(
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
          'rad_control_sources' => __('Sources'),
          'rad_archival_history' => __('Archival history')) as $key => $value): ?>

          <div class="form-item form-item-checkbox">
            <?php echo $form[$key] ?>
            <?php echo $form[$key]
              ->label($value)
              ->renderLabel() ?>
          </div>

        <?php endforeach; ?>

      </fieldset>

      <fieldset class="collapsible collapsed">

        <legend><?php echo __('Digital object metadata area') ?></legend>

        <?php foreach (array(
          'digital_object_url' => __('URL'),
          'digital_object_file_name' => __('File name'),
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
