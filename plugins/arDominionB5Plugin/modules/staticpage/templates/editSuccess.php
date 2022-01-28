<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Edit page'); ?>
    <span class="sub"><?php echo render_title($resource); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'staticpage', 'action' => 'edit'])); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'staticpage', 'action' => 'add'])); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
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
              <?php echo $form->slug->renderRow(['class' => 'readOnly', 'disabled' => 'disabled']); ?>
            <?php } else { ?>
              <?php echo $form->slug->renderRow(); ?>
            <?php } ?>

            <?php echo render_field($form->content, $resource, ['class' => 'resizable']); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'staticpage'], ['title' => __('Cancel'), 'class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
        <?php } else { ?>
          <li><?php echo link_to(__('Cancel'), ['module' => 'staticpage', 'action' => 'list'], ['title' => __('Cancel'), 'class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
        <?php } ?>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
