<?php if (QubitAcl::check($resource, 'update')) { ?>
  <li class="separator"><h4><?php echo __('Tasks'); ?></h4></li>

  <li>
    <a href="<?php echo url_for([$resource, 'module' => 'informationobject', 'action' => 'calculateDates']); ?>" title="<?php echo __("Click 'Calculate dates' to recalculate the start and end dates of a parent-level description. A job runs in the background, accounting for the earliest and most recent dates across all the child descriptions. The results display in the Start and End fields of the edit page."); ?>">
      <i class="fa fa-calendar"></i>
      <?php echo __('Calculate dates'); ?>
    </a>
  </li>
  <li>
    <?php echo __('Last run:'); ?> <?php echo $lastRun; ?>
  </li>
<?php } ?>
