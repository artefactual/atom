<?php $actorsShown = array(); ?>
<?php foreach ($ancestor->getCreators() as $item): ?>
  <?php if (!isset($actorsShown[$item->id])): ?>
    <div class="field">
      <h3><?php echo __('Name of creator') ?></h3>
      <div>

        <div class="creator">
          <?php if (0 < count($resource->getCreators())): ?>
            <?php echo link_to(render_title($item), array($item)) ?>
          <?php else: ?>
            <?php echo link_to(render_title($item), array($item), array('title' => __('Inherited from %1%', array('%1%' => $ancestor)))) ?>
          <?php endif; ?>
        </div>

        <?php if (isset($item->datesOfExistence)): ?>
          <div class="datesOfExistence">
            (<?php echo $item->getDatesOfExistence(array('cultureFallback' => true)) ?>)
          </div>
        <?php endif; ?>

        <?php if (0 < count($resource->getCreators())): ?>
          <div class="field">
            <?php if (QubitTerm::CORPORATE_BODY_ID == $item->entityTypeId): ?>
              <?php $history_kind = __('Administrative history'); ?>
            <?php else: ?>
              <?php $history_kind = __('Biographical history'); ?>
            <?php endif; ?>
            <h3><?php echo $history_kind; ?></h3>
            <div class="history">
              <?php echo render_value($item->getHistory(array('cultureFallback' => true))) ?>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>
    <?php $actorsShown[$item->id] = true; ?>
  <?php endif; ?>
<?php endforeach; ?>
