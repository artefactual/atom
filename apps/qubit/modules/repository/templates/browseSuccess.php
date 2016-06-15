<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-institutions.png', array('alt' => '')) ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_repository') ?></span>
  </div>
<?php end_slot() ?>

<?php slot('sidebar') ?>

  <section id="facets">

    <div class="visible-phone facets-header">
      <a class="x-btn btn-wide">
        <i class="fa fa-filter"></i>
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
        'target' => '#facet-archivetype',
        'label' => __('Archive type'),
        'facet' => 'types',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-province',
        'label' => __('Geographic Region'),
        'facet' => 'regions',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-geographicsubregion',
        'label' => __('Geographic Subregion'),
        'facet' => 'geographicSubregions',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-locality',
        'label' => __('Locality'),
        'facet' => 'locality',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-thematicarea',
        'label' => __('Thematic Area'),
        'facet' => 'thematicAreas',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

    </div>

  </section>

<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span5">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_repository')))))) ?>
      </div>

      <div class="span2">
        <span class="view-header-label"><?php echo __('View:') ?></span>

        <div class="btn-group">
          <?php echo link_to(' ', array('module' => 'repository', 'action' => 'browse', 'view' => $cardView) +
                             $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                             array('class' => 'btn fa fa-th-large '.($view === $cardView ? 'active' : ''))) ?>

          <?php echo link_to(' ', array('module' => 'repository', 'action' => 'browse', 'view' => $tableView) +
                             $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                             array('class' => 'btn fa fa-list '.($view === $tableView ? 'active' : ''))) ?>
        </div>
      </div>
      <div class="span2">
        <?php echo get_partial('default/sortPicker',
          array(
            'options' => array(
              'lastUpdated' => __('Most recent'),
              'alphabetic' => __('Alphabetic'),
              'identifier' => __('Identifier')))) ?>
      </div>
    </div>

    <section class="advanced-search-section">
      <a href="#" id="toggle-advanced-filters" class="advanced-search-toggle"><?php echo __('Advanced search options') ?></a>
      <div id="advanced-repository-filters" class="advanced-search">
        <?php echo get_component('repository', 'advancedFilters', array(
          'thematicAreas' => $thematicAreas,
          'repositories' => $repositories,
          'repositoryTypes' => $repositoryTypes) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
      </div>
    </section>
  </section>

<?php end_slot() ?>

<?php slot('content') ?>
  <?php if ($view === $tableView): ?>
    <?php echo get_partial('repository/browseTableView', array('pager' => $pager, 'selectedCulture' => $selectedCulture)) ?>
  <?php elseif ($view === $cardView): ?>
    <?php echo get_partial('repository/browseCardView', array('pager' => $pager, 'selectedCulture' => $selectedCulture)) ?>
  <?php endif; ?>
<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
