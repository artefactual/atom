<?php use_helper('Javascript'); ?>
<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Import multiple digital objects'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title(new sfIsadPlugin($resource)); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <noscript>
    <div class="alert alert-warning" role="alert">
      <?php echo __('Your browser does not support JavaScript. See %1%minimum requirements%2%.', ['%1%' => '<a href="https://www.accesstomemory.org/wiki/index.php?title=Minimum_requirements">', '%2%' => '</a>']); ?>
    </div>

    <section class="actions mb-3">
      <?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light']); ?>
    </section>
  </noscript>

  <?php if (QubitDigitalObject::reachedAppUploadLimit()) { ?>

    <div id="upload_limit_reached">
      <div class="alert alert-warning" role="alert">
        <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your system administrator to increase the available disk space.', ['%1%' => sfConfig::get('app_upload_limit')]); ?>
      </div>

      <section class="actions mb-3">
        <?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light']); ?>
      </section>
    </div>

  <?php } else { ?>
    <div class="multifileupload-form"
      data-multifileupload-max-file-size="<?php echo $maxFileSize; ?>"
      data-multifileupload-max-post-size="<?php echo $maxPostSize; ?>"
      data-multifileupload-upload-response-path="<?php echo $uploadResponsePath; ?>"
      data-multifileupload-slug="<?php echo $resource->slug; ?>"
      data-multifileupload-thumb-width = 150;
      data-multifileupload-i18n-max-file-size-message="<?php echo __('Maximum file size: '); ?>"
      data-multifileupload-i18n-max-post-size-message="<?php echo __('Maximum total upload size: '); ?>"
      data-multifileupload-i18n-max-size-note="<?php echo __('%{maxFileSizeMessage}; %{maxPostSizeMessage}'); ?>"
      data-multifileupload-i18n-retry="<?php echo __('Retry'); ?>"
      data-multifileupload-i18n-info-object-title="<?php echo __('Title'); ?>"
      data-multifileupload-i18n-save="<?php echo __('Save'); ?>"
      data-multifileupload-i18n-add-more-files="<?php echo __('Add more files'); ?>"
      data-multifileupload-i18n-add-more="<?php echo __('Add more'); ?>"
      data-multifileupload-i18n-adding-more-files="<?php echo __('Adding more files'); ?>"
      data-multifileupload-i18n-some-files-failed-error="<?php echo __('Some files failed to upload. Press the \\\'Import\\\' button to continue importing anyways, or press \\\'Retry\\\' to re-attempt upload.'); ?>"
      data-multifileupload-i18n-retry-success="<?php echo __('Files successfully uploaded! Press the \\\'Import\\\' button to complete importing these files.'); ?>"
      data-multifileupload-i18n-file-selected="<?php echo __('%{smart_count} file selected'); ?>"
      data-multifileupload-i18n-files-selected="<?php echo __('%{smart_count} files selected'); ?>"
      data-multifileupload-i18n-uploading="<?php echo __('Uploading'); ?>"
      data-multifileupload-i18n-complete="<?php echo __('Complete'); ?>"
      data-multifileupload-i18n-upload-failed="<?php echo __('Upload failed'); ?>"
      data-multifileupload-i18n-remove-file="<?php echo __('Remove file'); ?>"
      data-multifileupload-i18n-drop-file="<?php echo __('Drop files here, paste or %{browse}'); ?>"
      data-multifileupload-i18n-file-uploaded-of-total="<?php echo __('%{complete} of %{smart_count} file uploaded'); ?>"
      data-multifileupload-i18n-files-uploaded-of-total="<?php echo __('%{complete} of %{smart_count} files uploaded'); ?>"
      data-multifileupload-i18n-data-uploaded-of-total="<?php echo __('%{complete} of %{total}'); ?>"
      data-multifileupload-i18n-time-left="<?php echo __('%{time} left'); ?>"
      data-multifileupload-i18n-cancel="<?php echo __('Cancel'); ?>"
      data-multifileupload-i18n-edit="<?php echo __('Edit'); ?>"
      data-multifileupload-i18n-back="<?php echo __('Back'); ?>"
      data-multifileupload-i18n-editing="<?php echo __('Editing %{file}'); ?>"
      data-multifileupload-i18n-uploading-file="<?php echo __('Uploading %{smart_count} file'); ?>"
      data-multifileupload-i18n-uploading-files="<?php echo __('Uploading %{smart_count} files'); ?>"
      data-multifileupload-i18n-importing="<?php echo __('Importing digital objects - please wait...'); ?>"
      data-multifileupload-i18n-failed-to-upload="<?php echo __('Failed to upload %{file}'); ?>"
      data-multifileupload-i18n-size-error="<?php echo __('Skipping file %{fileName} because file size %{fileSize} is larger than file size limit of %{maxSize} MB'); ?>"
      data-multifileupload-i18n-no-files-error="<?php echo __('Please add a file to begin uploading.'); ?>"
      data-multifileupload-i18n-no-successful-files-error="<?php echo __('Files not uploaded successfully. Please retry.'); ?>"
      data-multifileupload-i18n-post-size-error="<?php echo __('Upload limit of %{maxPostSize} MB reached. Unable to add additional files.'); ?>"
      data-multifileupload-i18n-alert-close="<?php echo __('Close'); ?>">

      <?php echo $form->renderGlobalErrors(); ?>

      <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'multiFileUpload']), ['id' => 'multiFileUploadForm', 'style' => 'inline']); ?>

        <?php echo $form->renderHiddenFields(); ?>

        <div class="accordion mb-3">
          <div class="accordion-item">
            <h2 class="accordion-header" id="upload-heading">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#upload-collapse" aria-expanded="true" aria-controls="upload-collapse">
                <?php echo __('Import multiple digital objects'); ?>
              </button>
            </h2>
            <div id="upload-collapse" class="accordion-collapse collapse show" aria-labelledby="upload-heading">
              <div class="accordion-body">
                <div class="alert alert-info" role="alert">
                  <p><?php echo __('Add your digital objects by dragging and dropping local files into the pane below, or by clicking the browse link to open your local file explorer.'); ?></p>
                  <p><?php echo __('The Title and Level of description values entered on this page will be applied to each child description created for the associated digital objects - \'%dd%\' represents an incrementing 2-value number, so by default descriptions created via this uploader will be named image 01, image 02, etc.'); ?></p>
                  <p><?php echo __('You will also be able to review and individually modify each description title on the next page after clicking "Upload."'); ?></p>
                </div>

                <?php echo render_field($form->title
                    ->help(__('The "<strong>%dd%</strong>" placeholder will be replaced with a incremental number (e.g. \'image <strong>01</strong>\', \'image <strong>02</strong>\')'))
                    ->label(__('Title'))
                ); ?>

                <?php echo render_field($form->levelOfDescription
                    ->label(__('Level of description'))
                ); ?>

                <h3 class="fs-6 mb-2">
                  <?php echo __('Digital objects'); ?>
                </h3>

                <div id="uploads"></div>

                <div id="uiElements" style="display: inline;">
                  <div id="uploaderContainer">
                    <div class="uppy-dashboard"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <ul class="actions mb-3 nav gap-2">
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
          <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Upload'); ?>"></li>
        </ul>

      </form>
    </div>
  <?php } ?>

<?php end_slot(); ?>
