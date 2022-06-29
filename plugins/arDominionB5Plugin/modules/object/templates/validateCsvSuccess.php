<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
    <h1><?php echo $title; ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'object', 'action' => 'validateCsv']), ['enctype' => 'multipart/form-data']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="options-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#options-collapse" aria-expanded="true" aria-controls="options-collapse">
            <?php echo __('CSV Validation options'); ?>
          </button>
        </h2>
        <div id="options-collapse" class="accordion-collapse collapse show" aria-labelledby="options-heading">
          <div class="accordion-body">
            <div class="mb-3">
              <label class="form-label" for="object-type-select"><?php echo __('Type'); ?></label>
              <select class="form-select" name="objectType" id="object-type-select">
                <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject'); ?></option>
                <option value="accession"><?php echo sfConfig::get('app_ui_label_accession', __('Accession')); ?></option>
                <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor'); ?></option>
                <option value="authorityRecordRelationship"><?php echo sfConfig::get('app_ui_label_authority_record_relationships'); ?></option>
                <option value="event"><?php echo sfConfig::get('app_ui_label_event', __('Event')); ?></option>
                <option value="repository"><?php echo sfConfig::get('app_ui_label_repository', __('Repository')); ?></option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="select-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#select-collapse" aria-expanded="true" aria-controls="select-collapse">
            <?php echo __('Select file'); ?>
          </button>
        </h2>
        <div id="select-collapse" class="accordion-collapse collapse show" aria-labelledby="select-heading">
          <div class="accordion-body">
            <div class="mb-3">
              <label for="file-input" class="form-label"><?php echo __('Select a CSV file to validate'); ?></label>
              <input class="form-control" type="file" id="file-input" name="file">
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Validate'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
