<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource)) { ?>
    <div class="multiline-header d-flex flex-column mb-3">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo $title; ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo render_title($resource); ?>
      </span>
    </div>
  <?php } else { ?>
    <h1><?php echo $title; ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'clipboard', 'action' => 'export']), ['id' => 'clipboard-export-form']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="export-options" data-export-toggle="tooltip" data-export-title="<?php echo __('Export'); ?>" data-export-alert-close="<?php echo __('Close'); ?>" data-export-alert-message="<?php echo __('Error: You must have at least one %1%Level of description%2% selected or choose %1%Include all descendant levels of description%2% to proceed.', ['%1%' => '<strong>', '%2%' => '</strong>']); ?>">
      <div class="accordion mb-3">
        <div class="accordion-item">
          <h2 class="accordion-header" id="export-heading">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#export-collapse" aria-expanded="true" aria-controls="export-collapse">
              <?php echo __('Export options'); ?>
            </button>
          </h2>
          <div id="export-collapse" class="accordion-collapse collapse show" aria-labelledby="export-heading">
            <div class="accordion-body">
              <?php echo render_field($form->type); ?>
              <?php echo render_field($form->format); ?>
              <?php if ($showOptions) { ?>
                <div id="exportOptions">
                  <?php if (!empty($helpMessages)) { ?>
                    <div class="generic-help-box">
                      <a href="#" class="generic-help-icon" aria-expanded="false" aria-label="<?php echo __('Help'); ?>">
                        <i class="fa fa-question-circle float-end pt-1" aria-hidden="true"></i>
                      </a>
                    </div>
                  <?php } ?>
                  <?php if (isset($form->includeDrafts)) { ?>
                    <?php echo render_field($form->includeDrafts); ?>
                  <?php } ?>
                  <?php if (isset($form->includeDescendants)) { ?>
                    <?php echo render_field($form->includeDescendants); ?>
                  <?php } ?>
                  <?php if (isset($form->includeAllLevels)) { ?>
                    <div>
                      <?php echo render_field($form->includeAllLevels); ?>
                    </div>
                  <?php } ?>
                  <?php if (isset($form->levels)) { ?>
                    <div id="exportLevels">
                      <?php echo render_field($form->levels); ?>
                    </div>
                  <?php } ?>
                  <?php if (isset($form->includeDigitalObjects)) { ?>
                    <?php echo render_field($form->includeDigitalObjects); ?>
                  <?php } ?>
                  <?php if (!empty($helpMessages)) { ?>
                    <div class="alert alert-info generic-help animateNicely hidden">
                      <?php foreach ($sf_data->getRaw('helpMessages') as $helpMessage) { ?>
                        <p><?php echo $helpMessage; ?></p>
                      <?php } ?>
                    </div>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><input class="btn atom-btn-outline-success" type="submit" id="exportSubmit" value="<?php echo __('Export'); ?>"></li>
      <li><?php echo link_to(__('Cancel'), !empty($sf_request->getReferer()) ? $sf_request->getReferer() : ['module' => 'clipboard', 'action' => 'view'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    </ul>

  </form>

<?php end_slot(); ?>
