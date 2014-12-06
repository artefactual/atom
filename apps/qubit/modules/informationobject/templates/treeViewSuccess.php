<?php if (isset($hasPrevSiblings) && $hasPrevSiblings): ?>
  <?php echo render_treeview_node(
    null,
    array('more' => true),
    array('xhr-location' => url_for(array($items[0], 'module' => 'informationobject', 'action' => 'treeView')),
          'numSiblingsLeft' => $siblingCountPrev)); ?>

<?php endif; ?>

<?php foreach ($items as $item): ?>
  <?php echo render_treeview_node(
    $item,
    array('expand' => 1 < $item->rgt - $item->lft, 'active' => $sf_request->resourceId == $item->id),
    array('xhr-location' => url_for(array($item, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
<?php endforeach; ?>

<?php if (isset($hasNextSiblings) && $hasNextSiblings): ?>
  <?php echo render_treeview_node(
    null,
    array('more' => true),
    array('xhr-location' => url_for(array($item, 'module' => 'informationobject', 'action' => 'treeView')),
          'numSiblingsLeft' => $siblingCountNext)); ?>

<?php endif; ?>
