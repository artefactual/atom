<?php use_helper('Javascript') ?>

<div class="row">
  <div class="span6">
    <h1><?php echo __('Media') ?></h1>
  </div>
  <?php if (null !== $facet = $pager->getFacet('mediaTypeId')): ?>
    <div class="span6">
      <div class="btn-group top-options">
        <?php foreach ($facet['terms'] as $item): ?>
          <button type="button" class="btn"><?php echo $item['term'] ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<div class="row">

  <div class="span12">

    <div class="section masonry">

      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit) ?>
        <div class="brick">
          <div class="preview">
            <?php echo link_to(image_tag($doc['digitalObject']['thumbnail_FullPath']), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
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
