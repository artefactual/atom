<div class="sidebar-lowering settings-menu">
  <table class="table table-bordered table-hover table-condensed">
    <tbody>
      <?php foreach ($nodes as $node) { ?>
        <?php if ($node['active']) { ?>
          <tr class="info">
        <?php } else { ?>
          <tr>
        <?php } ?>
          <td>
            <?php echo link_to($node['label'], [
                'module' => $node['module'] ?? 'settings',
                'action' => $node['action'],
            ]); ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
