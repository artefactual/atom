<div id="search-results">

  <div class="row">

    <div class="span12 hidden-phone">
      <h1>
        <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')) ?>
        <?php echo __('%1% institutions', array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
      </h1>
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

      <div class="section">

        <?php foreach ($pager->getResults() as $hit): ?>
          <?php $doc = build_i18n_doc($hit, array('actor')) ?>
          <div class="institution maxi">
            <h2><?php echo link_to($doc['actor'][$sf_user->getCulture()]['authorizedFormOfName'], array('module' => 'repository', 'slug' => $doc['slug'])) ?></h2>
          </div>
        <?php endforeach; ?>

      </div>

      <div class="section">

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    </div>

  </div>

</div>
