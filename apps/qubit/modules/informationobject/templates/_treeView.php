<div id="treeview" data-current-id="<?php echo $resource->id ?>" data-sortable="<?php echo $sortable ? 'true' : 'false' ?>">

  <ul class="unstyled">

    <?php // Ancestors ?>
    <?php foreach ($ancestors as $item): ?>
      <?php if (QubitInformationObject::ROOT_ID == $item->id) continue; ?>
      <?php echo render_treeview_node($item,
        array('ancestor' => true),
        array('xhr-location' => url_for(array($item, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endforeach; ?>

    <?php if (!isset($children)): ?>

      <?php // More button ?>
      <?php if ($hasPrevSiblings): ?>
        <?php echo render_treeview_node(
          null,
          array('more' => true),
          array('xhr-location' => url_for(array($prevSiblings[0], 'module' => 'informationobject', 'action' => 'treeView')))); ?>
      <?php endif; ?>

      <?php // N prev items ?>
      <?php if (isset($prevSiblings)): ?>
        <?php foreach ($prevSiblings as $prev): ?>
          <?php echo render_treeview_node(
            $prev,
            array('expand' => 1 < $prev->rgt - $prev->lft),
            array('xhr-location' => url_for(array($prev, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php endif; ?>

    <?php // Current ?>
    <?php echo render_treeview_node(
      $resource,
      array('ancestor' => $resource->hasChildren(), 'active' => true),
      array('xhr-location' => url_for(array($resource, 'module' => 'informationobject', 'action' => 'treeView')))); ?>

    <?php // Children ?>
    <?php if (isset($children)): ?>
      <?php foreach ($children as $item): ?>
        <?php echo render_treeview_node(
          $item,
          array('expand' => $item->hasChildren()),
          array('xhr-location' => url_for(array($item, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
      <?php endforeach; ?>
    <?php elseif (isset($nextSibligns)): ?>

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

    <?php endif; ?>

  </ul>

</div>
