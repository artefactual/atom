<h1>Manage jobs</h1>

<table class="table table-bordered sticky-enabled sticky-table" style="margin-top:20px;">
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

  <!-- Allow administrators see all jobs, not just their own -->
  <?php if ($this->context->user->isAdministrator()): ?>
    <?php $jobs = QubitJob::getAll(); ?>
  <?php else: ?>
    $jobs = QubitJob::getJobsByUser($user);
  <?php endif; ?>

  <?php if ($jobs->count() === 0): ?>
    <div class="messages error" style="margin-top:20px;">
      <ul>
          <li>There are no jobs to report on.</li>
      </ul>
    </div>
  <?php endif; ?>

  <?php foreach ($jobs as $job): ?>
    <tr>
      <!-- Creation date -->
      <td><?php echo $job->getCreationDateString(); ?></td>

      <!-- End date -->
      <td><?php echo $job->getCompletionDateString(); ?></td>

      <!-- Job name -->
      <td><?php echo $job; ?></td>

      <!-- Job status -->
      <td>
        <?php if ($job->statusId == QubitTerm::JOB_STATUS_COMPLETED_ID): ?>
          <i class="icon-check-sign" style="color:#00CC00"></i>
        <?php elseif ($job->statusId == QubitTerm::JOB_STATUS_ERROR_ID): ?>
          <i class="icon-warning-sign" style="color:#CC0000"></i>
        <?php elseif ($job->statusId == QubitTerm::JOB_STATUS_IN_PROGRESS_ID): ?>
          <i class="icon-cogs" style="color:#666666"></i>
        <?php endif; ?>

        <?php echo ucfirst($job->getStatusString()); ?>
      </td>

      <!-- Job notes -->
      <td>
        <?php foreach ($job->getNotes() as $note): ?>
          <p><?php echo $note->__toString(); ?></p>
        <?php endforeach; ?>
      </td>

      <!-- User who created the job -->
      <td>
        <?php if (isset($job->userId)): ?>
          <?php $user = QubitUser::getById($job->userId); ?>
          <?php echo $user ? $user->__toString() : 'Deleted user'; ?>
        <?php else: ?>
          Command line
        <?php endif; ?>
    </tr>
  <?php endforeach; ?>
</table>

<?php if ($this->context->user->isAdministrator()): ?>
  <div class="messages" style="background-color:#FFFFCC">
    <i class="icon-info-sign" style="color:#336699"></i>&nbsp;You may only clear jobs belonging to you.
  </div>
<?php endif; ?>
<section class="actions">
  <ul>
    <li>
      <?php echo link_to(__('Export history CSV'), array('module' => 'jobs', 'action' => 'export'),
        array('class' => 'c-btn')) ?>
    </li>
    <li>
      <?php echo link_to(__('Clear inactive jobs'), array('module' => 'jobs', 'action' => 'delete'),
        array('class' => 'c-btn c-btn-delete')) ?>
    </li>
  </ul>
</section>