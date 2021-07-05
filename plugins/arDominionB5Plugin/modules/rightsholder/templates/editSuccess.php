<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Edit rights holder'); ?>
    <span class="sub"><?php echo render_title($resource); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'rightsholder', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'rightsholder', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion" id="rightsholder-edit">
      <div class="accordion-item">
        <h2 class="accordion-header" id="identity-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#identity-collapse" aria-expanded="true" aria-controls="identity-collapse">
            <?php echo __('Identity area'); ?>
          </button>
        </h2>
        <div id="identity-collapse" class="accordion-collapse collapse show" aria-labelledby="identity-heading" data-bs-parent="#rightsholder-edit">
          <div class="accordion-body">
            <?php echo render_field($form->authorizedFormOfName
                ->label(__('Authorized form of name').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="contact-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contact-collapse" aria-expanded="false" aria-controls="contact-collapse">
            <?php echo __('Contact area'); ?>
          </button>
        </h2>
        <div id="contact-collapse" class="accordion-collapse collapse" aria-labelledby="contact-heading" data-bs-parent="#rightsholder-edit">
          <div class="accordion-body">
            <?php echo get_partial('contactinformation/edit', $sf_data->getRaw('contactInformationEditComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul class="clearfix links">
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'rightsholder'], ['title' => __('Cancel'), 'class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
        <?php } else { ?>
          <li><?php echo link_to(__('Cancel'), ['module' => 'rightsholder', 'action' => 'list'], ['title' => __('Cancel'), 'class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
        <?php } ?>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
