<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Reports'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'reports']), ['class' => 'form-inline']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="reports-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#reports-collapse" aria-expanded="true" aria-controls="reports-collapse">
            <?php echo __('Reports'); ?>
          </button>
        </h2>
        <div id="reports-collapse" class="accordion-collapse collapse show" aria-labelledby="reports-heading">
          <div class="accordion-body">

            <?php if (count($existingReports)) { ?>
              <?php echo __('Existing reports:'); ?>
                <ul class="job-report-list">
                  <?php foreach ($existingReports as $report) { ?>
                    <li>
                      <?php echo link_to($report['type'].' ('.$report['format'].')', $report['path']); ?>
                    </li>
                  <?php } ?>
                </ul>
            <?php } ?>

            <?php if ($reportsAvailable) { ?>
              <?php echo render_field($form->report->label(__('Select new report to generate:')), $resource); ?>
            <?php } else { ?>
              <?php echo __('There are no relevant reports for this item'); ?>
            <?php } ?>

          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li>
        <?php echo link_to(
            __('Cancel'),
            [$resource, 'module' => 'informationobject'],
            ['class' => 'btn atom-btn-outline-light', 'role' => 'button']
        ); ?>
      </li>
      <?php if ($reportsAvailable) { ?>
        <li>
          <input
            class="btn atom-btn-outline-success"
            type="submit"
            value="<?php echo __('Continue'); ?>">
        </li>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
