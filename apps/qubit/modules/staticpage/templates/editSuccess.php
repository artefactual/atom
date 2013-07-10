<h1><?php echo __('Edit page') ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'staticpage', 'action' => 'edit'))) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'staticpage', 'action' => 'add'))) ?>
<?php endif; ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php echo render_field($form->title, $resource) ?>

  <?php if ($resource->isProtected()): ?>
    <?php echo $form->slug->renderRow(array('class' => 'readOnly', 'disabled' => 'disabled')) ?>
  <?php else: ?>
    <?php echo $form->slug->renderRow() ?>
  <?php endif; ?>

  <?php echo render_field($form->content, $resource, array('class' => 'resizable')) ?>

  <section class="actions">

    <ul>
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'staticpage')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      <?php else: ?>
        <li><?php echo link_to(__('Cancel'), array('module' => 'staticpage', 'action' => 'list')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
      <?php endif; ?>
    </ul>

  </section>

</form>
