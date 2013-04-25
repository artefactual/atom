<div id="treeview" data-current-id="<?php echo $resource->id ?>" data-browser="<?php echo $browser ? 'true' : 'false' ?>">

  <ul class="unstyled">

    <?php // Ancestors ?>
    <?php foreach ($ancestors as $ancestor): ?>
      <?php if (QubitTerm::ROOT_ID == $ancestor->id) continue; ?>
      <?php echo render_treeview_node(
        $ancestor,
        array('ancestor' => true),
        array('browser' => $browser, 'xhr-location' => url_for(array($ancestor, 'module' => 'term', 'action' => 'treeView')))); ?>
    <?php endforeach; ?>

    <?php // Prev siblings (if there's no children) ?>
    <?php if (!isset($children)): ?>

      <?php // More button ?>
      <?php if ($hasPrevSiblings): ?>
        <?php echo render_treeview_node(
          null,
          array('more' => true),
          array('browser' => $browser, 'xhr-location' => url_for(array($prevSiblings[0], 'module' => 'term', 'action' => 'treeView')))); ?>
      <?php endif; ?>

      <?php // N prev items ?>
      <?php if (isset($prevSiblings)): ?>
        <?php foreach ($prevSiblings as $prev): ?>
          <?php echo render_treeview_node(
            $prev,
            array('expand' => 1 < $prev->rgt - $prev->lft),
            array('browser' => $browser, 'xhr-location' => url_for(array($prev, 'module' => 'term', 'action' => 'treeView')))); ?>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php endif; ?>

    <?php // Current ?>
    <?php echo render_treeview_node(
      $resource,
      array('ancestor' => $resource->hasChildren(), 'active' => true),
      array('browser' => $browser, 'xhr-location' => url_for(array($resource, 'module' => 'term', 'action' => 'treeView')))); ?>

    <?php // Children ?>
    <?php if (isset($children)): ?>

      <?php foreach ($children as $child): ?>
        <?php echo render_treeview_node(
          $child,
          array('expand' => $child->hasChildren()),
          array('browser' => $browser, 'xhr-location' => url_for(array($child, 'module' => 'term', 'action' => 'treeView')))); ?>
      <?php endforeach; ?>

      <?php // More button ?>
      <?php $last = isset($child) ? $child : $resource ?>
      <?php if ($hasNextSiblings): ?>
        <?php echo render_treeview_node(
          null,
          array('more' => true),
          array('browser' => $browser, 'xhr-location' => url_for(array($child, 'module' => 'term', 'action' => 'treeView')))); ?>
      <?php endif; ?>

    <?php // Or siblings ?>
    <?php elseif (isset($nextSiblings)): ?>

      <?php // N next items ?>
      <?php if (isset($nextSiblings)): ?>
        <?php foreach ($nextSiblings as $next): ?>
          <?php echo render_treeview_node(
            $next,
            array('expand' => 1 < $next->rgt - $next->lft),
            array('browser' => $browser, 'xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $next->slug)))); ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php // More button ?>
      <?php $last = isset($next) ? $next : $resource ?>
      <?php if ($hasNextSiblings): ?>
        <?php echo render_treeview_node(
          null,
          array('more' => true),
          array('browser' => $browser, 'xhr-location' => url_for(array('module' => 'term', 'action' => 'treeView', 'slug' => $last->slug)))); ?>
      <?php endif; ?>

    <?php endif; ?>

  </ul>

</div>
