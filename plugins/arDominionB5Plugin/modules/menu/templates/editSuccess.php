<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <?php if (isset($sf_request->id)) { ?>
    <div class="multiline-header d-flex flex-column mb-3">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo __('Edit menu'); ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo $menu->getName(['sourceCulture' => true]); ?>
      </span>
    </div>
  <?php } else { ?>
    <h1><?php echo __('Add new menu'); ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->id)) { ?>
    <?php echo $form->renderFormTag(url_for([$menu, 'module' => 'menu', 'action' => 'edit'])); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'menu', 'action' => 'add'])); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="edit-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#edit-collapse" aria-expanded="true" aria-controls="edit-collapse">
            <?php echo __('Main area'); ?>
          </button>
        </h2>
        <div id="edit-collapse" class="accordion-collapse collapse show" aria-labelledby="edit-heading">
          <div class="accordion-body">
            <?php if (!$menu->isProtected()) { ?>
              <div class="form-item">
                <?php echo render_field($form->name
                    ->help(__('Provide an internal menu name.  This is not visible to users.'))
                    ->label(__('Name')), null); ?>
              </div>
            <?php } ?>

            <?php echo render_field($form->label
                ->help(__('Provide a menu label for users.  For menu items that are not visible (i.e. are organizational only) this should be left blank.'))
                ->label(__('Label')), $menu); ?>

            <?php echo render_field($form->parentId
                ->label(__('Parent')), null); ?>

            <?php echo render_field($form->path
                ->help(__('Provide a link to an external website or an internal, symfony path (module/action).'))
                ->label(__('Path')), null); ?>

            <?php echo render_field($form->description
                ->help(__('Provide a brief description of the menu and it\'s purpose.'))
                ->label(__('Description')), $menu); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), ['module' => 'menu', 'action' => 'list'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <?php if (!$menu->isProtected() && isset($menu->id)) { ?>
        <li><?php echo link_to(__('Delete'), [$menu, 'module' => 'menu', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger', 'role' => 'button']); ?></li>
      <?php } ?>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
