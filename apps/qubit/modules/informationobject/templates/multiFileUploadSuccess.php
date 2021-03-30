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

          <div class="multiFileUpload">

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

<?php slot('after-content'); ?>

<?php echo javascript_tag(<<<content
Qubit.multiFileUpload.maxFileSize = '{$maxFileSize}';
Qubit.multiFileUpload.maxPostSize = '{$maxPostSize}';
Qubit.multiFileUpload.uploadResponsePath = '{$uploadResponsePath}';
Qubit.multiFileUpload.slug = '{$resource->slug}';
Qubit.multiFileUpload.thumbWidth = 150;

Qubit.multiFileUpload.i18nMaxFileSizeMessage = '{$sf_context->i18n->__('Maximum file size: ')}';
Qubit.multiFileUpload.i18nMaxPostSizeMessage = '{$sf_context->i18n->__('Maximum total upload size: ')}';
Qubit.multiFileUpload.i18nMaxSizeNote = '{$sf_context->i18n->__('%{maxFileSizeMessage}; %{maxPostSizeMessage}')}';
Qubit.multiFileUpload.i18nRetry = '{$sf_context->i18n->__('Retry')}';
Qubit.multiFileUpload.i18nInfoObjectTitle = '{$sf_context->i18n->__('Title')}';
Qubit.multiFileUpload.i18nSave = '{$sf_context->i18n->__('Save')}';
Qubit.multiFileUpload.i18nAddMoreFiles = '{$sf_context->i18n->__('Add more files')}';
Qubit.multiFileUpload.i18nAddMore = '{$sf_context->i18n->__('Add more')}';
Qubit.multiFileUpload.i18nAddingMoreFiles = '{$sf_context->i18n->__('Adding more files')}';
Qubit.multiFileUpload.i18nSomeFilesFailedError = '{$sf_context->i18n->__('Some files failed to upload. Press the \\\'Import\\\' button to continue importing anyways, or press \\\'Retry\\\' to re-attempt upload.')}';
Qubit.multiFileUpload.i18nRetrySuccess = '{$sf_context->i18n->__('Files successfully uploaded! Press the \\\'Import\\\' button to complete importing these files.')}';
Qubit.multiFileUpload.i18nFileSelected = '{$sf_context->i18n->__('%{smart_count} file selected')}';
Qubit.multiFileUpload.i18nFilesSelected = '{$sf_context->i18n->__('%{smart_count} files selected')}';
Qubit.multiFileUpload.i18nUploading = '{$sf_context->i18n->__('Uploading')}';
Qubit.multiFileUpload.i18nComplete = '{$sf_context->i18n->__('Complete')}';
Qubit.multiFileUpload.i18nUploadFailed = '{$sf_context->i18n->__('Upload failed')}';
Qubit.multiFileUpload.i18nRemoveFile = '{$sf_context->i18n->__('Remove file')}';
Qubit.multiFileUpload.i18nDropFile = '{$sf_context->i18n->__('Drop files here, paste or %{browse}')}';
Qubit.multiFileUpload.i18nFileUploadedOfTotal = '{$sf_context->i18n->__('%{complete} of %{smart_count} file uploaded')}';
Qubit.multiFileUpload.i18nFilesUploadedOfTotal = '{$sf_context->i18n->__('%{complete} of %{smart_count} files uploaded')}';
Qubit.multiFileUpload.i18nDataUploadedOfTotal = '{$sf_context->i18n->__('%{complete} of %{total}')}';
Qubit.multiFileUpload.i18nTimeLeft = '{$sf_context->i18n->__('%{time} left')}';
Qubit.multiFileUpload.i18nCancel = '{$sf_context->i18n->__('Cancel')}';
Qubit.multiFileUpload.i18nEdit = '{$sf_context->i18n->__('Edit')}';
Qubit.multiFileUpload.i18nBack = '{$sf_context->i18n->__('Back')}';
Qubit.multiFileUpload.i18nEditing = '{$sf_context->i18n->__('Editing %{file}')}';
Qubit.multiFileUpload.i18nUploadingFile = '{$sf_context->i18n->__('Uploading %{smart_count} file')}';
Qubit.multiFileUpload.i18nUploadingFiles = '{$sf_context->i18n->__('Uploading %{smart_count} files')}';
Qubit.multiFileUpload.i18nImporting = '{$sf_context->i18n->__('Importing digital objects - please wait...')}';
Qubit.multiFileUpload.i18nFailedToUpload = '{$sf_context->i18n->__('Failed to upload %{file}')}';
Qubit.multiFileUpload.i18nSizeError = '{$sf_context->i18n->__('Skipping file %{fileName} because file size %{fileSize} is larger than file size limit of %{maxSize} MB')}';
Qubit.multiFileUpload.i18nNoFilesError = '{$sf_context->i18n->__('Please add a file to begin uploading.')}';
Qubit.multiFileUpload.i18nNoSuccessfulFilesError = '{$sf_context->i18n->__('Files not uploaded successfully. Please retry.')}';
Qubit.multiFileUpload.i18nPostSizeError = '{$sf_context->i18n->__('Upload limit of %{maxPostSize} MB reached. Unable to add additional files.')}';
content
); ?>
<?php end_slot(); ?>
