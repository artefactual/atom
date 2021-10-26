<?php decorate_with('layout_1col'); ?>

<?php slot('pre'); ?>
  <div class="jumbotron py-md-3 py-lg-5">
    <div class="container-xl">
      <div class="row">
        <div class="col-sm-6 col-md-5 col-lg-4">
          <div class="browse-menu p-2 p-md-3 bg-primary">
            <h2 class="text-white mb-2"><?php echo __('Browse'); ?></h2>
            <div class="list-group rounded-0">
              <?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID); ?>
              <?php if ($browseMenu->hasChildren()) { ?>
                <?php foreach ($browseMenu->getChildren() as $item) { ?>
                  <a class="list-group-item list-group-item-action bg-primary p-1 px-xl-3 py-xl-2 text-center" href="<?php echo url_for($item->getPath(['getUrl' => true, 'resolveAlias' => true])); ?>">
                    <?php echo esc_specialchars($item->getLabel(['cultureFallback' => true])); ?>
                  </a>
                <?php } ?>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-7 col-lg-8 col-xl-7 offset-xl-1">
          <h1 class="display-5 mt-3 mt-sm-0"><?php echo render_title($resource->getTitle(['cultureFallback' => true])); ?></h1>
          <?php echo get_component('search', 'box'); ?>
        </div>
      </div>
    </div>
  </div>
<?php end_slot(); ?>

<div class="row mb-3">
  <div class="col-md-8">
    <div class="page p-3 p-sm-4 bg-unog-beige mb-3 mb-md-0">
      <?php echo render_value_html($sf_data->getRaw('content')); ?>
      <?php if (QubitAcl::check($resource, 'update')) { ?>
        <section class="actions">
          <?php echo link_to(__('Edit'), [$resource, 'module' => 'staticpage', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?>
        </section>
      <?php } ?>
    </div>
  </div>
  <div class="col-md-4 d-flex">
    <div class="d-flex flex-column flex-grow-1">
      <div class="d-flex flex-grow-1 border border-2 sign-up">
        <a class="d-flex flex-column flex-grow-1 text-white text-center justify-content-center p-3 text-decoration-none" href="<?php echo url_for(['module' => 'staticpages', 'slug' => 'newsletter']); ?>" target="_blank">
          <span class="d-block fs-3"><i class="fa fa-envelope me-2" aria-hidden="true"></i><?php echo __('Sign up to be part of'); ?></span>
          <span class="d-block fs-3"><?php echo __('our research community'); ?></span>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8">
    <div class="mb-3 m-md-0 spotlight">
      <a class="" href="https://lontad-project.unog.ch/">
        <figure class="m-0">
          <figcaption class="w-100 text-white p-3 bg-secondary">
            <span class="d-block fs-3"><?php echo __('Spotlight'); ?></span>
            <span class="d-block fs-3"><?php echo __('Total Digital Access to the League of Nations Archives Project (LONTAD)'); ?></span>
          </figcaption>
          <?php echo image_tag('/plugins/arUnogPlugin/images/spotlight.jpg', ['class' => 'img-fluid d-none d-sm-block', 'alt' => __('View of the red seals of the Locarno agreements.')]); ?>
        </figure>
      </a>
    </div>
  </div>
  <div class="col-md-4 d-flex">
      <div class="d-flex flex-grow-1 border border-2 border-unog-red">
        <a class="d-flex flex-column flex-grow-1 text-dark text-center justify-content-center p-3 text-decoration-none" href="https://libraryresources.unog.ch/">
          <span class="d-block fs-3"><?php echo __('View our Research Guides'); ?></span>
          <span class="d-block fs-3"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i></span>
        </a>
      </div>
  </div>
</div>

<div id="unog-carousel" class="row mb-3 g-0">
  <div class="col-md-8">
    <div id="unog-slider-images">
      <?php foreach ($carouselItems as $i => $item) { ?>
        <div>
          <?php if (!empty($item[$culture]['url'])) { ?><a href="<?php echo $item[$culture]['url'] ?>" class="text-white text-decoration-none"><?php } ?>
            <?php echo image_tag('/plugins/arUnogPlugin/carousel/'.$i, ['class' => '', 'alt' => strip_markdown($item[$culture]['title'])]); ?>
          <?php if (!empty($item[$culture]['url'])) { ?></a><?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
  <div class="col-md-4">
    <div class="px-5 py-3 p-md-5">
      <div id="unog-slider-title">
        <?php foreach ($carouselItems as $i => $item) { ?>
          <div class="mb-5 fs-6 text-white">
            <?php if (!empty($item[$culture]['url'])) { ?><a href="<?php echo $item[$culture]['url'] ?>" class="text-white text-decoration-none"><?php } ?>
              <?php echo strip_markdown($item[$culture]['title']); ?>
            <?php if (!empty($item[$culture]['url'])) { ?></a><?php } ?>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
