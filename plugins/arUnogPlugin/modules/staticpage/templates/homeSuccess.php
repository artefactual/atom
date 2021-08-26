<?php decorate_with('layout_1col'); ?>

<?php slot('pre'); ?>
  <div class="jumbotron py-md-3 py-lg-5">
    <div class="container-xl">
      <div class="row">
        <div class="col-sm-6 col-md-5 col-lg-4">
          <div class="browse-menu p-2 p-md-3 bg-primary">
            <h2 class="text-white mb-2"><?php echo __('Browse by'); ?></h2>
            <div class="list-group">
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
  <div class="col-12">
    <div class="sign-up text-white d-flex align-items-center px-3 py-2 py-sm-3 bg-unog-red">
      <i class="fa fa-envelope me-3 fs-5" aria-hidden="true"></i>
      <a href="#" target="_blank" class="text-white fs-5">
        <?php echo __('Sign up to receive tips on how to start your search on UN Archives Geneva fonds and collections'); ?>
      </a>
    </div>
  </div>
</div>

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
    <div class="d-flex flex-column flex-grow-1 gap-3">
      <div class="ask-an-archivist d-flex flex-grow-1">
        <a class="d-flex flex-column flex-grow-1 text-white text-center justify-content-center p-3 bg-dark-50" href="https://ask.unog.ch/archives">
          <span class="d-block fs-2"><?php echo __('Ask an Archivist'); ?></span>
          <span class="d-block fs-4"><?php echo __('Help your for your research'); ?></span>
        </a>
      </div>
      <div class="research-guides d-flex flex-grow-1 bg-unog-red">
        <a class="d-flex flex-column flex-grow-1 text-white text-center justify-content-center p-3" href="#">
          <span class="d-block fs-1"><?php echo __('View our Research Guides'); ?><i class="ms-2 fa fa-chevron-circle-right" aria-hidden="true"></i></span>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8">
    <div class="featured-project mb-3 m-md-0">
      <a class="" href="https://lontad-project.unog.ch/">
        <figure class="position-relative m-0">
          <figcaption class="position-absolute w-100 text-white bg-dark-50 p-3">
            <span class="d-block fs-3"><?php echo __('Featured Project'); ?></span>
            <span class="d-block fs-3"><?php echo __('Total Digital Access to the League of Nations Archives Project (LONTAD)'); ?></span>
          </figcaption>
          <?php echo image_tag('/plugins/arUnogPlugin/images/featured-project.jpg', ['class' => 'img-fluid', 'alt' => __('XXX Alt of the featured project image')]); ?>
        </figure>
      </a>
    </div>
  </div>
  <div class="col-md-4 d-flex">
    <div class="d-flex flex-grow-1">
      <div class="visit-the-archives d-flex flex-grow-1">
        <a class="d-flex flex-column flex-grow-1 text-white text-center justify-content-center p-3 bg-dark-50" href="https://www.ungeneva.org/en/knowledge/archives">
          <span class="d-block fs-2"><?php echo __('Visit the Archives'); ?></span>
          <span class="d-block fs-4"><?php echo __('Hours and bookings'); ?></span>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row mb-3">
  <div id="unogCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <?php $counter = 0; ?>
      <?php foreach ($carouselItems as $item) { ?>
        <button type="button" data-bs-target="#unogCarousel" data-bs-slide-to="<?php echo $counter; ?>" <?php if (0 == $counter) { ?>class="active" aria-current="true"<?php }?> aria-label="<?php echo $item[$culture]; ?>"></button>
        <?php ++$counter; ?>
      <?php } ?>
    </div>
    <div class="carousel-inner">
      <?php $counter = 0; ?>
      <?php foreach ($carouselItems as $i => $item) { ?>
        <div class="carousel-item carousel-item-image position-relative bg-dark-75<?php if ('carousel-01.jpg' == $i) { ?> active<?php } ?>">
          <?php echo image_tag('/plugins/arUnogPlugin/carousel/'.$i, ['alt' => $item[$culture]]); ?>
          <div class="carousel-caption bg-dark-75">
            <span class="d-block h5"><?php echo __('Featured Archive'); ?></span>
            <span class="d-block h5"><?php echo $item[$culture]; ?></span>
          </div>
        </div>
        <?php ++$counter; ?>
      <?php } ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#unogCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#unogCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</div>

<?php slot('post'); ?>
  <?php if (count($mostPopularLastMonth)) { ?>
    <h2 class="text-center">
      <?php echo __("See this month's most visited images in our %1%", ['%1%' => link_to(__('Photographs Collection'), ['module' => 'informationobject', 'slug' => $ribbonCollectionSlug])]); ?>
    </h2>
    <div class="popular-last-month d-flex border-top border-bottom border-unog-red border-4">
      <?php foreach ($mostPopularLastMonth as $item) { ?>
        <div class="carousel-item-image">
          <?php echo link_to(image_tag($item['path'].DIRECTORY_SEPARATOR.$item['name'], ['class' => 'd-block-inline border-end border-unog-red border-4']), ['module' => 'informationobject', 'slug' => $item['slug']]); ?>
        </div>
      <?php } ?>
    </div>
  <?php } ?>
<?php end_slot(); ?>
