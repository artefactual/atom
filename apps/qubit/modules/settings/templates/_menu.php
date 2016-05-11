<div class="sidebar-lowering settings-menu">
  <table class="table table-bordered table-hover table-condensed">
    <tbody>
      <?php foreach ($nodes as $node): ?>
        <?php if ($node['active']): ?>
          <tr class="info">
        <?php else: ?>
          <tr>
        <?php endif; ?>
          <td>
            <?php echo link_to($node['label'], array('module' => 'settings', 'action' => $node['action'])) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
