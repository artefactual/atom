<?php decorate_with('layout_2col'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-institutions.png', ['alt' => '']); ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?></h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_repository'); ?></span>
  </div>
<?php end_slot(); ?>

<?php slot('sidebar'); ?>

  <section id="facets">

    <div class="visible-phone facets-header">
      <a class="x-btn btn-wide">
        <i class="fa fa-filter"></i>
        <?php echo __('Filters'); ?>
      </a>
    </div>

    <div class="content">

      <h2><?php echo sfConfig::get('app_ui_label_facetstitle'); ?></h2>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-languages',
          'label' => __('Language'),
          'name' => 'languages',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-archivetype',
          'label' => __('Archive type'),
          'name' => 'types',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-province',
          'label' => __('Geographic Region'),
          'name' => 'regions',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-geographicsubregion',
          'label' => __('Geographic Subregion'),
          'name' => 'geographicSubregions',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-locality',
          'label' => __('Locality'),
          'name' => 'locality',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-thematicarea',
          'label' => __('Thematic Area'),
          'name' => 'thematicAreas',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

    </div>

  </section>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <section class="browse-options">
    <div class="row">
      <div class="span4">
        <?php echo get_component('search', 'inlineSearch', [
            'label' => __('Search %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_repository'))]), ]); ?>
      </div>

      <?php echo get_partial('default/viewPicker', ['view' => $view, 'cardView' => $cardView,
          'tableView' => $tableView, 'module' => 'repository', ]); ?>

      <div class="pickers">
        <?php echo get_partial('default/sortPickers',
          [
              'options' => [
                  'lastUpdated' => __('Date modified'),
                  'alphabetic' => __('Name'),
                  'identifier' => __('Identifier'), ], ]); ?>
      </div>
    </div>
  </section>

  <section class="advanced-search-section">
    <a href="#" id="toggle-advanced-filters" class="advanced-search-toggle"><?php echo __('Advanced search options'); ?></a>
    <div id="advanced-repository-filters" class="advanced-search">
      <?php echo get_component('repository', 'advancedFilters', [
          'thematicAreas' => $thematicAreas,
          'repositories' => $repositories,
          'repositoryTypes' => $repositoryTypes, ] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?>
    </div>
  </section>

<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php if ($view === $tableView) { ?>
    <?php echo get_partial('repository/browseTableView', ['pager' => $pager, 'selectedCulture' => $selectedCulture]); ?>
  <?php } elseif ($view === $cardView) { ?>
    <?php echo get_partial('repository/browseCardView', ['pager' => $pager, 'selectedCulture' => $selectedCulture]); ?>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
