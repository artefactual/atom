<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1>
    <?php echo image_tag('/images/icons-large/icon-institutions.png') ?>
    <?php echo __('Browse %1% %2%', array(
      '%1%' => $pager->getNbResults(),
      '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
  </h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>

  <div id="facets">

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-archivetype',
      'label' => __('Archive type'),
      'facet' => 'types',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-province',
      'label' => __('Region'),
      'facet' => 'regions',
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
        <a href="<?php echo url_for(array('module' => 'repository', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
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

<?php slot('content') ?>
  <section class="masonry">

    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <?php $authorizedFormOfName = render_title(get_search_i18n($doc, 'authorizedFormOfName')) ?>
      <?php $hasLogo = file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/conf/logo.png') ?>
      <?php if ($hasLogo): ?>
        <div class="brick">
      <?php else: ?>
        <div class="brick brick-only-text">
      <?php endif; ?>
        <a href="<?php echo url_for(array('module' => 'repository', 'slug' => $doc['slug'])) ?>">
          <?php if ($hasLogo): ?>
            <div class="preview">
              <?php echo image_tag('/uploads/r/'.$doc['slug'].'/conf/logo.png') ?>
            </div>
          <?php else: ?>
            <h4><?php echo $authorizedFormOfName ?></h4>
          <?php endif; ?>
        </a>
        <div class="bottom">
          <p><?php echo $authorizedFormOfName ?></p>
        </div>
      </div>
    <?php endforeach; ?>

  </section>
<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
