<h1>Manage jobs</h1>
<div>
  <ul class="nav nav-tabs" style="margin:0px;padding:0px">
    <li<?php if ('all' === $sf_request->filter): ?> class="active"<?php endif; ?>><?php echo link_to(__('All jobs'), array('filter' => 'all') + $sf_request->getParameterHolder()->getAll()) ?></li>
    <li<?php if ('active' === $sf_request->filter): ?> class="active"<?php endif; ?>><?php echo link_to(__('Active jobs'), array('filter' => 'active') + $sf_request->getParameterHolder()->getAll()) ?></li>
  </ul>
</div>
<div class="tab-content">
  <table class="table table-bordered sticky-enabled sticky-table">
    <thead class="tableheader-processed">
      <tr>
        <th width="15%">Start date</th>
        <th width="15%">End date</th>
        <th width="20%">Job name</th>
        <th width="10%">Job status</th>
        <th width="30%">Info</th>
        <th width="15%">User</th>
      </tr>
    </thead>

    <?php $jobs = $pager->getResults() ?>
    <?php $autoRefresh = $sf_request->autoRefresh; ?>

    <?php foreach ($jobs as $job): ?>
      <tr>
        <!-- Creation date -->
        <td><?php echo $job->getCreationDateString() ?></td>

        <!-- End date -->
        <td><?php echo $job->getCompletionDateString() ?></td>

        <!-- Job name -->
        <td><?php echo $job ?></td>

        <!-- Job status -->
        <td>
          <?php if ($job->statusId == QubitTerm::JOB_STATUS_COMPLETED_ID): ?>
            <i class="icon-check-sign" style="color:#00CC00"></i>
          <?php elseif ($job->statusId == QubitTerm::JOB_STATUS_ERROR_ID): ?>
            <i class="icon-warning-sign" style="color:#CC0000"></i>
          <?php elseif ($job->statusId == QubitTerm::JOB_STATUS_IN_PROGRESS_ID): ?>
            <i class="icon-cogs" style="color:#666666"></i>
          <?php endif; ?>

          <?php echo ucfirst($job->getStatusString()) ?>
        </td>

        <!-- Job notes -->
        <td>
          <?php foreach ($job->getNotes() as $note): ?>
            <p><?php echo $note->__toString(); ?></p>
          <?php endforeach; ?>
        </td>

        <!-- User who created the job -->
        <td>
          <?php echo QubitJob::getUserString($job); ?>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<!-- User tips -->
<?php if ($this->context->user->isAdministrator() && $jobs->count()): ?>
  <div class="messages" style="background-color:#FFFFCC">
    <i class="icon-info-sign" style="color:#336699"></i>&nbsp;You may only clear jobs belonging to you.
  </div>
<?php endif; ?>

<?php if (!$jobs->count()): ?>
  <div class="messages error" style="margin-top:20px;">
    <ul>
        <li>There are no jobs to report on.</li>
    </ul>
  </div>
<?php endif; ?>

<!-- Action buttons -->
<section class="actions">
  <ul>
    <li>
      <a class="c-btn" onClick="window.location.reload()"><i class="icon-refresh icon-large" ></i> Refresh</a>
    </li>
    <li>
        <?php $autoRefreshIcons = sprintf("c-btn %s icon-large", $autoRefresh ? 'icon-ok-circle' : 'icon-circle-blank') ?>
        <?php echo link_to(__(' Auto refresh'), array('module' => 'jobs', 'action' => 'browse',
         'autoRefresh' => !$autoRefresh) + $sf_request->getParameterHolder()->getAll(),
         array('class' => $autoRefreshIcons)) ?>
    </li>
    <?php if ($jobs->count()): ?>
      <li>
        <?php echo link_to(__('Export history CSV'), array('module' => 'jobs', 'action' => 'export'),
          array('class' => 'c-btn')) ?>
      </li>
      <li>
        <?php echo link_to(__('Clear inactive jobs'), array('module' => 'jobs', 'action' => 'delete'),
          array('class' => 'c-btn c-btn-delete')) ?>
      </li>
    <?php endif; ?>
  </ul>
</section>

<!-- Refresh after specified interval if auto-refresh enabled -->
<?php if ($autoRefresh): ?>
  <script>
    setTimeout(function() {
      window.location.reload(1);
    }, <?php echo $refreshInterval ?>);
  </script>
<?php endif; ?>
