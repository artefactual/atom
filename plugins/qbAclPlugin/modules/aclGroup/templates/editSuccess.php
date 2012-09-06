<h1>
  <?php if (isset($sf_request->id)): ?>
    <?php echo __('Edit group') ?>
  <?php else: ?>
    <?php echo __('Add new group') ?>
  <?php endif; ?>
</h1>

<?php if (isset($sf_request->id)): ?>
  <h1 class="label"><?php echo $group ?></h1>
<?php endif; ?>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($sf_request->id)): ?>
  <?php echo $form->renderFormTag(url_for(array($group, 'module' => 'aclGroup', 'action' => 'edit')), array('id' => 'editForm')) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'aclGroup', 'action' => 'add')), array('id' => 'editForm')) ?>
<?php endif; ?>

  <?php echo render_field($form->name, $group) ?>

  <?php echo render_field($form->description, $group, array('class' => 'resizable')) ?>

  <?php echo $form->translate->renderRow() ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <?php if (isset($sf_request->id)): ?>
          <li><?php echo link_to(__('Cancel'), array($group, 'module' => 'aclGroup')) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'aclGroup', 'action' => 'list')) ?></li>
          <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>
      </ul>
    </div>

  </div>

</form>
