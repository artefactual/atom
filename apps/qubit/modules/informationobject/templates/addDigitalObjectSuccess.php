<h1><?php echo __('Upload digital objects') ?></h1>

<h1 class="label"><?php echo render_title(new sfIsadPlugin($resource)) ?> </h1>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'addDigitalObject')), array('id' => 'uploadForm')) ?>

  <?php echo $form->renderHiddenFields() ?>

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

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
