<?php
/**
 * IIIF Carousel Component Template
 * 
 * Available variables:
 * - $images: Array of IIIF image data
 * - $carouselId: Unique ID for this carousel instance
 * - $autoRotate: Boolean for auto-rotation
 * - $rotateInterval: Milliseconds between rotations
 * - $showNavigation: Boolean for navigation controls
 * - $showThumbnails: Boolean for thumbnail strip
 * - $viewerHeight: Height of viewer in pixels
 */
?>

<?php if (!empty($images)): ?>
  <div class="iiif-carousel-wrapper" id="<?php echo $carouselId ?>-wrapper">
    
    <div 
      class="iiif-carousel-container"
      id="<?php echo $carouselId ?>"
      data-iiif-carousel
      data-iiif-images='<?php echo json_encode(array_map(function($img) { return $img['url']; }, $images)) ?>'
      data-auto-rotate="<?php echo $autoRotate ? 'true' : 'false' ?>"
      data-rotate-interval="<?php echo $rotateInterval ?>"
      data-show-navigation="<?php echo $showNavigation ? 'true' : 'false' ?>"
      data-show-thumbnails="<?php echo $showThumbnails ? 'true' : 'false' ?>"
      data-viewer-height="<?php echo $viewerHeight ?>">
      
      <!-- OpenSeadragon viewer container -->
      <div class="iiif-viewer-container" style="height: <?php echo $viewerHeight ?>px;">
        <div class="iiif-loading-message">
          <i class="fa fa-spinner fa-spin"></i>
          <?php echo __('Loading images...') ?>
        </div>
      </div>

      <?php if ($showNavigation): ?>
        <!-- Navigation controls -->
        <div class="iiif-carousel-nav">
          <button class="iiif-nav-btn iiif-prev" 
                  aria-label="<?php echo __('Previous image') ?>"
                  title="<?php echo __('Previous image') ?>">
            <i class="fa fa-chevron-left"></i>
          </button>

          <div class="iiif-carousel-counter">
            <span class="current-slide">1</span>
            <span class="counter-separator">/</span>
            <span class="total-slides"><?php echo count($images) ?></span>
          </div>

          <button class="iiif-nav-btn iiif-next" 
                  aria-label="<?php echo __('Next image') ?>"
                  title="<?php echo __('Next image') ?>">
            <i class="fa fa-chevron-right"></i>
          </button>

          <button class="iiif-play-pause" 
                  aria-label="<?php echo __('Toggle autoplay') ?>"
                  title="<?php echo __('Toggle autoplay') ?>">
            <i class="fa fa-pause"></i>
          </button>
        </div>
      <?php endif; ?>

      <?php if ($showThumbnails && count($images) > 1): ?>
        <!-- Thumbnail strip -->
        <div class="iiif-carousel-thumbnails">
          <?php foreach ($images as $index => $image): ?>
            <?php
              // Generate thumbnail URL from IIIF image URL
              $thumbnailUrl = str_replace('/info.json', '/full/150,/0/default.jpg', $image['url']);
            ?>
            <div class="iiif-thumbnail <?php echo $index === 0 ? 'active' : '' ?>" 
                 data-index="<?php echo $index ?>"
                 title="<?php echo htmlspecialchars(isset($image['label']) ? $image['label'] : '') ?>">
              <img src="<?php echo $thumbnailUrl ?>" 
                   alt="<?php echo htmlspecialchars(isset($image['label']) ? $image['label'] : 'Image ' . ($index + 1)) ?>"
                   loading="lazy" />
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>

  </div>

<?php else: ?>
  <!-- No images available -->
  <div class="iiif-no-images alert alert-info">
    <i class="fa fa-info-circle"></i>
    <?php echo __('No IIIF images available for this resource.') ?>
  </div>
<?php endif; ?>
