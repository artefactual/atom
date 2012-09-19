<div id="search-results">

  <div class="row">

    <div class="hidden-phone">
      <div class="span8">
        <h1>
          <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')) ?>
          <?php echo __('%1% institutions', array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
        </h1>
      </div>
      <div class="span4">
        <div class="btn-group">
          <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
            <?php echo __('Sort') ?>
            <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="#"><?php echo __('Alphabetical') ?></a></li>
            <li><a href="#"><?php echo __('Last updated') ?></a></li>
          </ul>
        </div>
      </div>
    </div>

    <div id="filter" class="span12 visible-phone">
      <h2 class="widebtn btn-huge" data-toggle="collapse" data-target="#facets">
        <?php echo __('Filter %1% institutions', array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
      </h2>
    </div>

  </div>

  <div class="row">

    <div class="span3" id="facets">

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-archivetype',
        'label' => __('Archive type'),
        'facet' => 'types',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-province',
        'label' => __('Region'),
        'facet' => 'contact_i18n_region',
        'pager' => $pager,
        'filters' => $filters)) ?>

    </div>

    <div class="span9">

      <div class="section masonry">

        <?php foreach ($pager->getResults() as $hit): ?>
          <?php $doc = build_i18n_doc($hit, array('actor')) ?>
          <?php if (file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/.conf/logo.png')): ?>
            <div class="item image">
              <?php echo link_to(image_tag('/uploads/r/'.$doc['slug'].'/.conf/logo.png'), array('module' => 'repository', 'slug' => $doc['slug'])) ?>
            </div>
          <?php else: ?>
            <div class="item text">
              <?php echo link_to($doc['actor'][$sf_user->getCulture()]['authorizedFormOfName'], array('module' => 'repository', 'slug' => $doc['slug'])) ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>

      </div>

      <div class="section">

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    </div>

  </div>

</div>
