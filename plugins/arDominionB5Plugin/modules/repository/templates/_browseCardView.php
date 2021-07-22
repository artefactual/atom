<div class="row g-3 mb-3 masonry">
  <?php foreach ($pager->getResults() as $hit) { ?>
    <?php $doc = $hit->getData(); ?>
    <?php $authorizedFormOfName = get_search_i18n(
        $doc,
        'authorizedFormOfName',
        ['allowEmpty' => false, 'culture' => $selectedCulture]
    ); ?>

    <div class="col-sm-6 col-lg-4 masonry-item">
      <div class="card">
        <?php if (file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/conf/logo.png')) { ?>
          <a href="<?php echo url_for(['module' => 'repository', 'slug' => $doc['slug']]); ?>">
            <?php echo image_tag('/uploads/r/'.$doc['slug'].'/conf/logo.png', [
                'alt' => strip_markdown($authorizedFormOfName),
                'class' => 'card-img-top',
            ]); ?>
          </a>
        <?php } else { ?>
          <a class="p-3" href="<?php echo url_for(['module' => 'repository', 'slug' => $doc['slug']]); ?>">
            <?php echo render_title($authorizedFormOfName); ?>
          </a>
        <?php } ?>

        <div class="card-body">
          <div class="card-text d-flex align-items-start gap-2">
            <span><?php echo render_title($authorizedFormOfName); ?></span>
            <?php echo get_component('clipboard', 'button', [
                'slug' => $doc['slug'],
                'wide' => false,
                'type' => 'repository',
            ]); ?>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>

</div>
