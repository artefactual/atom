<?php if (!$showTreeview) { ?>
  <?php echo get_component('term', 'treeView', ['browser' => false]); ?>
<?php } else { ?>

  <?php echo get_component('term', 'treeView', ['browser' => false, 'tabs' => true, 'pager' => $listPager]); ?>

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

    <?php echo get_partial('search/aggregation', [
        'id' => '#facet-languages',
        'label' => __('Language'),
        'name' => 'languages',
        'aggs' => $aggs,
        'filters' => $search->filters, ]); ?>

    <?php if (QubitTaxonomy::PLACE_ID != $resource->taxonomyId) { ?>
      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-places',
          'label' => sfConfig::get('app_ui_label_place'),
          'name' => 'places',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>
      <?php } ?>

    <?php if (QubitTaxonomy::SUBJECT_ID != $resource->taxonomyId) { ?>
      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-subjects',
          'label' => sfConfig::get('app_ui_label_subject'),
          'name' => 'subjects',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>
    <?php } ?>

    <?php if (QubitTaxonomy::GENRE_ID != $resource->taxonomyId) { ?>
      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-genres',
          'label' => sfConfig::get('app_ui_label_genre'),
          'name' => 'genres',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>
    <?php } ?>

    <?php if (QubitTaxonomy::ACTOR_OCCUPATION_ID != $resource->taxonomyId) { ?>
      <?php echo get_partial('search/aggregation', [
          'id' => '#facet-occupations',
          'label' => __('Occupation'),
          'name' => 'occupations',
          'aggs' => $aggs,
          'filters' => $search->filters, ]); ?>
    <?php } ?>

  </div>

<?php } ?>
