<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>

  <div class="hidden-phone">
    <h1>
      <?php echo image_tag('/images/icons-large/icon-institutions.png') ?>
      <?php echo __('Browse %1% %2%', array(
        '%1%' => $pager->getNbResults(),
        '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
    </h1>
  </div>

  <div id="filter" class="visible-phone">
    <h2 class="widebtn btn-huge" data-toggle="collapse" data-target="#facets">
      <?php echo __('Filter %1% institutions', array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
    </h2>
  </div>

<?php end_slot() ?>

<?php slot('sidebar') ?>

  <div id="facets">

    <div class="btn-group top-options">
      <?php echo link_to(
        __('Alphabetic'),
        array('sort' => 'alphabetic') + $sf_request->getParameterHolder()->getAll(),
        array('class' => 'btn' . ('alphabetic' == $sortSetting ? ' active' : ''))) ?>
      <?php echo link_to(
        __('Last updated'),
        array('sort' => 'lastUpdated') + $sf_request->getParameterHolder()->getAll(),
        array('class' => 'btn' . ('lastUpdated' == $sortSetting ? ' active' : ''))) ?>
    </div>

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

<?php slot('content') ?>
  <section class="masonry">

    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <?php $authorizedFormOfName = render_title(get_search_i18n($doc, 'authorizedFormOfName')) ?>
      <div class="brick">
        <a href="<?php echo url_for(array('module' => 'repository', 'slug' => $doc['slug'])) ?>">
          <?php if (file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/conf/logo.png')): ?>
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
