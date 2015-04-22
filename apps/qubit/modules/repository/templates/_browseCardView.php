<section class="masonry">

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <?php $authorizedFormOfName = render_title(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $selectedCulture))) ?>
    <?php $hasLogo = file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/conf/logo.png') ?>
    <?php if ($hasLogo): ?>
      <div class="brick">
    <?php else: ?>
      <div class="brick brick-only-text">
    <?php endif; ?>
      <a href="<?php echo url_for(array('module' => 'repository', 'slug' => $doc['slug'])) ?>">
        <?php if ($hasLogo): ?>
          <div class="preview">
            <?php echo image_tag('/uploads/r/'.$doc['slug'].'/conf/logo.png') ?>
          </div>
        <?php else: ?>
          <h4><?php echo $authorizedFormOfName ?></h4>
        <?php endif; ?>
      </a>
      <div class="bottom">
        <p><?php echo $authorizedFormOfName ?></p>
      </div>
    </div>
  <?php endforeach; ?>

</section>
