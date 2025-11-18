<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('%1 - report criteria', ['%1' => $type]); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'boxLabel', 'type' => $type]), ['class' => 'form-inline']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="report-criteria-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#report-criteria-collapse" aria-expanded="true" aria-controls="report-criteria-collapse">
            <?php echo __('Report criteria'); ?>
          </button>
        </h2>
        <div id="report-criteria-collapse" class="accordion-collapse collapse show" aria-labelledby="report-criteria-heading">
          <div class="accordion-body">

            <?php echo render_field($form->format->label(__('Format')), $resource); ?>

          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Continue'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
