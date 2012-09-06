<div id="treeview" data-current-id="<?php echo $resource->id ?>">

  <ul class="unstyled">

    <li class="back" style="<?php echo QubitTerm::ROOT_ID == $resource->parentId ? 'display: none;' : '' ?>" data-xhr-location="<?php echo url_for(array('module' => 'term', 'action' => 'treeView')) ?>">
      <i></i>
      <?php echo link_to(__('Show all'), array('module' => 'term', 'action' => 'browse')) ?>
    </li>

    <?php // Ancestors ?>
    <?php foreach ($ancestors as $item): ?>
      <?php if (QubitTerm::ROOT_ID == $item->id) continue; ?>
      <?php echo render_treeview_node(
        $item,
        array('ancestor' => true),
        array('xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $item->slug)))); ?>
    <?php endforeach; ?>

    <?php // More button ?>
    <?php if ($hasPrevSiblings): ?>
      <?php echo render_treeview_node(
        null,
        array('more' => true),
        array('xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $prevSiblings[0]->slug)))); ?>
    <?php endif; ?>

    <?php // N prev items ?>
    <?php if (isset($prevSiblings)): ?>
      <?php foreach ($prevSiblings as $prev): ?>
        <?php echo render_treeview_node(
          $prev,
          array('expand' => 1 < $prev->rgt - $prev->lft),
          array('xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $prev->slug)))); ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php // Current ?>
    <?php echo render_treeview_node(
      $resource,
      array('expand' => $resource->hasChildren(), 'active' => true),
      array('xhr-location' => url_for(array($resource, 'module' => 'term', 'action' => 'treeView')))); ?>

    <?php // N next items ?>
    <?php if (isset($nextSiblings)): ?>
      <?php foreach ($nextSiblings as $next): ?>
        <?php echo render_treeview_node(
          $next,
          array('expand' => 1 < $next->rgt - $next->lft),
          array('xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $next->slug)))); ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php // More button ?>
    <?php $last = isset($next) ? $next : $resource ?>
    <?php if ($hasNextSiblings): ?>
      <?php echo render_treeview_node(
        null,
        array('more' => true),
        array('xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $last->slug)))); ?>
    <?php endif; ?>

  </ul>

</div>
