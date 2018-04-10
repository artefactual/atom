<?php if (QubitAcl::check($resource, 'update') && count($resource->descendants) && count($events)): ?>
  <li class="separator"><h4><?php echo __('Tasks') ?></h4></li>

  <li>
    <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'calculateDates')) ?>">
      <i class="fa fa-calendar"></i>
      <?php echo __('Calculate dates') ?>
    </a>
  </li>
  <li>
    <?php echo __('Last run:') ?> <?php echo $lastRun ?>
  </li>
<?php endif; ?>
