<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <?php if (isset($resource)): ?>
    <h1 class="multiline">
      <?php echo $title ?>
      <span class="sub"><?php echo render_title($resource) ?></span>
    </h1>
  <?php else: ?>
    <h1><?php echo $title ?></h1>
  <?php endif; ?>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php if ($sf_user->hasFlash('error')): ?>
    <div class="messages error">
      <h3><?php echo __('Error encountered') ?></h3>
      <div><?php echo $sf_user->getFlash('error', ESC_RAW) ?></div>
    </div>
  <?php endif; ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'object', 'action' => 'export'))) ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">
      <div id="export-options" data-export-toggle="tooltip" data-export-title="<?php echo __('Export') ?>" data-export-alert-message="<?php echo __('Error: You must have at least one %1%Level of description%2% selected or choose %1%Include all levels of description%2% to proceed.', array('%1%' => '<strong>', '%2%' => '</strong>')) ?>">
      <fieldset class="collapsible">

        <legend><?php echo __('Export options') ?></legend>
        <div class="form-item">
          <?php echo $form->objectType
            ->label(__('Type'))
            ->renderRow() ?>
        </div>

        <div class="form-item">
          <?php echo $form->format
            ->label(__('Format'))
            ->renderRow() ?>
        </div>

        <div class="form-item">
          <div class="panel panel-default" id="exportOptions">
            <div class="panel-body">
              <div class="generic-help-box">
                <label class="generic-help-type">
                  <input name="includeDescendants" type="checkbox"/>
                  <?php echo __('Include descendants') ?>
                </label>
                <a href="#" class="generic-help-icon" aria-expanded="false"><i class="fa fa-question-circle pull-right"></i></a>
              </div>

              <?php if ($sf_user->isAuthenticated()): ?>
                <label>
                  <input name="includeDrafts" type="checkbox"/>
                  <?php echo __('Include draft records') ?>
                </label>
              <?php endif; ?>
              <label>
                <input name="includeAllLevels" type="checkbox"/>
                <?php echo __('Include all levels of description') ?>
              </label>
              <div class="hidden" id="exportLevels">
                <?php echo $form->levels
                  ->label(__('Levels of description'))
                  ->help(__('Select the levels of description to be included in the export. If no levels are selected, the export will fail. You can use the control (Mac ⌘) and/or shift keys to multi-select values from the Levels of description menu. Descriptions that are descendants of levels not included in the export will also be excluded.'))
                  ->renderRow() ?>
              </div>
            </div>

            <div class="alert alert-info generic-help animateNicely">
              <p><?php echo __('Choosing "Include descendants" will include all lower-level records beneath those currently on the clipboard in the export.') ?></p>
              <?php if ($sf_user->isAuthenticated()): ?>
                <p><?php echo __('Choosing "Include draft records" will include those marked with a Draft publication status in the export. Note: if you do NOT choose this option, any descendants of a draft record will also be excluded, even if they are published.') ?></p>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </fieldset>
      </div>
    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" id="exportSubmit" value="<?php echo __('Export') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), !empty($sf_request->getReferer()) ? $sf_request->getReferer() : array('module' => 'user', 'action' => 'clipboard'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
