<?php use_helper('Javascript') ?>

<div class="row">
  <div class="span6">
    <h1>
      <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')) ?>
      <?php echo __('Media') ?>
    </h1>
  </div>
  <div class="span6">

    <?php if (isset($pager->facets['digitalObject_mediaTypeId'])): ?>
      <?php echo get_partial('search/singleFacet', array(
        'target' => '#facet-mediatype',
        'label' => __('Media type'),
        'facet' => 'digitalObject_mediaTypeId',
        'pager' => $pager,
        'filters' => $filters)) ?>
    <?php endif; ?>

  </div>
</div>

<div class="row">

  <div class="span12">

    <div class="section masonry">

      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit) ?>
        <div class="brick">
          <div class="preview zoom">
            <?php if (QubitTerm::EXTERNAL_URI_ID == $doc['digitalObject']['usageId']): ?>
              <a href="<?php echo $doc['digitalObject']['path'] ?>" class="btn"><?php echo __('External resource') ?></a>
            <?php else: ?>
              <?php echo link_to(image_tag($doc['digitalObject']['thumbnail_FullPath']), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
            <?php endif; ?>
          </div>
          <div class="details">
            <?php echo $doc[$sf_user->getCulture()]['title'] ?>
          </div>
        </div>
      <?php endforeach; ?>

    </div>

    <div class="section">

      <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

    </div>

  </div>

</div>
