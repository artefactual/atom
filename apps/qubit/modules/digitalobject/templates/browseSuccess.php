<?php decorate_with('layout_wide') ?>

<div class="row-fluid">
  <div class="span6">

    <div class="multiline-header">
      <?php echo image_tag('/images/icons-large/icon-media.png') ?>
      <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
      <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_digitalobject') ?></span>
    </div>

  </div>
  <div class="span6">

    <?php echo get_partial('default/sortPicker',
      array(
        'class' => 'shared',
        'options' => array(
          'alphabetic' => __('Alphabetic (title)'),
          'identifier' => __('Alphabetic (identifier)'),
          'lastUpdated' => __('Most recent')))) ?>

    <div class="header-facets">
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
          <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
          <a href="<?php echo url_for(array('module' => 'digitalobject', 'action' => 'browse') + $params) ?>" class="remove-filter"><i class="icon-remove"></i></a>
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
        <?php if (!empty($doc['digitalObject']['thumbnailPath'])): ?>
          <?php if (QubitAcl::check(QubitInformationObject::getById($hit->getId()), 'readThumbnail') &&
                    QubitGrantedRight::checkPremis($hit->getId(), 'readThumb')): ?>

            <?php echo link_to(image_tag($doc['digitalObject']['thumbnailPath']), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
          <?php else: ?>
            <?php echo link_to(image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($doc['digitalObject']['mediaTypeId'])),
              array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
          <?php endif; ?>
        <?php else: ?>
            <?php echo link_to(image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($doc['digitalObject']['mediaTypeId'])),
              array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
        <?php endif; ?>
      </div>
      <p class="description"><?php echo render_title(get_search_i18n($doc, 'title')) ?></p>
      <div class="bottom">
        <p>
          <?php if ('1' == sfConfig::get('app_inherit_code_informationobject', 1)
            && isset($doc['referenceCode']) && !empty($doc['referenceCode'])) : ?>
              <?php echo $doc['referenceCode'] ?>
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
