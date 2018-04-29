<section class="sidebar-paginated-list list-menu"
  data-total-pages="<?php echo $list['pager']->getLastPage() ?>"
  data-url="<?php echo $list['dataUrl'] ?>">

  <h3>
    <?php echo $list['label'] ?>
    <?php echo image_tag('loading.small.gif', array('class' => 'hidden', 'id' => 'spinner', 'alt' => __('Loading ...'))) ?>
  </h3>

  <div class="more">
    <a href="<?php echo $list['moreUrl'] ?>">
      <i class="fa fa-search"></i>
      <?php echo __('Browse %1% results', array('%1%' => $list['pager']->getNbResults())) ?>
    </a>
  </div>

  <ul>
    <?php foreach ($list['pager']->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <li><?php echo link_to(render_value_inline(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false))), array('module' => 'actor', 'slug' => $doc['slug'])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', array('pager' => $list['pager'])) ?>

</section>
