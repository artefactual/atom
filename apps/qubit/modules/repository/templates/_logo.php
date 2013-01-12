<div class="repository-logo repository-logo-text">
  <a href="<?php echo url_for(array($resource, 'module' => 'repository')) ?>">
    <?php if ($resource->existsLogo()): ?>
      <?php echo image_tag($resource->getLogoPath()) ?>
    <?php else: ?>
      <h2><?php echo render_title($resource) ?></h2>
    <?php endif; ?>
  </a>
</div>
