<?php decorate_with('layout_wide') ?>

<div class="row-fluid">
  <div class="offset1 span6">

    <h1>
      <?php echo image_tag('/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')) ?>
      <?php echo __('Browse %1% digital objects', array('%1%' => $pager->getNbResults())) ?>
    </h1>

  </div>
  <div class="offset3 span2">

    <div id="header-facets">
      <?php echo get_partial('search/singleFacet', array(
        'target' => '#facet-mediatype',
        'label' => __('Media type'),
        'facet' => 'mediatypes',
        'pager' => $pager,
        'filters' => $filters)) ?>
    </div>

  </div>
</div>

<section class="masonry centered">
  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <div class="brick">
      <div class="preview">
        <?php echo link_to(image_tag($doc['digitalObject']['thumbnailPath']), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
      </div>
      <p class="description"><?php echo render_title(get_search_i18n($doc, 'title')) ?></p>
      <div class="bottom">
        <p><?php echo $doc['referenceCode'] ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<section>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
</section>
