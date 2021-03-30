<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Export storage report'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'physicalobject', 'action' => 'holdingsReportExport'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <section id="content">
      <div id="export-options" data-export-toggle="tooltip" data-export-title="<?php echo __('Export'); ?>">
      <fieldset class="collapsible">

        <legend><?php echo __('Export options'); ?></legend>

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
      </fieldset>
      </div>
    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" id="exportSubmit" value="<?php echo __('Export'); ?>"/></li>
        <li><?php echo link_to(__('Cancel'), ['module' => 'physicalobject', 'action' => 'browse'], ['class' => 'c-btn']); ?></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
