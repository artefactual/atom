<?php
  // TODO: this check should be moved to the component.
  if (!isset($resource)) { return; }
?>

<ul class="nav nav-tabs border-0" id="treeview-menu" role="tablist">

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
      <?php echo __('Treeview'); ?>
    </button>
  </li>

  <?php if (isset($tabs) && $tabs) { ?>

    <li class="nav-item" role="presentation">
      <button
        class="nav-link"
        id="treeview-list-tab"
        data-bs-toggle="tab"
        data-bs-target="#treeview-list"
        type="button"
        role="tab"
        aria-controls="treeview-list"
        aria-selected="true">
        <?php echo __('List'); ?>
      </button>
    </li>

    <li class="nav-item" role="presentation">
      <button
        class="nav-link"
        id="treeview-search-tab"
        data-bs-toggle="tab"
        data-bs-target="#treeview-search"
        type="button"
        role="tab"
        aria-controls="treeview-search"
        aria-selected="true">
        <?php echo __('Search'); ?>
      </button>
    </li>

  <?php } ?>

</ul>

<div class="tab-content mb-3" id="treeview-content">

  <div class="tab-pane fade show active" id="treeview" role="tabpanel" aria-labelledby="treeview-tab" data-current-id="<?php echo $resource->id; ?>" data-browser="<?php echo $browser ? 'true' : 'false'; ?>">

    <div id="treeview-header">
      <h3 class="h5 mb-0 p-3 border border-bottom-0"><?php echo render_title($resource->taxonomy); ?></h3>
    </div>

    <ul class="list-group rounded-0">

      <?php foreach ($ancestors as $ancestor) { ?>
        <?php if (QubitTerm::ROOT_ID == $ancestor->id) { ?>
          <?php continue; ?>
        <?php } ?>
        <?php echo render_treeview_node(
          $ancestor,
          ['ancestor' => true],
          ['browser' => $browser, 'xhr-location' => url_for([$ancestor, 'module' => 'term', 'action' => 'treeView'])]); ?>
      <?php } ?>

      <?php if (!isset($children)) { ?>

        <?php if ($hasPrevSiblings) { ?>
          <?php echo render_treeview_node(
            null,
            ['more' => true],
            ['browser' => $browser, 'xhr-location' => url_for([$prevSiblings[0], 'module' => 'term', 'action' => 'treeView'])]); ?>
        <?php } ?>

        <?php if (isset($prevSiblings)) { ?>
          <?php foreach ($prevSiblings as $prev) { ?>
            <?php echo render_treeview_node(
              $prev,
              ['expand' => 1 < $prev->rgt - $prev->lft],
              ['browser' => $browser, 'xhr-location' => url_for([$prev, 'module' => 'term', 'action' => 'treeView'])]); ?>
          <?php } ?>
        <?php } ?>

      <?php } ?>

      <?php echo render_treeview_node(
        $resource,
        ['ancestor' => $resource->hasChildren(), 'active' => $getChildrensAndShowActive],
        ['browser' => $browser, 'xhr-location' => url_for([$resource, 'module' => 'term', 'action' => 'treeView'])]); ?>

      <?php if (isset($children)) { ?>

        <?php foreach ($children as $child) { ?>
          <?php echo render_treeview_node(
            $child,
            ['expand' => $child->hasChildren()],
            ['browser' => $browser, 'xhr-location' => url_for([$child, 'module' => 'term', 'action' => 'treeView'])]); ?>
        <?php } ?>

        <?php $last = isset($child) ? $child : $resource; ?>
        <?php if ($hasNextSiblings) { ?>
          <?php echo render_treeview_node(
            null,
            ['more' => true],
            ['browser' => $browser, 'xhr-location' => url_for([$child, 'module' => 'term', 'action' => 'treeView'])]); ?>
        <?php } ?>

      <?php } elseif (isset($nextSiblings)) { ?>

        <?php if (isset($nextSiblings)) { ?>
          <?php foreach ($nextSiblings as $next) { ?>
            <?php echo render_treeview_node(
              $next,
              ['expand' => 1 < $next->rgt - $next->lft],
              ['browser' => $browser, 'xhr-location' => url_for(['module' => 'term', 'action' => 'treeView', 'slug' => $next->slug])]); ?>
          <?php } ?>
        <?php } ?>

        <?php $last = isset($next) ? $next : $resource; ?>
        <?php if ($hasNextSiblings) { ?>
          <?php echo render_treeview_node(
            null,
            ['more' => true],
            ['browser' => $browser, 'xhr-location' => url_for(['module' => 'term', 'action' => 'treeView', 'slug' => $last->slug])]); ?>
        <?php } ?>

      <?php } ?>

    </ul>

  </div>

  <?php if (isset($tabs) && $tabs) { ?>

    <div class="tab-pane fade" id="treeview-list" role="tabpanel" aria-labelledby="treeview-list-tab">

      <?php if (isset($pager)) { ?>
        <ul>

          <?php foreach ($pager->getResults() as $hit) { ?>
            <?php $doc = $hit->getData(); ?>

            <li>
              <?php if ($doc['isProtected']) { ?>
                <?php echo link_to(render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])), ['module' => 'term', 'slug' => $doc['slug']], ['class' => 'readOnly']); ?>
              <?php } else { ?>
                <?php echo link_to(render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])), ['module' => 'term', 'slug' => $doc['slug']]); ?>
              <?php } ?>
            </li>

          <?php } ?>

        </ul>

        <?php if ($pager->haveToPaginate()) { ?>

          <section>

            <div class="result-count">
              <?php echo __('Results %1% to %2% of %3%', ['%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults()]); ?>
            </div>

            <div>
              <div class="pager">
                <ul>

                  <?php if (1 < $pager->getPage()) { ?>
                    <li class="previous">
                      <?php echo link_to('&laquo; '.__('Previous'), ['listPage' => $pager->getPage() - 1] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?>
                    </li>
                  <?php } ?>

                  <?php if ($pager->getLastPage() > $pager->getPage()) { ?>
                    <li class="next">
                      <?php echo link_to(__('Next').' &raquo;', ['listPage' => $pager->getPage() + 1] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?>
                    </li>
                  <?php } ?>

                </ul>
              </div>
            </div>

          </section>

        <?php } ?>

      <?php } ?>

    </div>

    <div class="tab-pane fade" id="treeview-search" role="tabpanel" aria-labelledby="treeview-search-tab">

      <form method="get" role="search" action="<?php echo url_for([$resource->taxonomy, 'module' => 'taxonomy']); ?>" data-not-found="<?php echo __('No results found.'); ?>" aria-label="<?php echo strip_markdown($resource->taxonomy); ?>">
        <div class="search-box">
          <input type="text" name="query" aria-label="<?php echo __('Search %1%', ['%1%' => strip_markdown($resource->taxonomy)]); ?>" placeholder="<?php echo __('Search %1%', ['%1%' => strtolower(strip_markdown($resource->taxonomy))]); ?>" />
          <button type="submit" aria-label="<?php echo __('Search'); ?>"><i aria-hidden="true" class="fa fa-search"></i></button>
          <button id="treeview-search-settings" aria-label="<?php echo __('Settings'); ?>" href="#"><i aria-hidden="true" class="fa fa-cog"></i></button>
        </div>
        <div class="animateNicely" id="field-options" style="display: none;">
          <ul>
            <li><label><input type="radio" name="queryField" value="All labels" checked><?php echo __('All labels'); ?></label></li>
            <li><label><input type="radio" name="queryField" value="Preferred label"><?php echo __('Preferred label'); ?></label></li>
            <li><label><input type="radio" name="queryField" value="\'Use for\' labels"><?php echo __('\'Use for\' labels'); ?></label></li>
          </ul>
        </div>
      </form>

    </div>

  <?php } ?>

</div>




