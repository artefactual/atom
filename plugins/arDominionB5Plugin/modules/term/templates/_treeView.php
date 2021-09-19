<?php
  // TODO: this check should be moved to the component.
  if (!isset($resource)) {
      return;
  }
?>

<h2 class="d-grid">
  <button
    class="btn btn-lg atom-btn-white text-wrap"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#collapse-treeview"
    aria-expanded="true"
    aria-controls="collapse-treeview">
    <?php echo __('Browse %1%:', ['%1%' => render_title($resource->taxonomy)]); ?>
  </button>
</h2>

<div class="collapse show" id="collapse-treeview">

  <?php if (isset($tabs) && $tabs) { ?>
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

    </ul>
  <?php } ?>

  <div class="tab-content mb-3" id="treeview-content">

    <div class="tab-pane fade show active" id="treeview" role="tabpanel" aria-labelledby="treeview-tab" data-current-id="<?php echo $resource->id; ?>" data-browser="<?php echo $browser ? 'true' : 'false'; ?>">

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

      <div class="tab-pane fade" id="treeview-list" role="tabpanel" aria-labelledby="treeview-list-tab" data-error="<?php echo __('List error.'); ?>">

        <?php if (isset($pager)) { ?>
          <div class="list-group list-group-flush rounded-0 border">
            <?php foreach ($pager->getResults() as $hit) { ?>
              <?php $doc = $hit->getData(); ?>

              <?php $linkOptions = ['class' => 'list-group-item list-group-item-action text-truncate']; ?>
              <?php if ($doc['isProtected']) {
                  $linkOptions['class'] += ' readOnly';
              } ?>

              <?php echo link_to(
                render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])),
                ['module' => 'term', 'slug' => $doc['slug']],
                $linkOptions,
              ); ?>
            <?php } ?>
          </div>

          <?php if ($pager->haveToPaginate()) { ?>
            <nav aria-label="<?php echo __('Pagination'); ?>" class="p-2 bg-white border border-top-0">

              <p class="text-center mb-1 small result-count">
                <?php echo __('Results %1% to %2% of %3%', ['%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults()]); ?>
              </p>

              <ul class="pagination pagination-sm justify-content-center mb-2">
                <li class="page-item disabled previous">
                  <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?php echo __('Previous'); ?></a>
                </li>
                <li class="page-item next">
                  <?php echo link_to(
                    __('Next'),
                    ['listPage' => $pager->getPage() + 1] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                    ['class' => 'page-link']
                  ); ?>
                </li>
              </ul>

            </nav>
          <?php } ?>

        <?php } ?>

      </div>

      <div class="tab-pane fade" id="treeview-search" role="tabpanel" aria-labelledby="treeview-search-tab">

        <form method="get" role="search" class="p-2 bg-white border" action="<?php echo url_for([$resource->taxonomy, 'module' => 'taxonomy']); ?>" data-error="<?php echo __('Search error.'); ?>" data-not-found="<?php echo __('No results found.'); ?>" aria-label="<?php echo strip_markdown($resource->taxonomy); ?>">
          <div class="input-group">
            <button class="btn atom-btn-white dropdown-toggle" type="button" id="treeview-search-settings" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <i aria-hidden="true" class="fas fa-cog"></i>
              <span class="visually-hidden"><?php echo __('Search options'); ?></span>
            </button>
            <div class="dropdown-menu mt-2" aria-labelledby="treeview-search-settings">
              <div class="px-3 py-2">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="queryField" id="treeview-search-query-field-1" value="All labels" checked>
                  <label class="form-check-label" for="treeview-search-query-field-1">
                    <?php echo __('All labels'); ?>
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="queryField" id="treeview-search-query-field-2" value="Preferred label">
                  <label class="form-check-label" for="treeview-search-query-field-2">
                    <?php echo __('Preferred label'); ?>
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="queryField" id="treeview-search-query-field-3" value="\'Use for\' labels">
                  <label class="form-check-label" for="treeview-search-query-field-3">
                    <?php echo __('\'Use for\' labels'); ?>
                  </label>
                </div>
              </div>
            </div>
            <input type="text" name="query" class="form-control" aria-label="<?php echo __('Search %1%', ['%1%' => strip_markdown($resource->taxonomy)]); ?>" placeholder="<?php echo __('Search %1%', ['%1%' => strtolower(strip_markdown($resource->taxonomy))]); ?>" required>
            <button class="btn atom-btn-white" type="submit" id="treeview-search-submit-button" aria-label="<?php echo __('Search'); ?>">
              <i aria-hidden="true" class="fas fa-search"></i>
              <span class="visually-hidden"><?php echo __('Search'); ?></span>
            </button>
          </div>
        </form>

      </div>

    <?php } ?>

  </div>

</div>
