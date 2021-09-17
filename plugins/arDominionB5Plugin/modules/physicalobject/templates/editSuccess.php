<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo render_title($resource); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo __('Edit %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'physicalobject', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'physicalobject', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="edit-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#edit-collapse" aria-expanded="false" aria-controls="edit-collapse">
            <?php echo __('Edit %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?>
          </button>
        </h2>
        <div id="edit-collapse" class="accordion-collapse collapse" aria-labelledby="edit-heading">
          <div class="accordion-body">
            <?php echo render_field($form->name, $resource); ?>
            <?php echo render_field($form->location, $resource); ?>
            <?php echo render_field($form->type); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (null !== $next = $form->getValue('next')) { ?>
        <li><?php echo link_to(__('Cancel'), $next, ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <?php } elseif (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'physicalobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), ['module' => 'physicalobject', 'action' => 'browse'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <?php } ?>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
