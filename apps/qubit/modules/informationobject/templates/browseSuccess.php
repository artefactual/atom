<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo image_tag('/images/icons-large/icon-archival.png') ?>
    <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
    <span class="sub"><?php echo sfConfig::get('app_ui_label_informationobject') ?></span>
  </h1>
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

      <h3><?php echo sfConfig::get('app_ui_label_facetstitle') ?></h3>

      <?php echo get_partial('search/facetLanguage', array(
        'target' => '#facet-languages',
        'label' => __('Language'),
        'facet' => 'languages',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-repository',
        'label' => sfConfig::get('app_ui_label_repository'),
        'facet' => 'repos',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-names',
        'label' => sfConfig::get('app_ui_label_creator'),
        'facet' => 'creators',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-names',
        'label' => sfConfig::get('app_ui_label_name'),
        'facet' => 'names',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-places',
        'label' => sfConfig::get('app_ui_label_place'),
        'facet' => 'places',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-subjects',
        'label' => sfConfig::get('app_ui_label_subject'),
        'facet' => 'subjects',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-levelOfDescription',
        'label' => __('Level of description'),
        'facet' => 'levels',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-mediaTypes',
        'label' => sfConfig::get('app_ui_label_mediatype'),
        'facet' => 'mediatypes',
        'pager' => $pager,
        'filters' => $filters)) ?>

    </div>

  </section>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">

    <?php if (isset($repos)): ?>
      <span class="search-filter">
        <?php echo render_title($repos) ?>
        <?php $params = $sf_request->getGetParameters() ?>
        <?php unset($params['repos']) ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($collectionFilter)): ?>
      <span class="search-filter">
        <?php echo $collectionFilter->__toString() ?>
        <?php $params = $sf_request->getGetParameters() ?>
        <?php unset($params['collection']) ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($sf_request->onlyMedia)): ?>
      <span class="search-filter">
        <?php echo __('Only digital objects') ?>
        <?php $params = $sf_request->getGetParameters() ?>
        <?php unset($params['onlyMedia']) ?>
        <?php unset($params['page']) ?>
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
      <?php $params = $sf_request->getGetParameters() ?>
      <?php unset($params['page']) ?>
      <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params + array('onlyMedia' => true)) ?>">
        <i class="icon-search"></i>
        <?php echo __('Show results with digital objects') ?>
      </a>
    </p>
  </div>
<?php endif; ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php $doc = $hit->getData() ?>
  <?php echo include_partial('search/searchResult', array('hit' => $hit, 'doc' => $doc, 'pager' => $pager, 'culture' => $selectedCulture)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
