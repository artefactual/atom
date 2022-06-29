<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Edit accession record'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'deaccession', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'deaccession', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="deaccession-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#deaccession-collapse" aria-expanded="false" aria-controls="deaccession-collapse">
            <?php echo __('Deaccession area'); ?>
          </button>
        </h2>
        <div id="deaccession-collapse" class="accordion-collapse collapse" aria-labelledby="deaccession-heading">
          <div class="accordion-body">
            <?php echo render_field($form->identifier
                ->label(__('Deaccession number').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo render_field($form->scope
                ->help(__('Identify if the whole accession is being deaccessioned or if only a part of the accession is being deaccessioned.'))
                ->label(__('Scope').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo render_field(
                $form->date->help(__('Date of deaccession'))->label(
                    __('Date')
                    .' <span class="form-required" title="'
                    .__('This is a mandatory element.')
                    .'">*</span>'
                ),
                null,
                ['type' => 'date']
            ); ?>

            <?php echo render_field($form->description
                ->help(__('Identify what materials are being deaccessioned.'))
                ->label(__('Description').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->extent
                ->help(__('The number of units as a whole number and the measurement of the records to be deaccessioned.')), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->reason
                ->help(__('Provide a reason why the records are being deaccessioned.')), $resource, ['class' => 'resizable']); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (isset($resource->id)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'deaccession'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), [$resource->accession, 'module' => 'accession'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
