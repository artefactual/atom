<h1><?php echo __('Manage jobs'); ?></h1>

<div>
  <ul class="nav nav-tabs" id="job-tabs">
    <li<?php if ('all' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('All jobs'), ['filter' => 'all'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
    <li<?php if ('active' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('Active jobs'), ['filter' => 'active'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
  </ul>
</div>

<br />

<div class="tab-content">
  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th style="width: 15%"><?php echo __('Start date'); ?></th>
        <th style="width: 15%"><?php echo __('End date'); ?></th>
        <th style="width: 20%"><?php echo __('Job name'); ?></th>
        <th style="width: 10%"><?php echo __('Job status'); ?></th>
        <th style="width: 30%"><?php echo __('Info'); ?></th>
        <th style="width: 10%"><?php echo __('User'); ?></th>
      </tr>
    </thead>

    <?php $jobs = $pager->getResults(); ?>

    <?php foreach ($jobs as $job) { ?>
      <tr>
        <!-- Creation date -->
        <td><?php echo $job->getCreationDateString(); ?></td>

        <!-- End date -->
        <td><?php echo $job->getCompletionDateString(); ?></td>

        <!-- Job name -->
        <td><?php echo $job; ?></td>

        <!-- Job status -->
        <td>
          <?php if (QubitTerm::JOB_STATUS_COMPLETED_ID == $job->statusId) { ?>
            <i class="fa fa-check-square" id="job-check-color"></i>
          <?php } elseif (QubitTerm::JOB_STATUS_ERROR_ID == $job->statusId) { ?>
            <i class="fa fa-exclamation-triangle" id="job-warning-color"></i>
          <?php } elseif (QubitTerm::JOB_STATUS_IN_PROGRESS_ID == $job->statusId) { ?>
            <i class="fa fa-cogs" id="job-cogs-color"></i>
          <?php } ?>

          <?php echo $job->getStatusString(); ?>

          <?php if ($job->getObjectModule() && $job->getObjectSlug()) { ?>
            <a href="<?php echo url_for(['module' => $job->getObjectModule(),
                'slug' => $job->getObjectSlug(), ]); ?>" class="fa fa-share"></a>

          <?php } ?>
        </td>

        <!-- Job notes -->
        <td>
          <?php foreach ($job->getNotes() as $note) { ?>
            <p><?php echo $note->__toString(); ?></p>
          <?php } ?>
          <?php if (isset($job->downloadPath)) { ?>
            <?php echo link_to(__('Download'), public_path($job->downloadPath), ['class' => 'job-link']); ?>
            (<?php echo hr_filesize(filesize($job->downloadPath)); ?>)
          <?php } ?>

          <?php echo link_to(__('Full report'), ['module' => 'jobs', 'action' => 'report', 'id' => $job->id],
            ['class' => 'job-link']); ?>
        </td>

        <!-- User who created the job -->
        <td>
          <?php echo esc_entities(QubitJob::getUserString($job)); ?>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<!-- User tips -->
<?php if ($this->context->user->isAdministrator() && $jobs->count()) { ?>
  <div class="messages" id="job-info-box">
    <i class="fa fa-info-circle" id="job-info-box-icon"></i>&nbsp;<?php echo __('You may only clear jobs belonging to you.'); ?>
  </div>
<?php } ?>

<?php if (!$jobs->count()) { ?>
  <div class="messages error" id="job-error-box">
    <ul>
      <li><?php echo __('There are no jobs to report on.'); ?></li>
    </ul>
  </div>
<?php } ?>

<!-- Action buttons -->
<section class="actions">
  <ul>
    <li>
      <a class="c-btn" onClick="window.location.reload()"><i class="fa fa-refresh"></i>&nbsp;<?php echo __('Refresh'); ?></a>
    </li>
    <li>
      <?php $autoRefreshIcons = sprintf('c-btn fa %s', $autoRefresh ? 'fa-check-circle-o' : 'fa-circle-o'); ?>
      <?php echo link_to(__(' Auto refresh'), ['module' => 'jobs', 'action' => 'browse',
          'autoRefresh' => !$autoRefresh, ] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
        ['class' => $autoRefreshIcons]); ?>
    </li>
    <?php if ($jobs->count()) { ?>
      <li>
        <?php echo link_to(__('Export history CSV'), ['module' => 'jobs', 'action' => 'export'],
          ['class' => 'c-btn']); ?>
      </li>
      <li>
        <?php echo link_to(__('Clear inactive jobs'), ['module' => 'jobs', 'action' => 'delete'],
          ['class' => 'c-btn c-btn-delete']); ?>
      </li>
    <?php } ?>
  </ul>
</section>

<!-- Refresh after specified interval if auto-refresh enabled -->
<?php if ($autoRefresh) { ?>
  <script>
    setTimeout(function() {
      window.location.reload(1);
    }, <?php echo $refreshInterval; ?>);
  </script>
<?php } ?>
