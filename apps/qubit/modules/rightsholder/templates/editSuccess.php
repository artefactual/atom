<h1><?php echo __('Edit rights holder') ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'rightsholder', 'action' => 'edit')), array('id' => 'editForm')) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'rightsholder', 'action' => 'add')), array('id' => 'editForm')) ?>
<?php endif; ?>

  <?php echo $form->renderHiddenFields() ?>

  <fieldset class="collapsible" id="identityArea">

    <legend><?php echo __('Identity area') ?></legend>

    <?php echo render_field($form->authorizedFormOfName
      ->label(__('Authorized form of name').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

  </fieldset>

  <fieldset class="collapsible" id="contactArea">

    <legend><?php echo __('Contact area') ?></legend>

    <?php echo get_partial('contactinformation/edit', $contactInformationEditComponent->getVarHolder()->getAll()) ?>
    
  </fieldset>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">

        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'rightsholder'), array('title' => __('Cancel'))) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'rightsholder', 'action' => 'list'), array('title' => __('Cancel'))) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>

      </ul>
    </div>

  </div>

</form>
