<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Export storage report'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'physicalobject', 'action' => 'holdingsReportExport'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion" data-export-toggle="tooltip" data-export-title="<?php echo __('Export'); ?>">
      <div class="accordion-item">
        <h2 class="accordion-header" id="export-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#export-collapse" aria-expanded="true" aria-controls="export-collapse">
            <?php echo __('Export options'); ?>
          </button>
        </h2>
        <div id="export-collapse" class="accordion-collapse collapse show" aria-labelledby="export-heading">
          <div class="accordion-body">
            <div class="form-item">
              <div class="panel panel-default" id="exportOptions">
                <div class="panel-body">
                  <label>
                    <input name="includeEmpty" type="checkbox" checked="checked" />
                    <?php echo __('Include unlinked containers'); ?>
                  </label>
                  <label>
                    <input name="includeAccessions" type="checkbox" checked="checked" />
                    <?php echo __('Include containers linked to accessions'); ?>
                  </label>
                  <label>
                    <input name="includeDescriptions" type="checkbox" checked="checked" />
                    <?php echo __('Include containers linked to descriptions'); ?>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), ['module' => 'physicalobject', 'action' => 'browse'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" id="exportSubmit" value="<?php echo __('Export'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
