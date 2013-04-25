<?php if (isset($hasPrevSiblings) && $hasPrevSiblings): ?>
  <?php echo render_treeview_node(
    null,
    array('more' => true),
    array('browser' => $browser, 'xhr-location' => url_for(array($items[0], 'module' => 'term', 'action' => 'treeView')))); ?>
<?php endif; ?>

<?php foreach ($items as $item): ?>
  <?php echo render_treeview_node(
    $item,
    array('expand' => 1 < $item->rgt - $item->lft, 'active' => $sf_request->resourceId == $item->id),
    array('browser' => $browser, 'xhr-location' => url_for(array($item, 'module' => 'term', 'action' => 'treeView')))); ?>
<?php endforeach; ?>

<?php if (isset($hasNextSiblings) && $hasNextSiblings): ?>
  <?php echo render_treeview_node(
    null,
    array('more' => true),
    array('browser' => $browser, 'xhr-location' => url_for(array($item, 'module' => 'term', 'action' => 'treeView')))); ?>
<?php endif; ?>
