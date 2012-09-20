<?php use_helper('Javascript') ?>

<div class="row">
  <div class="span12">
    <h1><?php echo __('Media') ?></h1>
  </div>
</div>

<div class="row">

  <div class="span3" id="facets">

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-mediatype',
      'label' => __('Media type'),
      'facet' => 'types',
      'pager' => $pager,
      'filters' => $filters)) ?>

  </div>

  <div class="span9">

    <div class="section masonry">
      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit) ?>
        <div class="brick">
          <div class="preview"></div>
          <div class="details"></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="section">
      <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
    </div>

  </div>

</div>
