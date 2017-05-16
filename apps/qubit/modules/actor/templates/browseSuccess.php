<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-people.png', array('alt' => '')) ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_actor') ?></span>
  </div>
<?php end_slot() ?>

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

      <?php echo get_partial('search/facetLanguage', array(
        'target' => '#facet-languages',
        'label' => __('Language'),
        'facet' => 'languages',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-entitytype',
        'label' => __('Entity type'),
        'facet' => 'types',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-maintainingrepository',
        'label' => __('Maintained by'),
        'facet' => 'maintainingRepository',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-occupation',
        'label' => __('Occupation'),
        'facet' => 'occupation',
        'pager' => $pager,
        'filters' => $search->filters)) ?>

    </div>

  </section>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span5">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_actor')))))) ?>
      </div>
      <div class="span4">
        <?php echo get_partial('default/sortPicker',
          array(
            'options' => array(
              'lastUpdated' => __('Most recent'),
              'alphabetic' => __('Alphabetic'),
              'identifier' => __('Identifier')))) ?>
      </div>
    </div>
  </section>

<?php end_slot() ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php $doc = $hit->getData() ?>
  <?php echo include_partial('actor/searchResult', array('doc' => $doc, 'pager' => $pager, 'culture' => $selectedCulture)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
