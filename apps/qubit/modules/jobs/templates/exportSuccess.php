<?php $sfUser = sfContext::getInstance()->user; ?>
<?php if (!$sfUser || !$sfUser->isAuthenticated()): ?>
  <?php QubitAcl::forwardUnauthorized(); ?>
<?php endif; ?>

<!-- Allow administrators see all jobs, not just their own -->
<?php if ($sfUser->isAdministrator()): ?>
  <?php $jobs = QubitJob::getAll(); ?>
<?php else: ?>
  $jobs = QubitJob::getJobsByUser($sfUser);
<?php endif; ?>

<?php echo QubitJob::getCSVString($jobs); ?>