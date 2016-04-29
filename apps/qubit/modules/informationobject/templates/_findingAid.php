<li class="separator"><h4><?php echo __('Finding aid') ?></h4></li>

<?php if ($sf_user->isAuthenticated()): ?>
  <li>
    <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'findingAid')) ?>">
      <i class="fa fa-cogs"></i>
      <?php echo __('Generate') ?>
    </a>
  </li>
<?php endif; ?>

<?php if ($status === QubitTerm::JOB_STATUS_COMPLETED_ID): ?>
  <li>
    <a href="<?php echo public_path($path) ?>" target="_blank">
      <i class="fa fa-upload"></i>
      <?php echo __('Download'); ?>
    </a>
  </li>
<?php else: ?>
  <li>
    <a>
      <i class="fa fa-info-circle"></i>
      <?php echo __('Status: ') . ucfirst($statusString); ?>
    </a>
  </li>
<?php endif; ?>
