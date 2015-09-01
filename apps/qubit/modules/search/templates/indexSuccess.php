<?php decorate_with('layout_2col') ?>

<?php slot('title') ?>
  <h1>
    <span class="search"><?php echo esc_entities($sf_request->query) ?></span>
    <span class="count"><?php echo __('%1% results', array('%1%' => $pager->getNbResults())) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">

    <?php if (isset($repos)): ?>
      <span class="search-filter">
        <?php echo render_title($repos) ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['repos']) ?>
        <a href="<?php echo url_for(array('module' => 'search') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($sf_request->onlyMedia)): ?>
      <span class="search-filter">
        <?php echo __('Only digital objects') ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['onlyMedia']) ?>
        <?php unset($params['page']) ?>
        <a href="<?php echo url_for(array('module' => 'search') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php if (isset($sf_request->topLod) && $sf_request->topLod): ?>
      <span class="search-filter">
        <?php echo __('Only top-level descriptions') ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php $params['topLod'] = 0 ?>
        <a href="<?php echo url_for(array('module' => 'search', 'action' => 'index') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

  </section>

<?php end_slot() ?>

<?php if (sfConfig::get('app_multi_repository') && isset($pager->facets['repository_id'])): ?>

  <?php if (sfConfig::get('app_multi_repository') && isset($pager->facets['repository_id'])): ?>
    <?php // echo __('in %1% institutions', array('%1%' => count($pager->facets['repository_id']['terms']))) ?>
  <?php endif; ?>

  <div id="top-facet">
    <h2 class="visible-phone widebtn btn-huge" data-toggle="collapse" data-target="#institutions"><?php echo __('Institutions') ?></h2>
    <div id="more-instistutions" class="pull-right">
      <select>
        <option value=""><?php echo __('All institutions') ?></option>
        <?php foreach ($pager->facets['repository_id']['terms'] as $id => $term): ?>
          <option value="<?php echo $id; ?>"><?php echo __($term['term']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

<?php endif; ?>

<?php slot('sidebar') ?>
  <section id="facets">

    <h2><?php echo sfConfig::get('app_ui_label_facetstitle') ?></h2>

    <?php echo get_partial('search/facetLanguage', array(
      'target' => '#facet-languages',
      'label' => __('Language'),
      'facet' => 'languages',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-collection',
      'label' => __('Part of'),
      'facet' => 'collection',
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
      'target' => '#facet-repository',
      'label' => sfConfig::get('app_ui_label_repository'),
      'facet' => 'repos',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-names',
      'label' => sfConfig::get('app_ui_label_creator'),
      'facet' => 'creators',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-names',
      'label' => sfConfig::get('app_ui_label_name'),
      'facet' => 'names',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-places',
      'label' => sfConfig::get('app_ui_label_place'),
      'facet' => 'places',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-subjects',
      'label' => sfConfig::get('app_ui_label_subject'),
      'facet' => 'subjects',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-genres',
      'label' => sfConfig::get('app_ui_label_genre'),
      'facet' => 'genres',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-mediaTypes',
      'label' => __('Media types'),
      'facet' => 'mediatypes',
      'pager' => $pager,
      'filters' => $search->filters,
      'open' => true)) ?>

  </section>
<?php end_slot() ?>

<?php if (!isset($sf_request->onlyMedia) && isset($pager->facets['digitalobjects']) && 0 < $pager->facets['digitalobjects']['count']): ?>
  <div class="search-result media-summary">
    <p>
      <?php echo __('%1% results with digital objects', array(
        '%1%' => $pager->facets['digitalobjects']['count'])) ?>
      <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
      <?php unset($params['page']) ?>
      <a href="<?php echo url_for(array('module' => 'search') + $params + array('onlyMedia' => true)) ?>">
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
