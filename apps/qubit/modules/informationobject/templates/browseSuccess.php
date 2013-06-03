<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1>
    <?php echo image_tag('/images/icons-large/icon-archival.png') ?>
    <?php echo __('Browse %1% %2%', array(
      '%1%' => $pager->getNbResults(),
      '%2%' => sfConfig::get('app_ui_label_informationobject'))) ?>
  </h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>
  <section id="facets">

    <?php echo get_partial('search/datesFacet') ?>

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

<?php slot('before-content') ?>

  <section class="header-options">
    <?php echo get_partial('default/sortPicker',
      array(
        'options' => array(
          'relevancy' => __('Relevancy'),
          'mostRecent' => __('Most recent'),
          'alphabetic' => __('Alphabetic')))) ?>
  </section>

<?php end_slot() ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php $doc = $hit->getData() ?>
  <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
