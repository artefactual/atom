<div
  class="accordion"
  id="atom-digital-object-carousel"
  data-carousel-instructions-text-text-link="<?php echo __('Clicking this description title link will open the description view page for this digital object. Advancing the carousel above will update this title text.'); ?>"
  data-carousel-instructions-text-image-link="<?php echo __('Changing the current slide of this carousel will change the description title displayed in the following carousel. Clicking any image in this carousel will open the related description view page.'); ?>"
  data-carousel-next-arrow-button-text="<?php echo __('Next'); ?>"
  data-carousel-prev-arrow-button-text="<?php echo __('Previous'); ?>"
  data-carousel-images-region-label="<?php echo __('Archival description images carousel'); ?>"
  data-carousel-title-region-label="<?php echo __('Archival description title link'); ?>">
  <div class="accordion-item border-0">
    <h2 class="accordion-header rounded-0 rounded-top border border-bottom-0" id="heading-carousel">
      <button class="accordion-button rounded-0 rounded-top text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-carousel" aria-expanded="true" aria-controls="collapse-carousel">
        <span><?php echo __('Image carousel'); ?></span>
      </button>
    </h2>
    <div id="collapse-carousel" class="accordion-collapse collapse show" aria-labelledby="heading-carousel">
      <div class="accordion-body bg-secondary px-5 pt-4 pb-3">
        <div id="atom-slider-images" class="mb-0">
          <?php foreach ($thumbnails as $item) { ?>
            <a title="<?php echo $item->parent->object; ?>" href="<?php echo url_for([$item->parent->object, 'module' => 'informationobject']); ?>">
              <?php echo image_tag($item->getFullPath(), ['class' => 'img-thumbnail mx-2', 'longdesc' => url_for([$item->parent->object, 'module' => 'informationobject']), 'alt' => strip_markdown($item->getDigitalObjectAltText() ?: $item->parent->object)]); ?>
            </a>
          <?php } ?>
        </div>

        <div id="atom-slider-title">
          <?php foreach ($thumbnails as $item) { ?>
            <a href="<?php echo url_for([$item->parent->object, 'module' => 'informationobject']); ?>" class="text-white text-center mt-2 mb-1">
              <?php echo strip_markdown($item->parent->object); ?>
            </a>
          <?php } ?>
        </div>

        <?php if (isset($limit) && $limit < $total) { ?>
          <div class="text-white text-center mt-2 mb-1">
            <?php echo __('Results %1% to %2% of %3%', ['%1%' => 1, '%2%' => $limit, '%3%' => $total]); ?>
            <a class='btn atom-btn-outline-light btn-sm ms-2' href="<?php echo url_for([
                'module' => 'informationobject',
                'action' => 'browse',
                'ancestor' => $resource->id,
                'topLod' => false,
                'view' => 'card',
                'onlyMedia' => true, ]); ?>"><?php echo __('Show all'); ?></a>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
