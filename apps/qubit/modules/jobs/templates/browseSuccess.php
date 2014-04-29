Hello!

<table class="table table-bordered sticky-enabled sticky-table">
  <thead class="tableheader-processed">
    <tr>
      <th>Job ID</th>
      <th>Job name</th>
      <th>Job status</th>
    </tr>
  </thead>

    <?php $jobs = QubitJob::getAll(); ?>
    <?php foreach ($jobs as $job): ?>
      <tr>
        <td><?php echo $job->id; ?></td>
        <td><?php echo $job->name; ?></td>
        <td><?php echo $job->statusId; ?></td>
      </tr>
    <?php endforeach; ?>
</table>