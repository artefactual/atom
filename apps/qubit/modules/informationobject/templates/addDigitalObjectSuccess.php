<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Link %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?>
    <span class="sub"><?php echo render_title(new sfIsadPlugin($resource)) ?></span>
  </h1>
<?php end_slot() ?>

<?php if (QubitDigitalObject::reachedAppUploadLimit()): ?>

  <?php slot('content') ?>

    <section id="content">
    </section>

      <div class="messages warning">
        <?php echo __('The maximum disk space of %1% GB available for uploading digital objects has been reached. Please contact your AtoM system administrator to increase the available disk space.',  array('%1%' => sfConfig::get('app_upload_limit'))) ?>
      </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  <?php end_slot() ?>

<?php else: ?>

  <?php slot('content') ?>

    <?php echo $form->renderGlobalErrors() ?>

    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'addDigitalObject')), array('id' => 'uploadForm')) ?>

      <?php echo $form->renderHiddenFields() ?>

      <section id="content">

        <fieldset class="collapsible" id="singleFileUpload">

          <legend><?php echo __('Upload a %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?></legend>

          <?php if (null == $repository || -1 == $repository->uploadLimit || floatval($repository->getDiskUsage() / pow(10, 9)) < floatval($repository->uploadLimit)): ?>

            <?php echo $form->file->renderRow() ?>

          <?php elseif (0 == $repository->uploadLimit): ?>

            <div class="messages warning">
              <?php echo __('Uploads for <a href="%1%">%2%</a> are disabled', array(
                '%1%' => url_for(array($repository, 'module' => 'repository')),
                '%2%' => $repository->__toString())) ?>
            </div>

          <?php else: ?>

            <div class="messages warning">
              <?php echo __('The upload limit of %1% GB for <a href="%2%">%3%</a> has been reached', array(
                '%1%' => $repository->uploadLimit,
                '%2%' => url_for(array($repository, 'module' => 'repository')),
                '%3%' => $repository->__toString())) ?>
            </div>

          <?php endif; // Test upload limit ?>

        </fieldset>

        <fieldset class="collapsible" id="externalFileLink">

          <legend><?php echo __('Link to an external %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?></legend>

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
