<h1><?php echo __('Edit digital object') ?></h1>

<h1 class="label"><?php echo render_title(QubitInformationObject::getStandardsBasedInstance($informationObject)) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($resource)): ?>
  <div class="form-item">
    <?php echo get_component('digitalobject', 'show', array('resource' => $resource, 'usageType' => QubitTerm::REFERENCE_ID)) ?>
  </div>
<?php endif; ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'digitalobject', 'action' => 'edit'))) ?>

  <?php echo $form->renderHiddenFields() ?>

  <fieldset class="collapsible">

    <legend><?php echo __('Master') ?></legend>

    <?php echo render_show(__('Filename'), $resource->name) ?>

    <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize)) ?>

    <?php echo $form->mediaType->renderRow() ?>

    <?php if ($showCompoundObjectToggle): ?>
      <?php echo $form->displayAsCompound
        ->label(__('View children as a compound digital object?'))
        ->renderRow() ?>
    <?php endif; ?>

    <?php echo get_partial('right/edit', $rightEditComponent->getVarHolder()->getAll()) ?>

  </fieldset>

  <?php foreach ($representations as $usageId => $representation): ?>

    <fieldset class="collapsible">

      <legend><?php echo __('%1% representation', array('%1%' => QubitTerm::getById($usageId))) ?></legend>

      <?php if (isset($representation)): ?>

        <?php echo get_component('digitalobject', 'editRepresentation', array('resource' => $resource, 'representation' => $representation)) ?>

        <?php $rightComponent = "rightEditComponent_$usageId" ?>
        <?php echo get_partial('right/edit', $$rightComponent->getVarHolder()->getAll() + array('tableId' => $usageId)) ?>

      <?php else: ?>

        <?php echo $form["repFile_$usageId"]
          ->label(__('Select a digital object to upload'))
          ->renderRow() ?>

        <?php if ($resource->canThumbnail()): ?>
          <?php echo $form["generateDerivative_$usageId"]
            ->label('Or auto-generate a new representation from master image')
            ->renderRow() ?>
        <?php endif; ?>

      <?php endif; ?>

    </fieldset>

  <?php endforeach; ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">

        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'digitalobject', 'action' => 'delete'), array('class' => 'delete')) ?></li>
        <?php endif; ?>

        <li><?php echo link_to(__('Cancel'), array($informationObject, 'module' => 'informationobject')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>

      </ul>
    </div>

  </div>

</form>
