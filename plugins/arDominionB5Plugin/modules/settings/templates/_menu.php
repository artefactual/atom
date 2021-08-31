<nav id="settings-menu" class="list-group mb-3 sticky-top">
  <?php foreach ($nodes as $node) { ?>
    <?php echo link_to(
      $node['label'],
      [
          'module' => $node['module'] ?? 'settings',
          'action' => $node['action'],
      ],
      [
          'class' => 'list-group-item list-group-item-action'
                     .($node['active'] ? ' active' : ''),
      ]
    ); ?>
  <?php } ?>
</nav>
