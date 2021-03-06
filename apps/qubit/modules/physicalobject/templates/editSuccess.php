<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo render_title($resource); ?>
    <span class="sub"><?php echo __('Edit %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'physicalobject', 'action' => 'edit'])); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'physicalobject', 'action' => 'add'])); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Edit %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?></legend>

        <?php echo render_field($form->name, $resource); ?>

        <?php echo render_field($form->location, $resource); ?>

        <?php echo $form->type->renderRow(); ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <?php if (null !== $next = $form->getValue('next')) { ?>
          <li><?php echo link_to(__('Cancel'), $next, ['class' => 'c-btn']); ?></li>
        <?php } elseif (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'physicalobject'], ['class' => 'c-btn']); ?></li>
        <?php } else { ?>
          <li><?php echo link_to(__('Cancel'), ['module' => 'physicalobject', 'action' => 'browse'], ['class' => 'c-btn']); ?></li>
        <?php } ?>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
