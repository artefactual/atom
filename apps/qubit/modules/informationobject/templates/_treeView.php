<ul id="treeview-menu" class="nav nav-tabs">
  <?php if ($treeviewType == 'sidebar'): ?>
    <li class="active">
      <a href="#treeview" data-toggle="#treeview">
        <?php echo __('Holdings') ?>
      </a>
    </li>
  <?php endif; ?>
  <li <?php echo ($treeviewType != 'sidebar') ? 'class="active"' : '' ?>>
    <a href="#treeview-search" data-toggle="#treeview-search">
      <?php echo __('Quick search') ?>
    </a>
  </li>
</ul>

<div id="treeview" data-current-id="<?php echo $resource->id ?>" data-sortable="<?php echo empty($sortable) ? 'false' : 'true' ?>">

  <?php if ($treeviewType == 'sidebar'): ?>

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
            array('xhr-location' => url_for(array($prevSiblings[0], 'module' => 'informationobject', 'action' => 'treeView')),
                  'numSiblingsLeft' => $siblingCountPrev)); ?>
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
            array('xhr-location' => url_for(array($child, 'module' => 'informationobject', 'action' => 'treeView')),
                  'numSiblingsLeft' => $siblingCountNext)); ?>
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
            array('xhr-location' => url_for(array($last, 'module' => 'informationobject', 'action' => 'treeView')),
                  'numSiblingsLeft' => $siblingCountNext)); ?>
        <?php endif; ?>

      <?php endif; ?>

    </ul>

  <?php endif; ?>

</div>

<div id="treeview-search" <?php echo ($treeviewType != 'sidebar') ? 'class="force-show"' : '' ?>>

  <form method="get" action="<?php echo url_for(array('module' => 'search', 'action' => 'index', 'collection' => $resource->getCollectionRoot()->id)) ?>" data-not-found="<?php echo __('No results found.') ?>">
    <div class="search-box">
      <input type="text" name="query" placeholder="<?php echo __('Search') ?>" />
      <button type="submit"><i class="fa fa-search"></i></button>
    </div>
  </form>

</div>
