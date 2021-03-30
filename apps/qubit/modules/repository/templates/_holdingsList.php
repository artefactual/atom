<section class="sidebar-paginated-list list-menu"
  data-total-pages="<?php echo $pager->getLastPage(); ?>"
  data-url="<?php echo url_for(['module' => 'repository', 'action' => 'holdings', 'id' => $resource->id]); ?>">

  <div class="more">
    <a href="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse', 'repos' => $resource->id]); ?>">
      <i class="fa fa-search"></i>
      <?php echo __('Browse %1% holdings', ['%1%' => $pager->getNbResults()]); ?>
    </a>
  </div>
  <ul>
    <?php foreach ($pager->getResults() as $hit) { ?>
      <?php $doc = $hit->getData(); ?>
      <li><?php echo link_to(render_value_inline(get_search_i18n($doc, 'title', ['allowEmpty' => false])), ['module' => 'informationobject', 'slug' => $doc['slug']]); ?></li>
    <?php } ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', ['pager' => $pager]); ?>
</section>
