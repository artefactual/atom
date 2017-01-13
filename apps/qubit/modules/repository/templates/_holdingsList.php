<section class="sidebar-paginated-list list-menu"
  data-total-pages="<?php echo $pager->getLastPage() ?>"
  data-url="<?php echo url_for(array('module' => 'repository', 'action' => 'holdings', 'id' => $resource->id)) ?>">

  <div class="more">
    <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse', 'repos' => $resource->id)) ?>">
      <i class="fa fa-search"></i>
      <?php echo __('Browse %1% holdings', array('%1%' => $pager->getNbResults())) ?>
    </a>
  </div>
  <ul>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <li><?php echo link_to(get_search_i18n($doc, 'title', array('allowEmpty' => false)), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', array('pager' => $pager)) ?>
</section>

