<section id="newest-additions" class="card mb-3">
  <h2 class="h5 p-3 mb-0"><?php echo __('Newest additions'); ?></h2>
  <div class="list-group list-group-flush">
    <?php
      // Fetch the 5 most recent records by creation date
      $criteria = new Criteria;
      $criteria->addDescendingOrderByColumn(QubitInformationObject::CREATED_AT);
      $criteria->setLimit(5);
      $recent = QubitInformationObject::get($criteria);

      if (count($recent)) {
        foreach ($recent as $object) { ?>
          <a class="list-group-item list-group-item-action text-break"
             href="<?php echo url_for([$object]); ?>">
            <?php echo render_title($object); ?>
          </a>
        <?php }
      } else { ?>
        <div class="list-group-item text-muted">
          <?php echo __('No recent descriptions.'); ?>
        </div>
      <?php } ?>
  </div>
</section>
