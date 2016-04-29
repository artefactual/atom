<?php if (isset($resource)): ?>

  <?php if (isset($tabs) && $tabs): ?>
    <ul id="treeview-menu" class="nav nav-tabs">
      <li class="active">
        <a href="#treeview" data-toggle="#treeview">
          <?php echo __('Treeview') ?>
        </a>
      </li>
      <li>
        <a href="#treeview-list" data-toggle="#treeview-list">
          <?php echo __('List') ?>
        </a>
      </li>
      <li>
        <a href="#treeview-search" data-toggle="#treeview-search">
          <?php echo __('Search') ?>
        </a>
      </li>
    </ul>
  <?php endif; ?>

  <div id="treeview" class="treeview-term" data-current-id="<?php echo $resource->id ?>" data-browser="<?php echo $browser ? 'true' : 'false' ?>">

    <div id="treeview-header">
      <p><?php echo render_title($resource->taxonomy) ?></p>
    </div>

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
        array('ancestor' => $resource->hasChildren(), 'active' => $getChildrensAndShowActive),
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

  <?php if (isset($tabs) && $tabs): ?>

    <div id="treeview-list">

      <?php if (isset($pager)): ?>
        <ul>

          <?php foreach ($pager->getResults() as $hit): ?>
            <?php $doc = $hit->getData() ?>

            <li>
              <?php if ($doc['isProtected']): ?>
                <?php echo link_to(get_search_i18n($doc, 'name', array('allowEmpty' => false)), array('module' => 'term', 'slug' => $doc['slug']), array('class' => 'readOnly')) ?>
              <?php else: ?>
                <?php echo link_to(get_search_i18n($doc, 'name', array('allowEmpty' => false)), array('module' => 'term', 'slug' => $doc['slug'])) ?>
              <?php endif; ?>
            </li>

          <?php endforeach; ?>

        </ul>

        <?php if ($pager->haveToPaginate()): ?>

          <section>

            <div class="result-count">
              <?php echo __('Results %1% to %2% of %3%', array('%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults())) ?>
            </div>

            <div>
              <div class="pager">
                <ul>

                  <?php if (1 < $pager->getPage()): ?>
                    <li class="previous">
                      <?php echo link_to('&laquo; '. __('Previous'), array('listPage' => $pager->getPage() - 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
                    </li>
                  <?php endif; ?>

                  <?php if ($pager->getLastPage() > $pager->getPage()): ?>
                    <li class="next">
                      <?php echo link_to(__('Next'). ' &raquo;', array('listPage' => $pager->getPage() + 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
                    </li>
                  <?php endif; ?>

                </ul>
              </div>
            </div>

          </section>

        <?php endif; ?>

      <?php endif; ?>

    </div>

    <div id="treeview-search">

      <form method="get" action="<?php echo url_for(array($resource->taxonomy, 'module' => 'taxonomy')) ?>" data-not-found="<?php echo __('No results found.') ?>">
        <div class="search-box">
          <input type="text" name="query" placeholder="<?php echo __('Search %1%', array('%1%' => $resource->taxonomy)) ?>" />
          <button type="submit"><i class="fa fa-search"></i></button>
          <button id="treeview-search-settings" href="#"><i class="fa fa-cog"></i></button>
        </div>

        <div class="animateNicely" id="field-options" style="display: none;">
          <ul>
            <li><label><input type="radio" name="queryField" value="All labels" checked><?php echo __('All labels') ?></label></li>
            <li><label><input type="radio" name="queryField" value="Preferred label"><?php echo __('Preferred label') ?></label></li>
            <li><label><input type="radio" name="queryField" value="\'Use for\' labels"><?php echo __('\'Use for\' labels') ?></label></li>
          </ul>
        </div>

      </form>

    </div>

  <?php endif; ?>

<?php endif; ?>
