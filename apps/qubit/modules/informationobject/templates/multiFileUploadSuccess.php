<?php use_helper('Javascript') ?>
<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Import multiple digital objects') ?>
    <span class="sub"><?php echo render_title(new sfIsadPlugin($resource)) ?> </span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <noscript>
    <div class="messages warning">
      <?php echo __('Your browser does not support JavaScript. See %1%minimum requirements%2%.', array('%1%' => '<a href="https://www.accesstomemory.org/wiki/index.php?title=Minimum_requirements">', '%2%' => '</a>')) ?>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>
  </noscript>

  <div id="no-flash" style="display: none;">
    <div class="messages warning">
      <?php echo __('Your browser does not have Flash player installed. To import digital objects, please <a href="https://get.adobe.com/flashplayer/">Download Adobe Flash Player</a> (requires version 9.0.45 or higher)') ?>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>
  </div>

  <?php if (QubitDigitalObject::reachedAppUploadLimit()): ?>

    <div id="upload_limit_reached">
      <div class="messages warning">
        <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your system administrator to increase the available disk space.',  array('%1%' => sfConfig::get('app_upload_limit'))) ?>
      </div>

      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
        </ul>
      </sectin>
    </div>

  <?php else: ?>

    <?php echo $form->renderGlobalErrors() ?>

    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'multiFileUpload')), array('id' => 'multiFileUploadForm', 'style' => 'display: none')) ?>

      <?php echo $form->renderHiddenFields() ?>

      <section id="content">

        <fieldset class="collapsible">

          <legend><?php echo __('Import multiple digital objects') ?></legend>

          <?php echo $form->title
            ->help(__('The "<strong>%dd%</strong>" placeholder will be replaced with a incremental number (e.g. \'image <strong>01</strong>\', \'image <strong>02</strong>\')'))
            ->label(__('Title'))
            ->renderRow() ?>

          <?php echo $form->levelOfDescription
            ->label(__('Level of description'))
            ->renderRow() ?>

          <div class="multiFileUpload section">

            <h3><?php echo __('Digital objects') ?></h3>

            <div id="uploads"></div>

            <div id="uiElements" style="display: inline;">
              <div id="uploaderContainer">
                <div id="uploaderOverlay" style="position: absolute; z-index: 2;"></div>
                <div id="selectFilesLink" style="z-index: 1"><a id="selectLink" href="#">Select files</a></div>
              </div>
            </div>

          </div>

        </fieldset>

      </section>

      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
        </ul>
      </section>

    </form>

  <?php endif; ?>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo javascript_tag(<<<content
// If JavaScript and Flash Player installed
if (0 == YAHOO.deconcept.SWFObjectUtil.getPlayerVersion().major)
{
  var noflash = jQuery('#no-flash');
  noflash.show();
}
else
{
  jQuery('form#multiFileUploadForm').show();
}

// Uncomment following line to enable logging to Firebug or Safari js console
//YAHOO.widget.Logger.enableBrowserConsole();

YAHOO.widget.Uploader.SWFURL = '$uploadSwfPath';

Qubit.multiFileUpload.maxUploadSize = '$maxUploadSize';
Qubit.multiFileUpload.uploadTmpDir = '$uploadTmpDir';
Qubit.multiFileUpload.uploadResponsePath = '$uploadResponsePath';
Qubit.multiFileUpload.informationObjectId = '$resource->id';
Qubit.multiFileUpload.thumbWidth = 150;

Qubit.multiFileUpload.i18nUploading = '{$sf_context->i18n->__('Uploading...')}';
Qubit.multiFileUpload.i18nLoadingPreview = '{$sf_context->i18n->__('Loading preview...')}';
Qubit.multiFileUpload.i18nWaiting = '{$sf_context->i18n->__('Waiting...')}';
Qubit.multiFileUpload.i18nUploadError = '{$sf_context->i18n->__('Upload error, retry?')}';
Qubit.multiFileUpload.i18nInfoObjectTitle = '{$sf_context->i18n->__('Title')}';
Qubit.multiFileUpload.i18nFilename  = '{$sf_context->i18n->__('Filename')}';
Qubit.multiFileUpload.i18nFilesize  = '{$sf_context->i18n->__('Filesize')}';
Qubit.multiFileUpload.i18nDelete = '{$sf_context->i18n->__('Delete')}';
Qubit.multiFileUpload.i18nCancel = '{$sf_context->i18n->__('Cancel')}';
Qubit.multiFileUpload.i18nStart = '{$sf_context->i18n->__('Start')}';
Qubit.multiFileUpload.i18nDuplicateFile = '{$sf_context->i18n->__('Warning: duplicate of %1%')}';
Qubit.multiFileUpload.i18nOversizedFile = '{$sf_context->i18n->__('This file couldn\\\'t be uploaded because of file size upload limits')}';

content
) ?>
<?php end_slot() ?>
