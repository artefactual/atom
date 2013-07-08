<?php use_helper('Javascript') ?>

<h1><?php echo __('Import multiple digital objects') ?></h1>

<h1 class="label"><?php echo render_title(new sfIsadPlugin($resource)) ?> </h1>

<noscript>
  <div class="messages warning">
    <?php echo __('Your browser does not support JavaScript. See %1%minimum requirements%2%.', array('%1%' => '<a href="http://accesstomemory.org/wiki/index.php?title=Minimum_requirements">', '%2%' => '</a>')) ?>
  </div>

  <ul class="actions links">
    <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
  </ul>
</noscript>

<div id="no-flash" style="display: none;">
  <div class="messages warning">
    <?php echo __('Your browser does not have Flash player installed. To import digital objects, please <a href="http://get.adobe.com/flashplayer/">Download Adobe Flash Player</a> (requires version 9.0.45 or higher)') ?>
  </div>

  <ul class="actions links">
    <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
  </ul>
</div>

<?php if (QubitDIgitalObject::reachedAppUploadLimit()): ?>

  <div id="upload_limit_reached">
    <div class="messages warning">
      <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your ICA-AtoM system administrator to increase the available disk space.',  array('%1%' => sfConfig::get('app_upload_limit'))) ?>
    </div>

    <ul class="actions links">
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
    </ul>
  </div>

<?php else: ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'multiFileUpload')), array('id' => 'multiFileUploadForm', 'style' => 'display: none')) ?>

    <?php echo $form->renderHiddenFields() ?>

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

    <div class="actions section">

      <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

      <div class="content">
        <ul class="clearfix links">
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
        </ul>
      </div>

    </div>

  </form>

<?php endif; ?>

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
