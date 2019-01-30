<span class="search-filter">
  <?php echo render_title($object) ?>
  <a href="<?php echo url_for(array('module' => $module, 'action' => $action) + $params) ?>" class="remove-filter"><i class="fa fa-times"></i></a>
</span>
