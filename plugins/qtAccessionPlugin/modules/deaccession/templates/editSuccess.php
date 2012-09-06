<h1><?php echo __('Edit deaccession record') ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'deaccession', 'action' => 'edit')), array('id' => 'editForm')) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'deaccession', 'action' => 'add')), array('id' => 'editForm')) ?>
<?php endif; ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php echo $form->identifier
    ->label(__('Deaccession number').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
    ->renderRow() ?>

  <?php echo $form->scope
    ->help(__('Identify if the whole accession is being deaccessioned or if only a part of the accession is being deaccessioned.'))
    ->label(__('Scope').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
    ->renderRow() ?>

  <?php echo $form->date
    ->help(__('Date of deaccession'))
    ->label(__('Date').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
    ->renderRow(array('class' => 'date-widget', 'icon' => image_path('calendar.png'))) ?>

  <?php echo render_field($form->description
    ->help(__('Identify what materials are being deaccessioned.'))
    ->label(__('Description').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, array('class' => 'resizable')) ?>

  <?php echo render_field($form->extent
    ->help(__('The number of units as a whole number and the measurement of the records to be deaccessioned.')), $resource, array('class' => 'resizable')) ?>

  <?php echo render_field($form->reason
    ->help(__('Provide a reason why the records are being deaccessioned.')), $resource, array('class' => 'resizable')) ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">

        <?php if (isset($resource->id)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'deaccession')) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array($resource->accession, 'module' => 'accession')) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>

      </ul>
    </div>

  </div>

</form>
