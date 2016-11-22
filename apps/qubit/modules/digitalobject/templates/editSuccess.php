<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Edit %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))) ?>
    <span class="sub"><?php echo render_title(QubitInformationObject::getStandardsBasedInstance($informationObject)) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'digitalobject', 'action' => 'edit'))) ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <?php if (isset($resource)): ?>
        <div class="form-item">
          <?php echo get_component('digitalobject', 'show', array('resource' => $resource, 'usageType' => QubitTerm::REFERENCE_ID)) ?>
        </div>
      <?php endif; ?>

      <fieldset class="collapsible">

        <legend><?php echo __('Master') ?></legend>

        <?php echo render_show(__('Filename'), $resource->name) ?>

        <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize)) ?>

        <?php echo $form->mediaType->renderRow() ?>

        <?php if ($showCompoundObjectToggle): ?>
          <?php echo $form->displayAsCompound
            ->label(__('View children as a compound %1%?', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))))
            ->renderRow() ?>
        <?php endif; ?>

        <?php echo $form->latitude->label('Latitude')->renderRow(); ?>

        <?php echo $form->longitude->label('Longitude')->renderRow(); ?>

      </fieldset>

      <?php foreach ($representations as $usageId => $representation): ?>

        <fieldset class="collapsible">

          <legend><?php echo __('%1% representation', array('%1%' => QubitTerm::getById($usageId))) ?></legend>

          <?php if (isset($representation)): ?>

            <?php echo get_component('digitalobject', 'editRepresentation', array('resource' => $resource, 'representation' => $representation)) ?>

          <?php else: ?>

            <?php echo $form["repFile_$usageId"]
              ->label(__('Select a %1% to upload', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))))
              ->renderRow() ?>

            <?php if ($resource->canThumbnail()): ?>
              <?php echo $form["generateDerivative_$usageId"]
                ->label('Or auto-generate a new representation from master image')
                ->renderRow() ?>
            <?php endif; ?>

          <?php endif; ?>

        </fieldset>

      <?php endforeach; ?>

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'digitalobject', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
        <?php endif; ?>
        <li><?php echo link_to(__('Cancel'), array($informationObject, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
