<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>
  <h1><?php echo __('Reports') ?></h1>
  <h2><?php echo render_title($resource) ?></h2>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <?php echo $form->renderGlobalErrors() ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'reports')), array('class' => 'form-inline')) ?>
<?php end_slot() ?>

<?php if (count($existingReports)): ?>
  <fieldset class="single">
    <div class="fieldset-wrapper">
      <?php echo __('Existing reports:') ?>
      <ul class="job-report-list">
        <?php foreach ($existingReports as $report): ?>
          <li>
            <?php echo link_to($report['type'].' ('.$report['format'].')', $report['path']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </fieldset>
<?php endif; ?>

<fieldset class="single">

  <div class="fieldset-wrapper">

  <?php if ($reportsAvailable): ?>
    <?php echo render_field($form->report->label(__('Select new report to generate:')), $resource) ?>
  <?php else: ?>
    <?php echo __('There are no relevant reports for this item') ?>
  <?php endif; ?>

  </div>

</fieldset>

<?php slot('after-content') ?>
  <section class="actions">
    <ul class="clearfix links">
      <li><input class="form-submit c-btn c-btn-submit" type="submit" value="<?php echo __('Continue') ?>"/></li>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
    </ul>
  </section>
  </form>
<?php end_slot() ?>
