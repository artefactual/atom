<?php if (isset($hasPrevSiblings) && $hasPrevSiblings) { ?>
  <?php echo render_treeview_node(
    null,
    ['more' => true],
    ['browser' => $browser, 'xhr-location' => url_for([$items[0], 'module' => 'term', 'action' => 'treeView'])]); ?>
<?php } ?>

<?php foreach ($items as $item) { ?>
  <?php echo render_treeview_node(
    $item,
    ['expand' => 1 < $item->rgt - $item->lft, 'active' => $sf_request->resourceId == $item->id],
    ['browser' => $browser, 'xhr-location' => url_for([$item, 'module' => 'term', 'action' => 'treeView'])]); ?>
<?php } ?>

<?php if (isset($hasNextSiblings) && $hasNextSiblings) { ?>
  <?php echo render_treeview_node(
    null,
    ['more' => true],
    ['browser' => $browser, 'xhr-location' => url_for([$item, 'module' => 'term', 'action' => 'treeView'])]); ?>
<?php } ?>
