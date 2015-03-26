<?php echo get_component('repository', 'logo') ?>

<?php if (QubitAcl::check($resource, 'update')): ?>
  <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
<?php endif; ?>

<section class="list-menu">

  <h4><?php echo sfConfig::get('app_ui_label_holdings') ?></h4>
  <form class="sidebar-search" method="get" action="/index.php/search" _lpchecked="1">
    <input type="hidden" name="repos" value="<?php echo $resource->id ?>">
    <div class="input-prepend input-append">
      <input type="text" name="query" placeholder="Search holdings">
      <div class="btn-group">
        <button class="btn" type="submit">
          <i class="icon-search"></i>
        </button>
      </div>
    </div>
  </form>

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
