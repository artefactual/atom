<span class="view-header-label"><?php echo __('View:'); ?></span>

<div class="btn-group">
  <?php echo link_to(' ', ['module' => $module, 'action' => 'browse', 'view' => $cardView] +
                      $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                      ['class' => 'btn fa fa-th-large '.($view === $cardView ? 'active' : ''), 'title' => __('Card view')]); ?>

  <?php echo link_to(' ', ['module' => $module, 'action' => 'browse', 'view' => $tableView] +
                      $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                      ['class' => 'btn fa fa-list '.($view === $tableView ? 'active' : ''), 'title' => __('Table view')]); ?>
</div>
