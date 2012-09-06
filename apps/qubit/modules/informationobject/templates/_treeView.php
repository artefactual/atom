<div id="treeview" data-current-id="<?php echo $resource->id ?>" data-sortable="<?php echo $sortable ? 'true' : 'false' ?>">

  <ul class="unstyled">

    <li class="back" style="<?php echo QubitInformationObject::ROOT_ID == $resource->parentId ? 'display: none;' : '' ?>" data-xhr-location="<?php echo url_for(array('module' => 'informationobject', 'action' => 'treeView')) ?>">
      <i></i>
      <?php echo link_to(__('Show all'), array('module' => 'informationobject', 'action' => 'browse')) ?>
    </li>

    <?php // Ancestors ?>
    <?php foreach ($ancestors as $item): ?>
      <?php if (QubitInformationObject::ROOT_ID == $item->id) continue; ?>
      <?php echo render_treeview_node(
        $item,
        array('ancestor' => true),
        array('xhr-location' => url_for(array($item, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endforeach; ?>

    <?php // More button ?>
    <?php if ($hasPrevSiblings): ?>
      <?php echo render_treeview_node(
        null,
        array('more' => true),
        array('xhr-location' => url_for(array($prevSiblings[0], 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endif; ?>

    <?php // N prev items ?>
    <?php foreach ($prevSiblings as $prev): ?>
      <?php echo render_treeview_node(
        $prev,
        array('expand' => 1 < $prev->rgt - $prev->lft),
        array('xhr-location' => url_for(array($prev, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endforeach; ?>

    <?php // Current ?>
    <?php echo render_treeview_node(
      $resource,
      array('expand' => $resource->hasChildren(), 'active' => true),
      array('xhr-location' => url_for(array($resource, 'module' => 'informationobject', 'action' => 'treeView')))); ?>

    <?php // N next items ?>
    <?php foreach ($nextSiblings as $next): ?>
      <?php echo render_treeview_node(
        $next,
        array('expand' => 1 < $next->rgt - $next->lft),
        array('xhr-location' => url_for(array($next, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endforeach; ?>

    <?php // More button ?>
    <?php $last = isset($next) ? $next : $resource ?>
    <?php if ($hasNextSiblings): ?>
      <?php echo render_treeview_node(
        null,
        array('more' => true),
        array('xhr-location' => url_for(array($last, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endif; ?>

  </ul>

</div>
