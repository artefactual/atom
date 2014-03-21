<?php decorate_with('layout_wide') ?>

<div class="row-fluid">
  <div class="span6">

    <h1 class="multiline">
      <?php echo image_tag('/images/icons-large/icon-media.png') ?>
      <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
      <span class="sub"><?php echo sfConfig::get('app_ui_label_digitalobject') ?></span>
    </h1>

  </div>
  <div class="span6">

    <div id="header-facets" class="pull-right">
      <?php echo get_partial('search/singleFacet', array(
        'target' => '#facet-mediatype',
        'label' => __('Media type'),
        'facet' => 'mediatypes',
        'pager' => $pager,
        'filters' => $filters)) ?>
    </div>

  </div>
</div>

<div class="row-fluid">
  <div class="span12">

    <section class="header-options">

      <?php if (isset($resource)): ?>
        <span class="search-filter">
          <?php echo render_title($resource) ?>
          <a href="<?php echo url_for(array('module' => 'digitalobject', 'action' => 'browse') + $sf_request->getGetParameters()) ?>" class="remove-filter"><i class="icon-remove"></i></a>
        </span>
      <?php endif; ?>

    </section>

  </div>
</div>

<section class="masonry centered">
  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <div class="brick">
      <div class="preview">
      <?php if (NULL !=  $doc['digitalObject']['thumbnailPath']): ?>
        <?php echo link_to(image_tag($doc['digitalObject']['thumbnailPath']), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
      <?php else: ?>
        <?php echo link_to(image_tag('question-mark'), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
      <?php endif; ?>
      </div>
      <p class="description"><?php echo render_title(get_search_i18n($doc, 'title')) ?></p>
      <div class="bottom">
        <p>
          <?php if ('1' == sfConfig::get('app_inherit_code_informationobject', 1)
            && isset($doc['inheritReferenceCode']) && !empty($doc['inheritReferenceCode'])) : ?>
              <?php echo $doc['inheritReferenceCode'] ?>
          <?php elseif (isset($doc['identifier']) && !empty($doc['identifier'])) : ?>
              <?php echo $doc['identifier'] ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<section>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
</section>
