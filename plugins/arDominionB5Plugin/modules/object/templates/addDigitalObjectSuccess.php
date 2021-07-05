<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
    <span class="sub"><?php echo $resourceDescription; ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php if (QubitDigitalObject::reachedAppUploadLimit()) { ?>

    <div class="messages warning">
      <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your AtoM system administrator to increase the available disk space.', ['%1%' => sfConfig::get('app_upload_limit')]); ?>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => $sf_request->module], ['class' => 'c-btn']); ?></li>
      </ul>
    </section>

  <?php } else { ?>

    <?php echo $form->renderGlobalErrors(); ?>

    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'object', 'action' => 'addDigitalObject']), ['id' => 'uploadForm']); ?>

      <?php echo $form->renderHiddenFields(); ?>

      <div class="accordion" id="object-add-do">
        <div class="accordion-item">
          <h2 class="accordion-header" id="upload-heading">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#upload-collapse" aria-expanded="true" aria-controls="upload-collapse">
              <?php echo __('Upload a %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
            </button>
          </h2>
          <div id="upload-collapse" class="accordion-collapse collapse show" aria-labelledby="upload-heading" data-bs-parent="#object-add-do">
            <div class="accordion-body">
              <?php if (null == $repository || -1 == $repository->uploadLimit || floatval($repository->getDiskUsage() / pow(10, 9)) < floatval($repository->uploadLimit)) { ?>

                <?php echo $form->file->renderRow(); ?>

              <?php } elseif (0 == $repository->uploadLimit) { ?>

                <div class="messages warning">
                  <?php echo __('Uploads for <a href="%1%">%2%</a> are disabled', [
                      '%1%' => url_for([$repository, 'module' => 'repository']),
                      '%2%' => $repository->__toString(), ]); ?>
                </div>

              <?php } else { ?>

                <div class="messages warning">
                  <?php echo __('The upload limit of %1% GB for <a href="%2%">%3%</a> has been reached', [
                      '%1%' => $repository->uploadLimit,
                      '%2%' => url_for([$repository, 'module' => 'repository']),
                      '%3%' => $repository->__toString(), ]); ?>
                </div>

              <?php } ?>
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="external-heading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#external-collapse" aria-expanded="false" aria-controls="external-collapse">
              <?php echo __('Link to an external %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
            </button>
          </h2>
          <div id="external-collapse" class="accordion-collapse collapse" aria-labelledby="external-heading" data-bs-parent="#object-add-do">
            <div class="accordion-body">
              <?php echo $form->url->renderRow(); ?>
            </div>
          </div>
        </div>
      </div>

      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => $sf_request->module], ['class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
        </ul>
      </section>

    </form>

  <?php } ?>

<?php end_slot(); ?>
