<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo render_title(__('Job report')) ?></h1>
<?php end_slot() ?>

<section id="report-overview-area">
  <h2><?php echo __('Overview') ?></h2>

  <div class="job-report-field">
    <div><?php echo __('Name') ?></div>
    <div><?php echo render_value($job->name) ?></div>
  </div>

  <div class="job-report-field">
    <div><?php echo __('Id') ?></div>
    <div><?php echo render_value($job->id) ?></div>
  </div>

  <div class="job-report-field">
    <div><?php echo __('Creation date') ?></div>
    <div><?php echo render_value($job->getCreationDateString()) ?></div>
  </div>

  <div class="job-report-field">
    <div><?php echo __('Completion date') ?></div>
    <div><?php echo render_value($job->getCompletionDateString()) ?></div>
  </div>

  <div class="job-report-field">
    <div><?php echo __('Status') ?></div>
    <div>
      <?php echo render_value($job->getStatusString()) ?>
      <?php if ($job->statusId == QubitTerm::JOB_STATUS_COMPLETED_ID): ?>
        <i class="fa fa-check-square" id="job-check-color"></i>
      <?php elseif ($job->statusId == QubitTerm::JOB_STATUS_ERROR_ID): ?>
        <i class="fa fa-exclamation-triangle" id="job-warning-color"></i>
      <?php elseif ($job->statusId == QubitTerm::JOB_STATUS_IN_PROGRESS_ID): ?>
        <i class="fa fa-cogs" id="job-cogs-color"></i>
      <?php endif; ?>
    </div>
  </div>

  <div class="job-report-field">
    <div><?php echo __('Creator') ?></div>
    <div><?php echo render_value(QubitJob::getUserString($job)) ?></div>
  </div>

  <?php if ($job->getObjectModule() && $job->getObjectSlug()): ?>
    <div class="job-report-field">
      <div><?php echo __('Associated record') ?></div>
      <div><?php echo link_to(__('Link'), array('module' => $job->getObjectModule(), 'slug' => $job->getObjectSlug())) ?></div>
    </div>
  <?php endif; ?>

  <?php if ($job->downloadPath): ?>
    <div class="job-report-field">
      <div><?php echo __('Download path') ?></div>
      <div><?php echo link_to(__('Link'), public_path($job->downloadPath)) ?></div>
    </div>
  <?php endif; ?>
</section>

<section id="log-area">
  <h2><?php echo __('Log') ?></h2>
  <div>
    <?php $output = trim($job->output) ?>
    <?php if (0 < strlen($output)): ?>
      <pre id="job-log-output"><?php echo render_value($output) ?></pre>
    <?php else: ?>
      <p id="job-log-output-empty"><?php echo __('Empty') ?></p>
    <?php endif; ?>
  </div>
</section>

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <li>
        <?php echo link_to(__('Return to jobs management page'), array('module' => 'jobs', 'action' => 'browse'), array('class' => 'c-btn')) ?>
      </li>
    </ul>
  </section>
<?php end_slot() ?>
