<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo image_tag('/images/icons-large/icon-people.png') ?>
    <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
    <span class="sub"><?php echo sfConfig::get('app_ui_label_actor') ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>

  <div id="browse-search">

    <form method="get" action="<?php echo url_for(array('module' => 'actor', 'action' => 'browse')) ?>" data-not-found="<?php echo __('No results found.') ?>">
      <div class="search-box">
        <input type="text" name="subquery" placeholder="<?php echo __('Search %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_actor')))) ?>" />
        <button type="submit"><i class="icon-search"></i></button>
      </div>
    </form>

  </div>

  <section id="facets">

    <div class="visible-phone facets-header">
      <a class="x-btn btn-wide">
        <i class="icon-filter"></i>
        <?php echo __('Filters') ?>
      </a>
    </div>

    <div class="content">

      <h3><?php echo sfConfig::get('app_ui_label_facetstitle') ?></h3>

      <?php echo get_partial('search/facetLanguage', array(
        'target' => '#facet-languages',
        'label' => __('Language'),
        'facet' => 'languages',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-entitytype',
        'label' => __('Entity type'),
        'facet' => 'types',
        'pager' => $pager,
        'filters' => $filters)) ?>

    </div>

  </section>
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

    <?php if (isset($sf_request->subquery)): ?>
      <span class="search-filter">
        <?php echo esc_entities($sf_request->subquery) ?>
        <?php $params = $sf_request->getGetParameters() ?>
        <?php unset($params['subquery']) ?>
        <a href="<?php echo url_for(array('module' => 'actor', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
      </span>
    <?php endif; ?>

    <?php echo get_partial('default/sortPicker',
      array(
        'options' => array(
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
