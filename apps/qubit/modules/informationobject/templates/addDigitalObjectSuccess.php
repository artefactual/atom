<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Link digital object') ?></h1>
  <h2><?php echo render_title(new sfIsadPlugin($resource)) ?> </h2>
<?php end_slot() ?>

<?php if (QubitDigitalObject::reachedAppUploadLimit()): ?>

  <div id="upload_limit_reached">
    <div class="messages warning">
      <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your ICA-AtoM system administrator to increase the available disk space.',  array('%1%' => sfConfig::get('app_upload_limit'))) ?>
    </div>

    <ul class="actions links">
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
    </ul>
  </div>

<?php else: ?>

  <?php slot('content') ?>

    <?php echo $form->renderGlobalErrors() ?>

    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'addDigitalObject')), array('id' => 'uploadForm')) ?>

      <?php echo $form->renderHiddenFields() ?>

      <section id="content">

        <?php if (null == $repository || -1 == $repository->uploadLimit || $repository->getDiskUsage(array('units' => 'G')) < floatval($repository->uploadLimit)): ?>
          <fieldset class="collapsible" id="singleFileUpload">

            <legend><?php echo __('Upload a digital object') ?></legend>

            <?php echo $form->file->renderRow() ?>

          </fieldset>
        <?php endif; // Test upload limit ?>

        <fieldset class="collapsible" id="externalFileLink">

          <legend><?php echo __('Link to an external digital object') ?></legend>

          <?php echo $form->url->renderRow() ?>

        </fieldset>

      </section>

      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        </ul>
      </section>

    </form>

  <?php end_slot() ?>

<?php endif; ?>
