<?php use_helper('Javascript'); ?>
<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Import multiple digital objects'); ?>
    <span class="sub"><?php echo render_title(new sfIsadPlugin($resource)); ?> </span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <noscript>
    <div class="messages warning">
      <?php echo __('Your browser does not support JavaScript. See %1%minimum requirements%2%.', ['%1%' => '<a href="https://www.accesstomemory.org/wiki/index.php?title=Minimum_requirements">', '%2%' => '</a>']); ?>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'c-btn']); ?></li>
      </ul>
    </section>
  </noscript>

  <?php if (QubitDigitalObject::reachedAppUploadLimit()) { ?>

    <div id="upload_limit_reached">
      <div class="messages warning">
        <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your system administrator to increase the available disk space.', ['%1%' => sfConfig::get('app_upload_limit')]); ?>
      </div>

      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject']); ?></li>
        </ul>
      </section>
    </div>

  <?php } else { ?>

    <?php echo $form->renderGlobalErrors(); ?>

    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'multiFileUpload']), ['id' => 'multiFileUploadForm', 'style' => 'inline']); ?>

      <?php echo $form->renderHiddenFields(); ?>

      <section id="content">

        <fieldset class="collapsible">

          <legend><?php echo __('Import multiple digital objects'); ?></legend>

          <div class="alert alert-info">
            <p><?php echo __('Add your digital objects by dragging and dropping local files into the pane below, or by clicking the browse link to open your local file explorer.'); ?></p>
            <p><?php echo __('The Title and Level of description values entered on this page will be applied to each child description created for the associated digital objects - \'%dd%\' represents an incrementing 2-value number, so by default descriptions created via this uploader will be named image 01, image 02, etc.'); ?></p>
            <p><?php echo __('You will also be able to review and individually modify each description title on the next page after clicking "Upload."'); ?></p>
          </div>

          <?php echo $form->title
              ->help(__('The "<strong>%dd%</strong>" placeholder will be replaced with a incremental number (e.g. \'image <strong>01</strong>\', \'image <strong>02</strong>\')'))
              ->label(__('Title'))
              ->renderRow(); ?>

          <?php echo $form->levelOfDescription
              ->label(__('Level of description'))
              ->renderRow(); ?>

          <div class="multiFileUpload"
            data-multiFileUpload-maxFileSize="<?php echo $maxFileSize; ?>"
            data-multiFileUpload-maxPostSize="<?php echo $maxPostSize; ?>"
            data-multiFileUpload-uploadResponsePath="<?php echo $uploadResponsePath; ?>"
            data-multiFileUpload-slug="<?php echo $resource->slug; ?>"
            data-multiFileUpload-thumbWidth = 150;
            data-multiFileUpload-i18nMaxFileSizeMessage="<?php echo __('Maximum file size: '); ?>"
            data-multiFileUpload-i18nMaxPostSizeMessage="<?php echo __('Maximum total upload size: '); ?>"
            data-multiFileUpload-i18nMaxSizeNote="<?php echo __('%{maxFileSizeMessage}; %{maxPostSizeMessage}'); ?>"
            data-multiFileUpload-i18nRetry="<?php echo __('Retry'); ?>"
            data-multiFileUpload-i18nInfoObjectTitle="<?php echo __('Title'); ?>"
            data-multiFileUpload-i18nSave="<?php echo __('Save'); ?>"
            data-multiFileUpload-i18nAddMoreFiles="<?php echo __('Add more files'); ?>"
            data-multiFileUpload-i18nAddMore="<?php echo __('Add more'); ?>"
            data-multiFileUpload-i18nAddingMoreFiles="<?php echo __('Adding more files'); ?>"
            data-multiFileUpload-i18nSomeFilesFailedError="<?php echo __('Some files failed to upload. Press the \\\'Import\\\' button to continue importing anyways, or press \\\'Retry\\\' to re-attempt upload.'); ?>"
            data-multiFileUpload-i18nRetrySuccess="<?php echo __('Files successfully uploaded! Press the \\\'Import\\\' button to complete importing these files.'); ?>"
            data-multiFileUpload-i18nFileSelected="<?php echo __('%{smart_count} file selected'); ?>"
            data-multiFileUpload-i18nFilesSelected="<?php echo __('%{smart_count} files selected'); ?>"
            data-multiFileUpload-i18nUploading="<?php echo __('Uploading'); ?>"
            data-multiFileUpload-i18nComplete="<?php echo __('Complete'); ?>"
            data-multiFileUpload-i18nUploadFailed="<?php echo __('Upload failed'); ?>"
            data-multiFileUpload-i18nRemoveFile="<?php echo __('Remove file'); ?>"
            data-multiFileUpload-i18nDropFile="<?php echo __('Drop files here, paste or %{browse}'); ?>"
            data-multiFileUpload-i18nFileUploadedOfTotal="<?php echo __('%{complete} of %{smart_count} file uploaded'); ?>"
            data-multiFileUpload-i18nFilesUploadedOfTotal="<?php echo __('%{complete} of %{smart_count} files uploaded'); ?>"
            data-multiFileUpload-i18nDataUploadedOfTotal="<?php echo __('%{complete} of %{total}'); ?>"
            data-multiFileUpload-i18nTimeLeft="<?php echo __('%{time} left'); ?>"
            data-multiFileUpload-i18nCancel="<?php echo __('Cancel'); ?>"
            data-multiFileUpload-i18nEdit="<?php echo __('Edit'); ?>"
            data-multiFileUpload-i18nBack="<?php echo __('Back'); ?>"
            data-multiFileUpload-i18nEditing="<?php echo __('Editing %{file}'); ?>"
            data-multiFileUpload-i18nUploadingFile="<?php echo __('Uploading %{smart_count} file'); ?>"
            data-multiFileUpload-i18nUploadingFiles="<?php echo __('Uploading %{smart_count} files'); ?>"
            data-multiFileUpload-i18nImporting="<?php echo __('Importing digital objects - please wait...'); ?>"
            data-multiFileUpload-i18nFailedToUpload="<?php echo __('Failed to upload %{file}'); ?>"
            data-multiFileUpload-i18nSizeError="<?php echo __('Skipping file %{fileName} because file size %{fileSize} is larger than file size limit of %{maxSize} MB'); ?>"
            data-multiFileUpload-i18nNoFilesError="<?php echo __('Please add a file to begin uploading.'); ?>"
            data-multiFileUpload-i18nNoSuccessfulFilesError="<?php echo __('Files not uploaded successfully. Please retry.'); ?>"
            data-multiFileUpload-i18nPostSizeError="<?php echo __('Upload limit of %{maxPostSize} MB reached. Unable to add additional files.'); ?>"
            data-multiFileUpload-i18n-alert-close="<?php echo __('Close'); ?>">

            <h3><?php echo __('Digital objects'); ?></h3>

            <div id="uploads"></div>

            <div id="uiElements" style="display: inline;">
              <div id="uploaderContainer">
                  <div class="uppy-dashboard"></div>
              </div>
            </div>
          </div>
        </fieldset>

      </section>

      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Upload'); ?>"/></li>
        </ul>
      </section>

    </form>

  <?php } ?>

<?php end_slot(); ?>
