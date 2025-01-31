<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource)) { ?>
    <div class="multiline-header d-flex flex-column mb-3">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo $title; ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo render_title($resource); ?>
      </span>
    </div>
  <?php } else { ?>
    <h1><?php echo $title; ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'object', 'action' => 'importSelect']), ['enctype' => 'multipart/form-data']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'object', 'action' => 'importSelect']), ['enctype' => 'multipart/form-data']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="import-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#import-collapse" aria-expanded="true" aria-controls="import-collapse">
            <?php echo __('Import options'); ?>
          </button>
        </h2>
        <div id="import-collapse" class="accordion-collapse collapse show" aria-labelledby="import-heading">
          <div class="accordion-body">
            <input type="hidden" name="importType" value="<?php echo esc_entities($type); ?>"/>

            <?php if ('csv' == $type) { ?>
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
            <?php } ?>

            <?php if ('csv' != $type) { ?>
              <p class="alert alert-info text-center"><?php echo __('If you are importing a SKOS file to a taxonomy other than subjects, please go to the %1%', ['%1%' => link_to(__('SKOS import page'), ['module' => 'sfSkosPlugin', 'action' => 'import'], ['class' => 'alert-link'])]); ?></p>
              <div class="mb-3">
                <label for="object-type-select" class="form-label"><?php echo __('Type'); ?></label>
                <select class="form-select" name="objectType" id="object-type-select">
                  <option value="ead"><?php echo __('EAD 2002'); ?></option>
                  <option value="eac-cpf"><?php echo __('EAC CPF'); ?></option>
                  <option value="mods"><?php echo __('MODS'); ?></option>
                  <option value="dc"><?php echo __('DC'); ?></option>
                </select>
              </div>
            <?php } ?>

            <div id="updateBlock">

              <?php if ('csv' == $type) { ?>
                <div class="mb-3">
                  <label class="form-label" for="update-type-select"><?php echo __('Update behaviours'); ?></label>
                  <select class="form-select" name="updateType" id="update-type-select">
                    <option value="import-as-new"><?php echo __('Ignore matches and create new records on import'); ?></option>
                    <option value="match-and-update"><?php echo __('Update matches ignoring blank fields in CSV'); ?></option>
                    <option value="delete-and-replace"><?php echo __('Delete matches and replace with imported records'); ?></option>
                  </select>
                </div>
              <?php } ?>

              <?php if ('csv' != $type) { ?>
                <div class="mb-3">
                  <label class="form-label" for="update-type-select"><?php echo __('Update behaviours'); ?></label>
                  <select class="form-select" name="updateType" id="update-type-select">
                    <option value="import-as-new"><?php echo __('Ignore matches and import as new'); ?></option>
                    <option value="delete-and-replace"><?php echo __('Delete matches and replace with imports'); ?></option>
                  </select>
                </div>
              <?php } ?>

	      <div class="form-item">
                <div class="panel panel-default" id="matchingOptions" style="display:none;">
                  <div class="panel-body">
                    <div class="mb-3 form-check">
                      <input class="form-check-input" name="skipUnmatched" id="skip-unmatched-input" type="checkbox"/>
                      <label class="form-check-label" for="skip-unmatched-input"><?php echo __('Skip unmatched records'); ?></label>
                    </div>

                    <div class="criteria">
                      <div class="repos-limit">
                        <?php echo render_field($form->repos->label(__('Limit matches to:'))); ?>
                      </div>

                      <div class="collection-limit">
                        <?php echo render_field(
                          $form->collection->label(__('Top-level description')),
                          null,
                          [
                              'class' => 'form-autocomplete',
                              'extraInputs' => '<input class="list" type="hidden" value="'
                                  .url_for([
                                      'module' => 'informationobject',
                                      'action' => 'autocomplete',
                                      'parent' => QubitInformationObject::ROOT_ID,
                                      'filterDrafts' => true,
                                  ])
                                  .'">',
                          ]
                        ); ?>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="panel panel-default" id="importAsNewOptions">
                  <div class="panel-body">
                    <div class="mb-3 form-check">
                      <input class="form-check-input" name="skipMatched" id="skip-matched-input" type="checkbox"/>
                      <label class="form-check-label" for="skip-matched-input"><?php echo __('Skip matched records'); ?></label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3 form-check" id="noIndex">
              <input class="form-check-input" name="noIndex" id="no-index-input" type="checkbox"/>
              <label class="form-check-label" for="no-index-input"><?php echo __('Do not index imported items'); ?></label>
            </div>

            <?php if ('csv' == $type && sfConfig::get('app_csv_transform_script_name')) { ?>
              <div class="mb-3 form-check">
                <input class="form-check-input" name="doCsvTransform" id="do-csv-transform-input" type="checkbox"/>
                <label class="form-check-label" for="do-csv-transform-input" aria-described-by="do-csv-transform-help"><?php echo __('Include transformation script'); ?></label>
                <div class="form-text" id="do-csv-transform-help"><?php echo __(sfConfig::get('app_csv_transform_script_name')); ?></div>
              </div>
            <?php } ?>

          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="file-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#file-collapse" aria-expanded="true" aria-controls="file-collapse">
            <?php echo __('Select file'); ?>
          </button>
        </h2>
        <div id="file-collapse" class="accordion-collapse collapse show" aria-labelledby="file-heading">
          <div class="accordion-body">
            <div class="mb-3">
              <label for="import-file" class="form-label"><?php echo __('Select a file to import'); ?></label>
              <input class="form-control" type="file" id="import-file" name="file">
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Import'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
