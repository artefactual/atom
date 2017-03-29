<?php use_helper('Text') ?>

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
            <?php echo image_tag('/uploads/r/'.$doc['slug'].'/conf/logo.png', array('alt' => esc_entities(render_title(truncate_text(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $selectedCulture)), 100))))) ?>
          </div>
        <?php else: ?>
          <h4><?php echo $authorizedFormOfName ?></h4>
        <?php endif; ?>
      </a>
      <div class="bottom">
        <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false, 'repositoryOrDigitalObjBrowse' => true)) ?><?php echo $authorizedFormOfName ?>
      </div>
    </div>
  <?php endforeach; ?>

</section>
