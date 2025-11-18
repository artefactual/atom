<section class="card sidebar-paginated-list mb-3"
  data-total-pages="<?php echo $pager->getLastPage(); ?>"
  data-url="<?php echo url_for(['module' => 'repository', 'action' => 'holdings', 'id' => $resource->id]); ?>">

  <h5 class="p-3 mb-0">
    <?php echo sfConfig::get('app_ui_label_holdings'); ?>
    <span class="d-none spinner">
      <i class="fas fa-spinner fa-spin ms-2" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Loading ...'); ?></span>
    </span>
  </h5>

  <ul class="list-group list-group-flush">
    <?php foreach ($pager->getResults() as $hit) { ?>
      <?php $doc = $hit->getData(); ?>
      <?php echo link_to(render_value_inline(get_search_i18n($doc, 'title', ['allowEmpty' => false])), ['module' => 'informationobject', 'slug' => $doc['slug']], ['class' => 'list-group-item list-group-item-action']); ?>
    <?php } ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', ['pager' => $pager]); ?>

  <div class="card-body p-0">
    <a class="btn atom-btn-white border-0 w-100" href="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse', 'repos' => $resource->id]); ?>">
      <i class="fas fa-search me-1" aria-hidden="true"></i>
      <?php echo __('Browse %1% results', ['%1%' => $pager->getNbResults()]); ?>
    </a>
  </div>

</section>
