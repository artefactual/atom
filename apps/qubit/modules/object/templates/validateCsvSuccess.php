<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
    <h1><?php echo $title; ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

    <?php echo $form->renderFormTag(url_for(['module' => 'object', 'action' => 'validateCsv']), ['enctype' => 'multipart/form-data']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <section id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('CSV Validation options'); ?></legend>

          <div class="form-item">
            <label><?php echo __('Type'); ?></label>
            <select name="objectType">
              <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject'); ?></option>
              <option value="accession"><?php echo sfConfig::get('app_ui_label_accession', __('Accession')); ?></option>
              <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor'); ?></option>
              <option value="authorityRecordRelationship"><?php echo sfConfig::get('app_ui_label_authority_record_relationships'); ?></option>
              <option value="event"><?php echo sfConfig::get('app_ui_label_event', __('Event')); ?></option>
              <option value="repository"><?php echo sfConfig::get('app_ui_label_repository', __('Repository')); ?></option>
            </select>
          </div>
      </fieldset>

      <fieldset class="collapsible">
        <legend><?php echo __('Select file'); ?></legend>

        <div class="form-item">
          <label><?php echo __('Select a CSV file to validate'); ?></label>
          <input name="file" type="file"/>
        </div>
      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Validate'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
