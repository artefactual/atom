<?php use_helper('Text'); ?>

<div class="repository-logo<?php echo $resource->existsLogo() ? '' : ' repository-logo-text'; ?>">
  <a href="<?php echo url_for([$resource, 'module' => 'repository']); ?>">
    <?php if ($resource->existsLogo()) { ?>
      <?php echo image_tag($resource->getLogoPath(),
            ['alt' => __('Go to %1%',
            ['%1%' => truncate_text(strip_markdown($resource), 100)])]); ?>
    <?php } else { ?>
      <h2><?php echo render_title($resource); ?></h2>
    <?php } ?>
  </a>
</div>
