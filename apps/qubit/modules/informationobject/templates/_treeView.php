<ul id="treeview-menu" class="nav nav-tabs">
  <li class="active">
    <a href="#treeview" data-toggle="#treeview">
      <?php echo __('Holdings') ?>
    </a>
  </li>
  <li>
    <a href="#treeview-search" data-toggle="#treeview-search">
      <?php echo __('Search') ?>
    </a>
  </li>
</ul>

<div id="treeview" data-current-id="<?php echo $resource->id ?>" data-sortable="<?php echo $sortable ? 'true' : 'false' ?>">

  <ul class="unstyled">

    <?php // Ancestors ?>
    <?php foreach ($ancestors as $ancestor): ?>
      <?php if (QubitInformationObject::ROOT_ID == $ancestor->id) continue; ?>
      <?php echo render_treeview_node(
        $ancestor,
        array('ancestor' => true, 'root' => QubitInformationObject::ROOT_ID == $ancestor->parentId),
        array('xhr-location' => url_for(array($ancestor, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
    <?php endforeach; ?>

    <?php // Prev siblings (if there's no children) ?>
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
      array('ancestor' => $resource->hasChildren(), 'active' => true, 'root' => QubitInformationObject::ROOT_ID == $resource->parentId),
      array('xhr-location' => url_for(array($resource, 'module' => 'informationobject', 'action' => 'treeView')))); ?>

    <?php // Children ?>
    <?php if (isset($children)): ?>

      <?php foreach ($children as $child): ?>
        <?php echo render_treeview_node(
          $child,
          array('expand' => $child->hasChildren()),
          array('xhr-location' => url_for(array($child, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
      <?php endforeach; ?>

      <?php // More button ?>
      <?php $last = isset($child) ? $child : $resource ?>
      <?php if ($hasNextSiblings): ?>
        <?php echo render_treeview_node(
          null,
          array('more' => true),
          array('xhr-location' => url_for(array($child, 'module' => 'informationobject', 'action' => 'treeView')))); ?>
      <?php endif; ?>

    <?php // Or siblings ?>
    <?php elseif (isset($nextSiblings)): ?>

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

<div id="treeview-search">

  <div class="search-box">
    <input type="text" placeholder="<?php echo __('e.g. National defence') ?>" />
  </div>

</div>
