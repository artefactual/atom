<?php use_helper('Text') ?>

<section class="masonry">

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <?php $authorizedFormOfName = get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $selectedCulture)) ?>
    <?php $hasLogo = file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/conf/logo.png') ?>
    <?php if ($hasLogo): ?>
      <div class="brick">
    <?php else: ?>
      <div class="brick brick-only-text">
    <?php endif; ?>
      <a href="<?php echo url_for(array('module' => 'repository', 'slug' => $doc['slug'])) ?>">
        <?php if ($hasLogo): ?>
          <div class="preview">
            <?php echo image_tag('/uploads/r/'.$doc['slug'].'/conf/logo.png',
                  array('alt' => truncate_text(strip_markdown($authorizedFormOfName), 100))) ?>
          </div>
        <?php else: ?>
          <h5><?php echo render_title($authorizedFormOfName) ?></h5>
        <?php endif; ?>
      </a>
      <div class="bottom">
        <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false, 'repositoryOrDigitalObjBrowse' => true)) ?><?php echo render_title($authorizedFormOfName) ?>
      </div>
    </div>
  <?php endforeach; ?>

</section>
