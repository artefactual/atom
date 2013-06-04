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
      'label' => __('Institution'),
      'facet' => 'repos',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-names',
      'label' => __('Creators'),
      'facet' => 'creators',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-names',
      'label' => __('Names'),
      'facet' => 'names',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-places',
      'label' => __('Places'),
      'facet' => 'places',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-subjects',
      'label' => __('Subjects'),
      'facet' => 'subjects',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-levelOfDescription',
      'label' => __('Level of description'),
      'facet' => 'levels',
      'pager' => $pager,
      'filters' => $filters)) ?>

  </section>
<?php end_slot() ?>

<?php if (isset($pager->facets['digitalObject_mediaTypeId'])): ?>

  <?php $numResults = 0 ?>
  <?php foreach ($pager->facets['digitalObject_mediaTypeId']['terms'] as $mediaType): ?>
    <?php $numResults += $mediaType['count']; ?>
  <?php endforeach; ?>

  <?php if ($numResults > 0): ?>
    <div class="search-result media">
      <p class="title"><?php echo __('%1% results with digital media', array('%1%' => $numResults)) ?></p>
      <a href="#"><?php echo __('Show all') ?>&nbsp;&raquo;</a>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php $doc = $hit->getData() ?>
  <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
