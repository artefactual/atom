<?php if (isset($pager) && $pager->getNbResults()): ?>
  <?php decorate_with('layout_2col') ?>
<?php else: ?>
  <?php decorate_with('layout_1col') ?>
<?php endif; ?>

<?php use_helper('Date') ?>

<?php slot('title') ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-people.png', array('alt' => '')) ?>
    <h1 aria-describedby="results-label">
      <?php if (isset($pager) && $pager->getNbResults()): ?>
        <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
      <?php else: ?>
        <?php echo __('No results found') ?>
      <?php endif; ?>
    </h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_actor') ?></span>
  </div>
<?php end_slot() ?>

<?php if (isset($pager) && $pager->getNbResults()): ?>

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

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-languages',
          'label' => __('Language'),
          'name' => 'languages',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-entitytype',
          'label' => __('Entity type'),
          'name' => 'entityType',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-maintainingrepository',
          'label' => __('Maintained by'),
          'name' => 'repository',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-occupation',
          'label' => __('Occupation'),
          'name' => 'occupation',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-places',
          'label' => sfConfig::get('app_ui_label_place'),
          'name' => 'places',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-subjects',
          'label' => sfConfig::get('app_ui_label_subject'),
          'name' => 'subjects',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

        <?php echo get_partial('search/aggregation', array(
          'id' => '#facet-mediaTypes',
          'label' => sfConfig::get('app_ui_label_mediatype'),
          'name' => 'mediatypes',
          'aggs' => $aggs,
          'filters' => $search->filters)) ?>

      </div>

    </section>
  <?php end_slot() ?>

<?php endif; ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <?php if (isset($sf_request->hasDigitalObject)): ?>
      <span class="search-filter">
        <?php if (filter_var($sf_request->hasDigitalObject, FILTER_VALIDATE_BOOLEAN)): ?>
          <?php echo __('With digital objects') ?>
        <?php else: ?>
          <?php echo __('Without digital objects') ?>
        <?php endif; ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['hasDigitalObject']) ?>
        <a href="<?php echo url_for(array('module' => 'actor', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="fa fa-times"></i></a>
      </span>
    <?php endif; ?>

    <?php echo get_component('search', 'filter', array('object' => @$repository, 'param' => 'repository')) ?>
    <?php echo get_component('search', 'filter', array('object' => @$entityType, 'param' => 'entityType')) ?>
    <?php echo get_component('search', 'filter', array('object' => @$occupation, 'param' => 'occupation')) ?>
    <?php echo get_component('search', 'filter', array('object' => @$places, 'param' => 'places')) ?>
    <?php echo get_component('search', 'filter', array('object' => @$subjects, 'param' => 'subjects')) ?>
    <?php echo get_component('search', 'filter', array('object' => @$mediatypes, 'param' => 'mediatypes')) ?>

    <?php if (isset($sf_request->emptyField)): ?>
      <span class="search-filter">
        <?php echo __('Empty: %1%', array('%1%' => $fieldOptions[$sf_request->emptyField])) ?>
        <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
        <?php unset($params['emptyField']) ?>
        <a href="<?php echo url_for(array('module' => 'actor', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="fa fa-times"></i></a>
      </span>
    <?php endif; ?>

    <div class="row">
      <div class="span5">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_actor')))))) ?>
      </div>
    </div>

  </section>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo get_partial('actor/advancedSearch', array(
    'criteria'     => $search->criteria,
    'form'         => $form,
    'fieldOptions' => $fieldOptions,
    'hiddenFields' => $hiddenFields)) ?>

  <?php if (isset($pager) && $pager->getNbResults()): ?>
    <section class="browse-options">

      <div class="pickers">
        <?php echo get_partial('default/sortPickers',
          array(
            'options' => array(
              'lastUpdated' => __('Date modified'),
              'alphabetic'  => __('Name'),
              'identifier'  => __('Identifier')))) ?>
      </div>

    </section>

    <div id="content" class="browse-content">

      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <?php echo include_partial('actor/searchResult', array('doc' => $doc, 'pager' => $pager, 'culture' => $selectedCulture)) ?>
      <?php endforeach; ?>

    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
