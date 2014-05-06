<?php decorate_with('layout_1col.php') ?>
<?php slot('title') ?>

<style>
.dropzone {
  width: 250px;
  height: 45px;
  border: 1px solid #ccc;
  text-align: center;
  padding: 30px;
  margin: 20px;
  font-family: Arial;
}
</style>

  <?php if (isset($resource)): ?>
    <h1 class="multiline">
      <?php echo $title ?>
      <span class="sub"><?php echo render_title($resource) ?></span>
    </h1>
  <?php else: ?>
    <h1><?php echo $title ?></h1>
  <?php endif; ?>
<?php end_slot() ?>

<?php slot('content') ?>

<?php if (isset($resource)): ?>
  <?php $url = url_for(array($resource, 'module' => 'object', 'action' => 'import')) ?>
<?php else: ?>
  <?php $url = url_for(array('module' => 'object', 'action' => 'import')) ?>
<?php endif; ?>

<?php echo "<script> var path = '" . $url . "'; </script>"?>

<div id="uploader">
    <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<script src="/curissue/vendor/plupload-2.1.1/js/plupload.full.min.js" type="text/javascript"></script>
<script src="/curissue/vendor/plupload-2.1.1/js/jquery.ui.plupload/jquery.ui.plupload.js" type="text/javascript"></script>

<script type="text/javascript">
// Initialize the widget when the DOM is ready
$(function() {
    $("#uploader").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : path,
 
        // Maximum file size
        max_file_size : '2mb',
 
        chunk_size: '1mb',
 
        // Resize images on clientside if we can
        resize : {
            width : 200,
            height : 200,
            quality : 90,
            crop: true // crop to exact dimensions
        },
 
        // Specify what files to browse for
/*
        filters : [
            {title : "Image files", extensions : "jpg,gif,png"},
            {title : "Zip files", extensions : "zip,avi"}
        ],
 */
        // Rename files by clicking on their titles
        rename: true,
         
        // Sort files
        sortable: true,
 
        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: true,
 
        // Views to activate
        views: {
            list: true,
            thumbs: true, // Show thumbs
            active: 'thumbs'
        },
 
        // Flash settings
        flash_swf_url : '/plupload/js/Moxie.swf',
     
        // Silverlight settings
        silverlight_xap_url : '/plupload/js/Moxie.xap'
    });
});
</script>

<!--
  <?php if ($sf_user->hasFlash('error')): ?>
    <div class="messages error">
      <h3><?php echo __('Error encountered') ?></h3>
      <div><?php echo $sf_user->getFlash('error') ?></div>
    </div>
  <?php endif; ?>

  <?php if (isset($resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'object', 'action' => 'import')), array('enctype' => 'multipart/form-data')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'object', 'action' => 'import')), array('enctype' => 'multipart/form-data')) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <fieldset class="collapsible">

        <legend><?php echo $title ?></legend>

        <div class="form-item">
          <label><?php echo __('Select a file to import') ?></label>
          <input name="file" type="file"/>
        </div>

        <input type="hidden" name="importType" value="<?php echo esc_entities($type) ?>"/>

        <?php if ('csv' != $type): ?>
          <div>
            <p><?php echo __('If you are importing a SKOS file to a taxonomy other than subjects, please go to the %1%', array('%1%' => link_to(__('SKOS import page'), array('module' => 'sfSkosPlugin', 'action' => 'import')))) ?></p>
          </div>
        <?php endif; ?>

        <?php if ('csv' == $type): ?>
          <div class="form-item">
            <label><?php echo __('Type') ?></label>
            <select name="csvObjectType">
              <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject') ?></option>
              <option value="accession"><?php echo sfConfig::get('app_ui_label_accession', __('Accession')) ?></option>
              <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor') ?></option>
              <option value="event"><?php echo sfConfig::get('app_ui_label_event', __('Event')) ?></option>
            </select>
          </div>
        <?php endif; ?>

        <div class="form-item">
          <label>
            <input name="noindex" type="checkbox"/>
            <?php echo __('Do not index imported items') ?>
          </label>
        </div>

      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
      </ul>
    </section>

  </form>-->

<?php end_slot() ?>