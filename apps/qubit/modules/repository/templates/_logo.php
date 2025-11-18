<?php use_helper('Text'); ?>

<?php if ($resource->existsLogo()) { ?>
  <div class="repository-logo mb-3 mx-auto">
    <a class="text-decoration-none" href="<?php echo url_for([$resource, 'module' => 'repository']); ?>">
      <?php echo image_tag(
        $resource->getLogoPath(),
        [
            'alt' => __('Go to %1%', ['%1%' => truncate_text(strip_markdown($resource), 100)]),
            'class' => 'img-fluid img-thumbnail border-4 shadow-sm bg-white',
        ],
      ); ?>
    </a>
  </div>
<?php } else { ?>
  <div class="repository-logo-text mb-3">
    <a class="text-decoration-none" href="<?php echo url_for([$resource, 'module' => 'repository']); ?>">
      <h2 class="h4 p-2 text-muted text-start border border-4 shadow-sm bg-white mx-auto">
        <?php echo render_title($resource); ?>
      </h2>
    </a>
  </div>
<?php } ?>
