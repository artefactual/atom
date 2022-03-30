<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Edit page'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'staticpage', 'action' => 'edit'])); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'staticpage', 'action' => 'add'])); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="elements-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#elements-collapse" aria-expanded="true" aria-controls="elements-collapse">
            <?php echo __('Elements area'); ?>
          </button>
        </h2>
        <div id="elements-collapse" class="accordion-collapse collapse show" aria-labelledby="elements-heading">
          <div class="accordion-body">
            <?php echo render_field($form->title, $resource); ?>

            <?php if ($resource->isProtected()) { ?>
              <?php echo render_field($form->slug, null, ['disabled' => 'disabled']); ?>
            <?php } else { ?>
              <?php echo render_field($form->slug); ?>
            <?php } ?>

            <?php echo render_field($form->content, $resource); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'staticpage'], ['role' => 'button', 'class' => 'btn atom-btn-outline-light']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), ['module' => 'staticpage', 'action' => 'list'], ['role' => 'button', 'class' => 'btn atom-btn-outline-light']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
