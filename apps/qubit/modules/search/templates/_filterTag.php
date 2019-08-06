<span class="search-filter">
  <?php if (!empty($label)): ?>
    <?php echo $label ?>
  <?php else: ?>
    <?php echo render_title($object) ?>
  <?php endif; ?>
  <a href="<?php echo url_for(array('module' => $module, 'action' => $action) + $sf_data->getRaw('getParams')) ?>" class="remove-filter"><i class="fa fa-times"></i></a>
</span>
