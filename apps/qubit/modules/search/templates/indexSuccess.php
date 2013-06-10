<?php decorate_with('layout_2col') ?>

<?php slot('title') ?>
  <h1>
    <span class="search"><?php echo esc_entities($sf_request->query) ?></span>
    <span class="count"><?php echo __('%1% results', array('%1%' => $pager->getNbResults())) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php if (isset($realm)): ?>

    <section class="header-options">

      <span class="search-filter">
        <?php echo render_title($realm) ?>
        <?php $params = $sf_request->getGetParameters() ?>
        <?php unset($params['realm']) ?>
        <a href="<?php echo url_for(array('module' => 'search') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>

      <?php if (isset($sf_request->onlyMedia)): ?>
        <span class="search-filter">
          <?php echo __('Only digital objects') ?>
          <?php $params = $sf_request->getGetParameters() ?>
          <?php unset($params['onlyMedia']) ?>
          <a href="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
        </span>
      <?php endif; ?>

    </section>

  <?php endif; ?>

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

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-levelOfDescription',
      'label' => __('Level of description'),
      'facet' => 'levels',
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
      'target' => '#facet-mediaTypes',
      'label' => __('Media types'),
      'facet' => 'mediatypes',
      'pager' => $pager,
      'filters' => $filters)) ?>

  </section>
<?php end_slot() ?>

<?php if (!isset($sf_request->onlyMedia) && isset($pager->facets['digitalobjects']) && 0 < $pager->facets['digitalobjects']['count']): ?>
  <div class="search-result media-summary">
    <p>
      <?php echo __('%1% results with digital objects', array(
        '%1%' => $pager->facets['digitalobjects']['count'])) ?>
      <a href="<?php echo url_for(array('module' => 'search') + $sf_request->getGetParameters() + array('onlyMedia' => true)) ?>">
        <i class="icon-search"></i>
        <?php echo __('Show all') ?>
      </a>
    </p>
  </div>
<?php endif; ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php $doc = $hit->getData() ?>
  <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
