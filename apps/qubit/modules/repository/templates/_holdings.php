<section id="repo-holdings" class="list-menu"
  data-total-pages="<?php echo $pager->getLastPage() ?>"
  data-url="<?php echo url_for(array('module' => 'repository', 'action' => 'holdings', 'id' => $resource->id)) ?>">

  <h3>
    <?php echo sfConfig::get('app_ui_label_holdings') ?>
    <?php echo image_tag('loading.small.gif', array('class' => 'hidden', 'id' => 'spinner', 'alt' => __('Loading ...'))) ?>
  </h3>
  <form class="sidebar-search" action="<?php echo url_for(array('module' => 'search')) ?>">
    <input type="hidden" name="repos" value="<?php echo $resource->id ?>">
    <div class="input-prepend input-append">
      <input type="text" name="query" placeholder="<?php echo __('Search holdings') ?>">
      <button class="btn" type="submit">
        <i class="icon-search"></i>
      </button>
    </div>
  </form>
  <div class="more">
    <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse', 'repos' => $resource->id)) ?>">
      <i class="icon-search"></i>
      <?php echo __('Browse %1% holdings', array('%1%' => $pager->getNbResults())) ?>
    </a>
  </div>
  <ul>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <li><?php echo link_to(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture)), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', array('pager' => $pager)) ?>
</section>
