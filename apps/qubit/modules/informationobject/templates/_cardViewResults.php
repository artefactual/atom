<?php use_helper('Text'); ?>

<section class="masonry browse-masonry">

  <?php foreach ($pager->getResults() as $hit) { ?>
    <?php $doc = $hit->getData(); ?>
    <?php $title = get_search_i18n($doc, 'title', ['allowEmpty' => false, 'culture' => $selectedCulture]); ?>

    <?php if (!empty($doc['hasDigitalObject'])) { ?>
      <div class="brick">
    <?php } else { ?>
      <div class="brick brick-only-text">
    <?php } ?>

      <a href="<?php echo url_for(['module' => 'informationobject', 'slug' => $doc['slug']]); ?>">
        <?php if (
            isset($doc['digitalObject'])
            && !empty($doc['digitalObject']['thumbnailPath'])
            && QubitAcl::check(QubitInformationObject::getById($hit->getId()), 'readThumbnail')
        ) { ?>

          <?php echo link_to(image_tag($doc['digitalObject']['thumbnailPath'],
            ['alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown($title), 100)]),
            ['module' => 'informationobject', 'slug' => $doc['slug']]); ?>

        <?php } elseif (isset($doc['digitalObject']) && !empty($doc['digitalObject']['mediaTypeId'])) { ?>

          <?php echo link_to(image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($doc['digitalObject']['mediaTypeId']),
            ['alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown($title), 100)]),
            ['module' => 'informationobject', 'slug' => $doc['slug']]); ?>

        <?php } else { ?>

          <h5><?php echo render_title($title); ?></h5>

        <?php } ?>
      </a>

      <div class="bottom">
        <?php echo get_component('clipboard', 'button', ['slug' => $doc['slug'], 'wide' => false, 'repositoryOrDigitalObjBrowse' => true, 'type' => 'informationObject']); ?><?php echo render_title($title); ?>
      </div>
    </div>
  <?php } ?>

</section>
