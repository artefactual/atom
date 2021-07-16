<?php if (isset($pager) && $pager->getNbResults()) { ?>
  <?php decorate_with('layout_2col'); ?>
<?php } else { ?>
  <?php decorate_with('layout_1col'); ?>
<?php } ?>

<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-people.png', ['alt' => '']); ?>
    <h1 aria-describedby="results-label">
      <?php if (isset($pager) && $pager->getNbResults()) { ?>
        <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
      <?php } else { ?>
        <?php echo __('No results found'); ?>
      <?php } ?>
    </h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_actor'); ?></span>
  </div>
<?php end_slot(); ?>

<?php if (isset($pager) && $pager->getNbResults()) { ?>

  <?php slot('sidebar'); ?>

    <h2 class="d-grid">
      <button class="btn btn-lg atom-btn-white text-wrap mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-aggregations" aria-expanded="true" aria-controls="collapse-aggregations">
        <?php echo sfConfig::get('app_ui_label_facetstitle'); ?>
      </button>
    </h2>

    <div class="collapse show" id="collapse-aggregations">

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-languages',
          'label' => __('Language'),
          'name' => 'languages',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-entitytype',
          'label' => __('Entity type'),
          'name' => 'entityType',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-maintainingrepository',
          'label' => __('Maintained by'),
          'name' => 'repository',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-occupation',
          'label' => __('Occupation'),
          'name' => 'occupation',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-places',
          'label' => sfConfig::get('app_ui_label_place'),
          'name' => 'place',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>

      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-subjects',
          'label' => sfConfig::get('app_ui_label_subject'),
          'name' => 'subject',
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

  <div class="d-flex flex-wrap gap-2 mb-3">
    <?php echo get_partial('search/filterTags', ['filterTags' => $filterTags]); ?>
  </div>

  <?php echo get_component('search', 'inlineSearch', [
      'label' => __('Search %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_actor'))]),
      'landmarkLabel' => __(sfConfig::get('app_ui_label_actor')), ]); ?>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo get_partial('actor/advancedSearch',
    [
        'criteria' => $search->criteria,
        'form' => $form,
        'fieldOptions' => $fieldOptions,
        'hiddenFields' => $hiddenFields,
        'show' => $showAdvanced,
    ]
  ); ?>

  <?php if (isset($pager) && $pager->getNbResults()) { ?>
    <section class="browse-options">

      <div class="pickers">
        <?php echo get_partial('default/sortPickers',
          [
              'options' => [
                  'lastUpdated' => __('Date modified'),
                  'alphabetic' => __('Name'),
                  'identifier' => __('Identifier'), ], ]); ?>
      </div>

    </section>

    <div id="content" class="browse-content">

      <?php foreach ($pager->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <?php echo include_partial('actor/searchResult', ['doc' => $doc, 'pager' => $pager, 'culture' => $selectedCulture, 'clipboardType' => 'actor']); ?>
      <?php } ?>

    </div>
  <?php } ?>

<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
