<div class="row g-3 mb-3 masonry">

<?php foreach ($pager->getResults() as $hit) { ?>
  <?php $doc = $hit->getData(); ?>
  <?php $title = get_search_i18n(
      $doc,
      'title',
      ['allowEmpty' => false, 'culture' => $selectedCulture]
  ); ?>

  <div class="col-sm-6 col-lg-4 masonry-item">
    <div class="card">
      <?php if (!empty($doc['hasDigitalObject'])) { ?>
        <?php
            // Get thumbnail or generic icon path
            if (
                isset($doc['digitalObject']['thumbnailPath'])
                && QubitAcl::check(
                    QubitInformationObject::getById($hit->getId()),
                    'readThumbnail'
                )
            ) {
                $imagePath = $doc['digitalObject']['thumbnailPath'];
            } else {
                $imagePath = QubitDigitalObject::getGenericIconPathByMediaTypeId(
                    $doc['digitalObject']['mediaTypeId'] ?: null
                );
            }
        ?>
        <a href="<?php echo url_for(
            ['module' => 'informationobject', 'slug' => $doc['slug']]
        ); ?>">
          <?php echo image_tag($imagePath, [
              'alt' => $doc['digitalObject']['digitalObjectAltText'] ?: strip_markdown($title),
              'class' => 'card-img-top',
          ]); ?>
        </a>
      <?php } else { ?>
        <a class="p-3" href="<?php echo url_for(['module' => 'informationobject', 'slug' => $doc['slug']]); ?>">
          <?php echo render_title($title); ?>
        </a>
      <?php } ?>

      <div class="card-body">
        <div class="card-text d-flex align-items-start gap-2">
          <span><?php echo render_title($title); ?></span>
          <?php echo get_component('clipboard', 'button', [
              'slug' => $doc['slug'],
              'wide' => false,
              'type' => 'informationObject',
          ]); ?>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
</div>
