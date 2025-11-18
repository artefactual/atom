<ul class="nav nav-tabs border-0" id="treeview-menu" role="tablist">

  <?php if ('sidebar' == $treeviewType) { ?>
    <li class="nav-item" role="presentation">
      <button
        class="nav-link active"
        id="treeview-tab"
        data-bs-toggle="tab"
        data-bs-target="#treeview"
        type="button"
        role="tab"
        aria-controls="treeview"
        aria-selected="true">
        <?php echo __('Holdings'); ?>
      </button>
    </li>
  <?php } ?>

  <li class="nav-item" role="presentation">
    <button
        class="nav-link<?php echo ('sidebar' != $treeviewType) ? ' active' : ''; ?>"
        id="treeview-search-tab"
        data-bs-toggle="tab"
        data-bs-target="#treeview-search"
        type="button"
        role="tab"
        aria-controls="treeview-search"
        aria-selected="<?php echo ('sidebar' != $treeviewType) ? 'true' : 'false'; ?>">
        <?php echo __('Quick search'); ?>
      </button>
  </li>

</ul>

<div class="tab-content mb-3" id="treeview-content">

  <?php if ('sidebar' == $treeviewType) { ?>
    <div class="tab-pane fade show active" id="treeview" role="tabpanel" aria-labelledby="treeview-tab" data-current-id="<?php echo $resource->id; ?>" data-sortable="<?php echo empty($sortable) ? 'false' : 'true'; ?>">

      <ul class="list-group rounded-0">

        <?php foreach ($ancestors as $ancestor) { ?>
          <?php if (QubitInformationObject::ROOT_ID == $ancestor->id) { ?>
            <?php continue; ?>
          <?php } ?>

          <?php echo render_treeview_node(
            $ancestor,
            ['ancestor' => true, 'root' => QubitInformationObject::ROOT_ID == $ancestor->parentId],
            ['xhr-location' => url_for([$ancestor, 'module' => 'informationobject', 'action' => 'treeView'])]); ?>
        <?php } ?>

        <?php if (!isset($children)) { ?>

          <?php if ($hasPrevSiblings) { ?>
            <?php echo render_treeview_node(
              null,
              ['more' => true],
              ['xhr-location' => url_for([$prevSiblings[0], 'module' => 'informationobject', 'action' => 'treeView']),
                  'numSiblingsLeft' => $siblingCountPrev, ]); ?>
          <?php } ?>

          <?php if (isset($prevSiblings)) { ?>
            <?php foreach ($prevSiblings as $prev) { ?>
              <?php echo render_treeview_node(
                $prev,
                ['expand' => 1 < $prev->rgt - $prev->lft],
                ['xhr-location' => url_for([$prev, 'module' => 'informationobject', 'action' => 'treeView'])]); ?>
            <?php } ?>
          <?php } ?>

        <?php } ?>

        <?php echo render_treeview_node(
          $resource,
          ['ancestor' => $resource->hasChildren(), 'active' => true, 'root' => QubitInformationObject::ROOT_ID == $resource->parentId],
          ['xhr-location' => url_for([$resource, 'module' => 'informationobject', 'action' => 'treeView'])]); ?>

        <?php if (isset($children)) { ?>

          <?php foreach ($children as $child) { ?>
            <?php echo render_treeview_node(
              $child,
              ['expand' => $child->hasChildren()],
              ['xhr-location' => url_for([$child, 'module' => 'informationobject', 'action' => 'treeView'])]); ?>
          <?php } ?>

          <?php $last = isset($child) ? $child : $resource; ?>
          <?php if ($hasNextSiblings) { ?>
            <?php echo render_treeview_node(
              null,
              ['more' => true],
              ['xhr-location' => url_for([$child, 'module' => 'informationobject', 'action' => 'treeView']),
                  'numSiblingsLeft' => $siblingCountNext, ]); ?>
          <?php } ?>

        <?php } elseif (isset($nextSiblings)) { ?>

          <?php foreach ($nextSiblings as $next) { ?>
            <?php echo render_treeview_node(
              $next,
              ['expand' => 1 < $next->rgt - $next->lft],
              ['xhr-location' => url_for([$next, 'module' => 'informationobject', 'action' => 'treeView'])]); ?>
          <?php } ?>

          <?php $last = isset($next) ? $next : $resource; ?>
          <?php if ($hasNextSiblings) { ?>
            <?php echo render_treeview_node(
              null,
              ['more' => true],
              ['xhr-location' => url_for([$last, 'module' => 'informationobject', 'action' => 'treeView']),
                  'numSiblingsLeft' => $siblingCountNext, ]); ?>
          <?php } ?>

        <?php } ?>

      </ul>

    </div>
  <?php } else { ?>
    <div id="fullwidth-treeview-active" data-treeview-alert-close="<?php echo __('Close'); ?>" hidden>
      <input type="button" id="fullwidth-treeview-more-button" class="btn btn-sm atom-btn-white" data-label="<?php echo __('%1% more'); ?>" value="" />
      <input type="button" id="fullwidth-treeview-reset-button" class="btn btn-sm atom-btn-white" value="<?php echo __('Reset'); ?>" />
      <span
        id="fullwidth-treeview-configuration"
        data-collection-url="<?php echo url_for([$resource->getCollectionRoot(), 'module' => 'informationobject']); ?>"
        data-collapse-enabled="<?php echo $collapsible; ?>"
        data-opened-text="<?php echo sfConfig::get('app_ui_label_fullTreeviewCollapseOpenedButtonText'); ?>"
        data-closed-text="<?php echo sfConfig::get('app_ui_label_fullTreeviewCollapseClosedButtonText'); ?>"
        data-items-per-page="<?php echo $itemsPerPage; ?>"
        data-enable-dnd="<?php echo $sf_user->isAuthenticated() ? 'yes' : 'no'; ?>">
      </span>
    </div>
  <?php } ?>

  <div class="tab-pane fade<?php echo ('sidebar' != $treeviewType) ? ' show active' : ''; ?>" id="treeview-search" role="tabpanel" aria-labelledby="treeview-search-tab">

    <form method="get" role="search" class="p-2 bg-white border" action="<?php echo url_for(['module' => 'search', 'action' => 'index', 'collection' => $resource->getCollectionRoot()->id]); ?>" data-not-found="<?php echo __('No results found.'); ?>">
      <div class="input-group">
        <input type="text" name="query" class="form-control" aria-label="<?php echo __('Search hierarchy'); ?>" placeholder="<?php echo __('Search hierarchy'); ?>" aria-describedby="treeview-search-submit-button" required>
        <button class="btn atom-btn-white" type="submit" id="treeview-search-submit-button">
          <i aria-hidden="true" class="fas fa-search"></i>
          <span class="visually-hidden"><?php echo __('Search'); ?></span>
        </button>
      </div>
    </form>

  </div>

</div>
