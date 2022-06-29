<?php if (isset($pager) && $pager->getNbResults() || sfConfig::get('app_enable_institutional_scoping')) { ?>
  <?php decorate_with('layout_2col'); ?>
<?php } else { ?>
  <?php decorate_with('layout_1col'); ?>
<?php } ?>

<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <?php echo get_partial('default/printPreviewBar'); ?>

  <div class="multiline-header d-flex align-items-center mb-3">
    <i class="fas fa-3x fa-file-alt me-3" aria-hidden="true"></i>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php if (isset($pager) && $pager->getNbResults()) { ?>
          <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
        <?php } else { ?>
          <?php echo __('No results found'); ?>
        <?php } ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo sfConfig::get('app_ui_label_informationobject'); ?>
      </span>
    </div>
  </div>
<?php end_slot(); ?>

<?php if (isset($pager) && $pager->getNbResults() || sfConfig::get('app_enable_institutional_scoping')) { ?>

  <?php slot('sidebar'); ?>

    <h2 class="d-grid">
      <button
        class="btn btn-lg atom-btn-white collapsed text-wrap"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#collapse-aggregations"
        aria-expanded="false"
        aria-controls="collapse-aggregations">
        <?php echo sfConfig::get('app_ui_label_facetstitle'); ?>
      </button>
    </h2>

    <div class="collapse" id="collapse-aggregations">

      <?php if ($sf_user->getAttribute('search-realm') && sfConfig::get('app_enable_institutional_scoping')) { ?>
        <?php include_component('repository', 'holdingsInstitution', ['resource' => QubitRepository::getById($sf_user->getAttribute('search-realm'))]); ?>
      <?php } ?>

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

  <?php end_slot(); ?>

<?php } ?>

<?php slot('before-content'); ?>
  <div class="d-flex flex-wrap gap-2">
    <?php if ($topLod) { ?>
      <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
      <?php $params['topLod'] = 0; ?>
      <?php unset($params['page']); ?>
      <a 
        href="<?php echo url_for(
            ['module' => 'informationobject', 'action' => 'browse']
            + $params
        ); ?>"
        class="btn btn-sm atom-btn-white align-self-start mw-100 filter-tag d-flex">
        <span class="visually-hidden">
          <?php echo __('Remove filter:'); ?>
        </span>
        <span class="text-truncate d-inline-block">
          <?php echo __('Only top-level descriptions'); ?>
        </span>
        <i aria-hidden="true" class="fas fa-times ms-2 align-self-center"></i>
      </a>
    <?php } ?>

    <?php echo get_partial('search/filterTags', ['filterTags' => $filterTags]); ?>
  </div>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo get_component(
      'informationobject',
      'advancedSearch',
      [
          'criteria' => $search->criteria,
          'template' => $template,
          'form' => $form,
          'topLod' => $topLod,
          'hiddenFields' => $hiddenFields,
      ]
    ); ?>

  <?php if (isset($pager) && $pager->getNbResults()) { ?>

    <div class="d-flex flex-wrap gap-2 mb-3">
      <?php echo get_partial('default/printPreviewButton'); ?>

      <?php if ('yes' === sfConfig::get('app_treeview_show_browse_hierarchy_page', 'no')) { ?>
        <a
          class="btn btn-sm atom-btn-white"
          href="<?php echo url_for(['module' => 'browse', 'action' => 'hierarchy']); ?>">
          <i class="fas fa-sitemap me-1" aria-hidden="true"></i>
          <?php echo __('Hierarchy'); ?>
        </a>
      <?php } ?>

      <?php if ($sf_user->isAuthenticated()) { ?>
        <a
          class="btn btn-sm atom-btn-white"
          href="<?php echo url_for(array_merge(
              $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
              ['module' => 'informationobject', 'action' => 'exportCsv']
          )); ?>">
          <i class="fas fa-upload me-1" aria-hidden="true"></i>
          <?php echo __('Export CSV'); ?>
        </a>
      <?php } ?>

      <?php echo get_partial('default/viewPicker', ['view' => $view, 'cardView' => $cardView,
          'tableView' => $tableView, 'module' => 'informationobject', ]); ?>

      <div class="d-flex flex-wrap gap-2 ms-auto">
        <?php echo get_partial('default/sortPickers', ['options' => [
            'lastUpdated' => __('Date modified'),
            'alphabetic' => __('Title'),
            'relevance' => __('Relevance'),
            'identifier' => __('Identifier'),
            'referenceCode' => __('Reference code'),
            'startDate' => __('Start date'),
            'endDate' => __('End date'),
        ]]); ?>
      </div>
    </div>

    <?php if ($view === $tableView) { ?>
      <div id="content">
        <?php if (
            !isset($sf_request->onlyMedia)
            && isset($aggs['digitalobjects'])
            && 0 < $aggs['digitalobjects']['doc_count']
        ) { ?>
          <div class="d-grid d-sm-flex gap-2 align-items-center p-3 border-bottom">
            <?php echo __(
                '%1% results with digital objects',
                ['%1%' => $aggs['digitalobjects']['doc_count']]
            ); ?>
            <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
            <?php unset($params['page']); ?>
            <a
              class="btn btn-sm atom-btn-white ms-auto text-wrap"
              href="<?php echo url_for(
                  ['module' => 'informationobject', 'action' => 'browse']
                  + $params
                  + ['onlyMedia' => true]
              ); ?>">
              <i class="fas fa-search me-1" aria-hidden="true"></i>
              <?php echo __('Show results with digital objects'); ?>
            </a>
          </div>
        <?php } ?>

        <?php echo get_partial(
            'informationobject/tableViewResults',
            ['pager' => $pager, 'selectedCulture' => $selectedCulture]
        ); ?>
      </div>
    <?php } elseif ($view === $cardView) { ?>
      <?php if (
          !isset($sf_request->onlyMedia)
          && isset($aggs['digitalobjects'])
          && 0 < $aggs['digitalobjects']['doc_count']
      ) { ?>
        <div class="d-flex mb-3">
          <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
          <?php unset($params['page']); ?>
          <a
            class="btn btn-sm atom-btn-white ms-auto text-wrap"
            href="<?php echo url_for(
                ['module' => 'informationobject', 'action' => 'browse']
                + $params
                + ['onlyMedia' => true]
            ); ?>">
            <i class="fas fa-search me-1" aria-hidden="true"></i>
            <?php echo __(
                'Show %1% results with digital objects',
                ['%1%' => $aggs['digitalobjects']['doc_count']]
            ); ?>
          </a>
        </div>
      <?php } ?>

      <?php echo get_partial(
          'informationobject/cardViewResults',
          ['pager' => $pager, 'selectedCulture' => $selectedCulture]
      ); ?>
    <?php } ?>
  <?php } ?>

<?php end_slot(); ?>

<?php if (isset($pager)) { ?>
  <?php slot('after-content'); ?>
    <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
  <?php end_slot(); ?>
<?php } ?>
