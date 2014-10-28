<?php echo get_component('repository', 'logo') ?>

<?php if (QubitAcl::check($resource, 'update')): ?>
  <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
<?php endif; ?>

<section class="list-menu">

  <?php if (QubitAcl::check($resource, 'update')): ?>
    <h4><?php echo __('Reports') ?></h4>

    <ul>
      <li><?php echo link_to(__('Page views'), array($resource, 'module' => 'repository', 'action' => 'popular')); ?></li>
    </ul>
  <?php endif; ?>

  <h4><?php echo sfConfig::get('app_ui_label_holdings') ?></h4>

  <ul>
    <?php foreach ($resultSet->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <li><?php echo link_to(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture)), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php if ($resultSet->getTotalHits() > $limit): ?>
    <div class="more">
      <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse', 'repos' => $resource->id)) ?>">
        <i class="icon-search"></i>
        <?php echo __('Browse %1% holdings', array('%1%' => $resultSet->getTotalHits())) ?>
      </a>
    </div>
  <?php endif; ?>

</section>
