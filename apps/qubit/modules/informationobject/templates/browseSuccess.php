<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-archival.png') ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_informationobject') ?></span>
  </div>
<?php end_slot() ?>

<?php slot('sidebar') ?>
  <section id="facets">

    <div class="visible-phone facets-header">
      <a class="x-btn btn-wide">
        <i class="icon-filter"></i>
        <?php echo __('Filters') ?>
      </a>
    </div>

    <div class="content">

      <h2><?php echo sfConfig::get('app_ui_label_facetstitle') ?></h2>

      <?php echo get_partial('search/facetLanguage', array(
        'target' => '#facet-languages',
        'label' => __('Language'),
        'facet' => 'languages',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-collection',
        'label' => __('Part of'),
        'facet' => 'collection',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-repository',
        'label' => sfConfig::get('app_ui_label_repository'),
        'facet' => 'repos',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-names',
        'label' => sfConfig::get('app_ui_label_creator'),
        'facet' => 'creators',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-names',
        'label' => sfConfig::get('app_ui_label_name'),
        'facet' => 'names',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-places',
        'label' => sfConfig::get('app_ui_label_place'),
        'facet' => 'places',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-subjects',
        'label' => sfConfig::get('app_ui_label_subject'),
        'facet' => 'subjects',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-genres',
        'label' => sfConfig::get('app_ui_label_genre'),
        'facet' => 'genres',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-levelOfDescription',
        'label' => __('Level of description'),
        'facet' => 'levels',
        'pager' => $pager,
        'filters' => $search->filters,
        'topLvlDescUrl' => $topLvlDescUrl,
        'allLvlDescUrl' => $allLvlDescUrl,
        'checkedTopDesc' => $checkedTopDesc,
        'checkedAllDesc' => $checkedAllDesc,
        'open' => true)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-mediaTypes',
        'label' => sfConfig::get('app_ui_label_mediatype'),
        'facet' => 'mediatypes',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

    </div>

  </section>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">

    <?php if (isset($repos)): ?>
      <span class="search-filter">
        <?php echo render_title($repos) ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['repos']) ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($collectionFilter)): ?>
      <span class="search-filter">
        <?php echo $collectionFilter->__toString() ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['collection']) ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($sf_request->onlyMedia)): ?>
      <span class="search-filter">
        <?php echo __('Only digital objects') ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['onlyMedia']) ?>
        <?php unset($params['page']) ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($sf_request->topLod) && $sf_request->topLod): ?>
      <span class="search-filter">
        <?php echo __('Only top-level descriptions') ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php $params['topLod'] = 0 ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php echo get_partial('default/sortPicker',
      array(
        'options' => array(
          'lastUpdated' => __('Most recent'),
          'alphabetic' => __('Alphabetic'),
          'identifier' => __('Reference code')))) ?>

  </section>

<?php end_slot() ?>

<?php if (!isset($sf_request->onlyMedia) && isset($pager->facets['digitalobjects']) && 0 < $pager->facets['digitalobjects']['count']): ?>
  <div class="search-result media-summary">
    <p>
      <?php echo __('%1% results with digital objects', array(
        '%1%' => $pager->facets['digitalobjects']['count'])) ?>
      <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
      <?php unset($params['page']) ?>
      <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params + array('onlyMedia' => true)) ?>">
        <i class="icon-search"></i>
        <?php echo __('Show results with digital objects') ?>
      </a>
    </p>
  </div>
<?php endif; ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php echo get_partial('search/searchResult', array('hit' => $hit, 'culture' => $selectedCulture)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
