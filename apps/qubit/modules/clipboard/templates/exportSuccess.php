<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource)) { ?>
    <h1 class="multiline">
      <?php echo $title; ?>
      <span class="sub"><?php echo render_title($resource); ?></span>
    </h1>
  <?php } else { ?>
    <h1><?php echo $title; ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'clipboard', 'action' => 'export']), ['id' => 'clipboard-export-form']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <section id="content">

      <div id="export-options" data-export-toggle="tooltip" data-export-title="<?php echo __('Export'); ?>" data-export-alert-message="<?php echo __('Error: You must have at least one %1%Level of description%2% selected or choose %1%Include all descendant levels of description%2% to proceed.', ['%1%' => '<strong>', '%2%' => '</strong>']); ?>">

        <fieldset class="collapsible">

          <legend><?php echo __('Export options'); ?></legend>

          <?php echo $form->type
              ->renderRow(); ?>
          <?php echo $form->format
              ->renderRow(); ?>
          <?php if ($showOptions) { ?>
          <div class="panel panel-default" id="exportOptions">
            <div class="panel-body">
              <?php if (!empty($helpMessages)) { ?>
                <div class="generic-help-box">
                  <a href="#" class="generic-help-icon" aria-expanded="false" aria-label="<?php echo __('Help'); ?>"><i class="fa fa-question-circle pull-right"></i></a>
                </div>
              <?php } ?>
              <?php if (isset($form->includeDescendants)) { ?>
                <?php echo $form->includeDescendants->renderRow(); ?>
              <?php } ?>
              <?php if (isset($form->includeAllLevels)) { ?>
                <?php echo $form->includeAllLevels->renderRow(); ?>
              <?php } ?>
              <?php if (isset($form->levels)) { ?>
                <div id="exportLevels">
                  <?php echo $form->levels->renderLabel(); ?>
                  <?php echo $form->levels->render(); ?>
                  <div class="alert alert-info">
                  <?php echo $form->levels->renderHelp(); ?>
                  </div>
                </div>
              <?php } ?>
              <?php if (isset($form->includeDigitalObjects)) { ?>
                <?php echo $form->includeDigitalObjects->renderRow(); ?>
              <?php } ?>
              <?php if (isset($form->includeDrafts)) { ?>
                <?php echo $form->includeDrafts->renderRow(); ?>
              <?php } ?>
              <?php if (isset($form->includeNonVisibleElements)) { ?>
                <?php echo $form->includeNonVisibleElements->renderRow(); ?>
              <?php } ?>
              <?php if (!empty($helpMessages)) { ?>
                <div class="alert alert-info generic-help animateNicely">
                  <?php foreach ($sf_data->getRaw('helpMessages') as $helpMessage) { ?>
                  <p><?php echo $helpMessage; ?></p>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>
          
        </fieldset>
      </div>
    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" id="exportSubmit" value="<?php echo __('Export'); ?>"/></li>
        <li><?php echo link_to(__('Cancel'), !empty($sf_request->getReferer()) ? $sf_request->getReferer() : ['module' => 'clipboard', 'action' => 'view'], ['class' => 'c-btn']); ?></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
