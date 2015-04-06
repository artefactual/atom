<section id="repo-holdings" class="list-menu"
  data-total-pages="<?php echo $pager->getLastPage() ?>"
  data-url="<?php echo url_for(array('module' => 'repository', 'action' => 'holdings', 'id' => $resource->id)) ?>">

  <h3><?php echo sfConfig::get('app_ui_label_holdings') ?></h3>
  <div><?php echo image_tag('loading.small.gif', array('class' => 'hidden')) ?></div>

  <ul>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <li><?php echo link_to(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture)), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', array('pager' => $pager)) ?>
</section>
