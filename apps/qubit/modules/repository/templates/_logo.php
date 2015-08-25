<?php use_helper('Text') ?>

<div class="repository-logo<?php echo $resource->existsLogo() ? '' : ' repository-logo-text' ?>">
  <a href="<?php echo url_for(array($resource, 'module' => 'repository')) ?>">
    <?php if ($resource->existsLogo()): ?>
      <?php echo image_tag($resource->getLogoPath(), array('alt' => __('Go to %1%', array('%1%' => esc_entities(render_title(truncate_text($resource), 100)))))) ?>
    <?php else: ?>
      <h2><?php echo render_title($resource) ?></h2>
    <?php endif; ?>
  </a>
</div>
