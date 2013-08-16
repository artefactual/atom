<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Group %1%', array('%1%' => render_title($group))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->id)): ?>
    <?php echo $form->renderFormTag(url_for(array($group, 'module' => 'aclGroup', 'action' => 'edit')), array('id' => 'editForm')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'aclGroup', 'action' => 'add')), array('id' => 'editForm')) ?>
  <?php endif; ?>

    <section id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Main area') ?></legend>

        <?php echo render_field($form->name, $group) ?>

        <?php echo render_field($form->description, $group, array('class' => 'resizable')) ?>

        <?php echo $form->translate->renderRow(array('class' => 'asdf')) ?>

      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->id)): ?>
          <li><?php echo link_to(__('Cancel'), array($group, 'module' => 'aclGroup'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'aclGroup', 'action' => 'list'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
