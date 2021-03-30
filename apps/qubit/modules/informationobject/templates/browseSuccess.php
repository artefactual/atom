<?php if (isset($pager) && $pager->getNbResults() || sfConfig::get('app_enable_institutional_scoping')) { ?>
  <?php decorate_with('layout_2col'); ?>
<?php } else { ?>
  <?php decorate_with('layout_1col'); ?>
<?php } ?>

<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <?php echo get_partial('default/printPreviewBar'); ?>

  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-archival.png', ['alt' => '']); ?>
    <h1 aria-describedby="results-label">
      <?php if (isset($pager) && $pager->getNbResults()) { ?>
        <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
      <?php } else { ?>
        <?php echo __('No results found'); ?>
      <?php } ?>
    </h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_informationobject'); ?></span>
  </div>
<?php end_slot(); ?>

<?php if (isset($pager) && $pager->getNbResults() || sfConfig::get('app_enable_institutional_scoping')) { ?>

  <?php slot('sidebar'); ?>

    <section id="facets">

      <div class="visible-phone facets-header">
        <a class="x-btn btn-wide">
          <i class="fa fa-filter"></i>
          <?php echo __('Filters'); ?>
        </a>
      </div>

      <div class="content">

        <?php if ($sf_user->getAttribute('search-realm') && sfConfig::get('app_enable_institutional_scoping')) { ?>
          <?php include_component('repository', 'holdingsInstitution', ['resource' => QubitRepository::getById($sf_user->getAttribute('search-realm'))]); ?>
        <?php } ?>

        <h2><?php echo sfConfig::get('app_ui_label_facetstitle'); ?></h2>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-languages',
            'label' => __('Language'),
            'name' => 'languages',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-collection',
            'label' => __('Part of'),
            'name' => 'collection',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php if (sfConfig::get('app_multi_repository')) { ?>
          <?php echo get_partial('search/aggregation', [
              'id' => '#facet-repository',
              'label' => sfConfig::get('app_ui_label_repository'),
              'name' => 'repos',
              'aggs' => $aggs,
              'filters' => $search->filters, ]); ?>
        <?php } ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-names',
            'label' => sfConfig::get('app_ui_label_creator'),
            'name' => 'creators',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-names',
            'label' => sfConfig::get('app_ui_label_name'),
            'name' => 'names',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-places',
            'label' => sfConfig::get('app_ui_label_place'),
            'name' => 'places',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-subjects',
            'label' => sfConfig::get('app_ui_label_subject'),
            'name' => 'subjects',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-genres',
            'label' => sfConfig::get('app_ui_label_genre'),
            'name' => 'genres',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-levelOfDescription',
            'label' => __('Level of description'),
            'name' => 'levels',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

        <?php echo get_partial('search/aggregation', [
            'id' => '#facet-mediaTypes',
            'label' => sfConfig::get('app_ui_label_mediatype'),
            'name' => 'mediatypes',
            'aggs' => $aggs,
            'filters' => $search->filters, ]); ?>

      </div>

    </section>

  <?php end_slot(); ?>

<?php } ?>

<?php slot('before-content'); ?>

  <section class="header-options">

    <?php if ($topLod) { ?>
      <span class="search-filter">
        <?php echo __('Only top-level descriptions'); ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
        <?php $params['topLod'] = 0; ?>
        <a href="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse'] + $params); ?>" class="remove-filter"><i class="fa fa-times"></i></a>
      </span>
    <?php } ?>

    <?php echo get_partial('search/filterTags', ['filterTags' => $filterTags]); ?>

  </section>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo get_partial('search/advancedSearch', [
      'criteria' => $search->criteria,
      'template' => $template,
      'form' => $form,
      'show' => $showAdvanced,
      'topLod' => $topLod,
      'rangeType' => $rangeType,
      'hiddenFields' => $hiddenFields, ]); ?>

  <?php if (isset($pager) && $pager->getNbResults()) { ?>

    <section class="browse-options">
      <?php echo get_partial('default/printPreviewButton'); ?>

      <?php if ('yes' === sfConfig::get('app_treeview_show_browse_hierarchy_page', 'no')) { ?>
        <a href="<?php echo url_for(['module' => 'browse', 'action' => 'hierarchy']); ?>">
          <i class="fa fa-sitemap"></i>
          Hierarchy
        </a>
      <?php } ?>

      <?php if ($sf_user->isAuthenticated()) { ?>
        <a href="<?php echo url_for(array_merge($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), ['module' => 'informationobject', 'action' => 'exportCsv'])); ?>">
          <i class="fa fa-upload"></i>
          <?php echo __('Export CSV'); ?>
        </a>
      <?php } ?>

      <span>
        <?php echo get_partial('default/viewPicker', ['view' => $view, 'cardView' => $cardView,
            'tableView' => $tableView, 'module' => 'informationobject', ]); ?>
      </span>

      <div class="pickers">
        <?php echo get_partial('default/sortPickers', [
            'options' => [
                'lastUpdated' => __('Date modified'),
                'alphabetic' => __('Title'),
                'relevance' => __('Relevance'),
                'identifier' => __('Identifier'),
                'referenceCode' => __('Reference code'),
                'startDate' => __('Start date'),
                'endDate' => __('End date'), ], ]); ?>
      </div>
    </section>

    <div id="content" class="browse-content">
      <?php if (!isset($sf_request->onlyMedia) && isset($aggs['digitalobjects']) && 0 < $aggs['digitalobjects']['doc_count']) { ?>
        <div class="search-result media-summary">
          <p>
            <?php echo __('%1% results with digital objects', [
                '%1%' => $aggs['digitalobjects']['doc_count'], ]); ?>
            <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
            <?php unset($params['page']); ?>
            <a href="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse'] + $params + ['onlyMedia' => true]); ?>">
              <i class="fa fa-search"></i>
              <?php echo __('Show results with digital objects'); ?>
            </a>
          </p>
        </div>
      <?php } ?>

      <?php if ($view === $tableView) { ?>
        <?php echo get_partial('informationobject/tableViewResults', ['pager' => $pager, 'selectedCulture' => $selectedCulture]); ?>
      <?php } elseif ($view === $cardView) { ?>
        <?php echo get_partial('informationobject/cardViewResults', ['pager' => $pager, 'selectedCulture' => $selectedCulture]); ?>
      <?php } ?>
    </div>

  <?php } ?>

<?php end_slot(); ?>

<?php if (isset($pager)) { ?>
  <?php slot('after-content'); ?>
    <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
  <?php end_slot(); ?>
<?php } ?>
