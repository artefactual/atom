<h1>
  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo __('Edit term') ?>
  <?php else: ?>
    <?php echo __('Add new term') ?>
  <?php endif; ?>
</h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'term', 'action' => 'edit'))) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'term', 'action' => 'add'))) ?>
<?php endif; ?>

  <?php echo $form->renderHiddenFields() ?>

  <div class="form-item">
    <?php echo $form->taxonomy->renderLabel() ?>
    <?php echo $form->taxonomy->renderError() ?>
    <?php echo $form->taxonomy->render(array('class' => 'form-autocomplete')) ?>
    <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'taxonomy', 'action' => 'autocomplete')) ?>"/>
  </div>

  <?php if ($resource->isProtected()): ?>
    <?php echo $form->name->renderRow(array('class' => 'readOnly', 'disabled' => 'disabled')) ?>
  <?php else: ?>
    <?php echo render_field($form->name, $resource) ?>
  <?php endif; ?>

  <?php echo $form->useFor->renderRow() ?>

  <?php echo render_field($form->code, $resource) ?>

  <?php echo $form->scopeNote
    ->label(__('Scope note(s)'))
    ->renderRow() ?>

  <?php echo $form->sourceNote
    ->label(__('Source note(s)'))
    ->renderRow() ?>

  <?php echo $form->displayNote
    ->label(__('Display note(s)'))
    ->renderRow() ?>

  <fieldset class="collapsible collapsed">

    <legend><?php echo __('Relationships') ?></legend>

    <?php if (null !== $form->taxonomy->getValue()): ?>

      <div class="form-item">
        <?php echo $form->parent
          ->label(__('Broad term'))
          ->renderLabel() ?>
        <?php echo $form->parent->render(array('class' => 'form-autocomplete')) ?>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $form->taxonomy->getValue())) ?>"/>
      </div>

      <div class="form-item">
        <?php echo $form->relatedTerms
          ->label(__('Related term(s)'))
          ->renderLabel() ?>
        <?php echo $form->relatedTerms->render(array('class' => 'form-autocomplete')) ?>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $form->taxonomy->getValue())) ?>"/>
      </div>

    <?php endif; ?>

    <?php echo $form->narrowTerms
      ->label(__('Add new narrow terms'))
      ->renderRow() ?>

  </fieldset>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'term')) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>

          <?php if (isset($resource->taxonomy)): ?>
            <li><?php echo link_to(__('Cancel'), array($resource->taxonomy, 'module' => 'taxonomy')) ?></li>
          <?php else: ?>
            <li><?php echo link_to(__('Cancel'), array('module' => 'taxonomy', 'action' => 'list')) ?></li>
          <?php endif; ?>

          <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>

        <?php endif; ?>
      </ul>
    </div>

  </div>

</form>
