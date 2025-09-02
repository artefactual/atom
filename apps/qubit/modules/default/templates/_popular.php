<section id="popular-this-week" class="card mb-3">
  <h2 class="h5 p-3 mb-0">
    <?php echo __('Popular this week'); ?>
  </h2>
  <div class="list-group list-group-flush">
    <?php foreach ($popularThisWeek as $item) { ?>
      <?php $object = QubitObject::getById($item[0]); ?>
      <a
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-break"
        href="<?php echo url_for([$object]); ?>">
        <?php echo render_title($object); ?>
        <span class="ms-3 text-nowrap">
          <?php echo __('%1% visits', ['%1%' => $item[1]]); ?>
        </span>
      </a>
    <?php } ?>
  </div>
</section>
