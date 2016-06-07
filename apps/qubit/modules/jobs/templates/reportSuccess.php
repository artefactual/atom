<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo render_title(__('Job report')) ?></h1>
<?php end_slot() ?>

<section id="report-overview-area">
  <h2><?php echo __('Overview') ?></h2>
  <?php echo render_show(__('Name'), render_value($job->name), array('fieldLabel' => 'jobName')) ?>
  <?php echo render_show(__('Id'), render_value($job->id), array('fieldLabel' => 'jobId')) ?>
  <?php echo render_show(__('Creation date'), render_value($job->getCreationDateString()), array('fieldLabel' => 'jobCreatedAt')) ?>
  <?php echo render_show(__('Completion date'), render_value($job->getCompletionDateString()), array('fieldLabel' => 'jobCompletedAt')) ?>
  <?php echo render_show(__('Status'), render_value(ucfirst($job->getStatusString())), array('fieldLabel' => 'jobStatus')) ?>
  <?php echo render_show(__('Creator'), render_value(QubitJob::getUserString($job)), array('fieldLabel' => 'jobUser')) ?>

  <?php if ($job->getObjectModule() && $job->getObjectSlug()): ?>
    <?php echo render_show(__('Associated item'), link_to(__('Link'), array('module' => $job->getObjectModule(), 'slug' => $job->getObjectSlug()))) ?>
  <?php endif; ?>

  <?php if ($job->downloadPath): ?>
    <?php echo render_show(__('Download link'), render_value($job->downloadPath), array('fieldLabel' => 'jobDownloadLink')) ?>
  <?php endif; ?>
</section>

<section id="log-area">
  <h2><?php echo __('Log') ?></h2>
  <div id="job-log-output">
    <?php echo render_value($job->output) ?>
  </div>
</section>