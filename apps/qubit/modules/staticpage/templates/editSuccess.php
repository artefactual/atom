<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Edit page') ?>
    <span class="sub"><?php echo render_title($resource) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'staticpage', 'action' => 'edit'))) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'staticpage', 'action' => 'add'))) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <fieldset class="collapsible" id="elementsArea">

        <legend><?php echo __('Elements area') ?></legend>

        <?php echo render_field($form->title, $resource) ?>

        <?php if ($resource->isProtected()): ?>
          <?php echo $form->slug->renderRow(array('class' => 'readOnly', 'disabled' => 'disabled')) ?>
        <?php else: ?>
          <?php echo $form->slug->renderRow() ?>
        <?php endif; ?>

        <?php echo render_field($form->content, $resource, array('class' => 'resizable')) ?>

      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'staticpage'), array('title' => __('Cancel'), 'class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'staticpage', 'action' => 'list'), array('title' => __('Cancel'), 'class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
