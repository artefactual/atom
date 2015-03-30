<?php echo get_component('repository', 'logo') ?>

<?php if (QubitAcl::check($resource, 'update')): ?>
  <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
<?php endif; ?>

<section id="repo-holdings" class="list-menu" data-total-pages="<?php echo $pager->getLastPage() ?>"
  data-url="<?php echo url_for(array('module' => 'repository', 'action' => 'holdings', 'repositoryId' => $resource->id)) ?>">

  <div class="inline-search">
    <h3 class="sidebar-pager-heading"><?php echo sfConfig::get('app_ui_label_holdings') ?></h3>
    <div class="sidebar-pager-heading"><?php echo image_tag('loading.small.gif', array('class' => 'hidden')) ?></div>
  </div>

  <ul id="repo-holdings-results">
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <li><?php echo link_to(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture)), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></li>

    <?php endforeach; ?>
  </ul>

  <?php echo get_partial('default/sidebarPager', array('pager' => $pager)) ?>
</section>
