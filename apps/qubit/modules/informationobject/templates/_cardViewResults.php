<?php use_helper('Text') ?>

<section class="masonry browse-masonry">

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <?php $title = get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $selectedCulture)) ?>

    <?php if (!empty($doc['hasDigitalObject'])): ?>
      <div class="brick">
    <?php else: ?>
      <div class="brick brick-only-text">
    <?php endif; ?>

      <a href="<?php echo url_for(array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>">
        <?php if (isset($doc['digitalObject']) && !empty($doc['digitalObject']['thumbnailPath'])
          && QubitAcl::check(QubitInformationObject::getById($hit->getId()), 'readThumbnail')
          && QubitGrantedRight::checkPremis($hit->getId(), 'readThumb')): ?>

          <?php echo link_to(image_tag($doc['digitalObject']['thumbnailPath'],
            array('alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown($title), 100))),
            array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>

        <?php elseif (isset($doc['digitalObject']) && !empty($doc['digitalObject']['mediaTypeId'])): // Show generic icon since no thumbnail present ?>

          <?php echo link_to(image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($doc['digitalObject']['mediaTypeId']),
            array('alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown($title), 100))),
            array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>

        <?php else: // No digital object, just display description title ?>

          <h5><?php echo render_title($title) ?></h5>

        <?php endif; ?>
      </a>

      <div class="bottom">
        <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false, 'repositoryOrDigitalObjBrowse' => true)) ?><?php echo render_title($title) ?>
      </div>
    </div>
  <?php endforeach; ?>

</section>
