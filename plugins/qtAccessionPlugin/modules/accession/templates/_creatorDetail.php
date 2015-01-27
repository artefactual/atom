<?php foreach ($resource->getCreators() as $item): ?>
  <div class="field">
    <h3><?php echo __('Name of creator') ?></h3>
    <div>

      <?php echo link_to(render_title($item), array($item)) ?>

      <?php if (isset($item->datesOfExistence)): ?>
        (<?php echo $item->getDatesOfExistence(array('cultureFallback' => true)) ?>)
      <?php endif; ?>

      <?php if (0 < count($resource->getCreators())): ?>
        <div class="field">
          <h3>
            <?php if (QubitTerm::CORPORATE_BODY_ID == $item->entityTypeId): ?>
              <?php echo __('Administrative history') ?>
            <?php else: ?>
              <?php echo __('Biographical history') ?>
            <?php endif; ?>
          </h3><div>
            <?php echo render_value($item->getHistory(array('cultureFallback' => true))) ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
<?php endforeach; ?>
