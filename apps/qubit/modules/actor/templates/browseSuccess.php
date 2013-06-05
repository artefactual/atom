<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1>
    <?php echo image_tag('/images/icons-large/icon-people.png') ?>
    <?php echo __('Browse %1% %2%', array(
      '%1%' => $pager->getNbResults(),
      '%2%' => sfConfig::get('app_ui_label_actor'))) ?>
  </h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>
  <div id="facets">

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-entitytype',
      'label' => __('Entity type'),
      'facet' => 'types',
      'pager' => $pager,
      'filters' => $filters)) ?>

  </div>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">

    <?php if (isset($sf_request->query)): ?>
      <span class="search-filter">
        <?php echo esc_entities($sf_request->query) ?>
        <?php $params = $sf_request->getGetParameters() ?>
        <?php unset($params['query']) ?>
        <a href="<?php echo url_for(array('module' => 'actor', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

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
  <?php echo include_partial('actor/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
