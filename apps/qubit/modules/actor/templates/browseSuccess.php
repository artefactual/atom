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

  <form class="header-form" method="get" action="<?php echo url_for(array('module' => 'actor', 'action' => 'browse')) ?>">
    <?php foreach ($sf_request->getGetParameters() as $key => $value): ?>
      <input type="hidden" name="<?php echo esc_entities($key) ?>" value="<?php echo esc_entities($value) ?>"/>
    <?php endforeach; ?>
    <div class="input-append">
      <input type="text" name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>" placeholder="<?php echo __('Search') ?>" />
      <button type="submit" class="btn icon-search"></button>
    </div>
  </form>

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
  <?php echo include_partial('actor/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
