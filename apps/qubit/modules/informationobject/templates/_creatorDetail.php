<?php $actorsShown = array(); ?>
<?php foreach ($ancestor->getCreators() as $item): ?>
  <?php if (!isset($actorsShown[$item->id])): ?>
    <div class="field">
      <h3><?php echo __('Name of creator') ?></h3>
      <div>

        <?php if (0 < count($resource->getCreators())): ?>
          <?php echo link_to(render_title($item), array($item)) ?>
        <?php else: ?>
          <?php echo link_to(render_title($item), array($item), array('title' => __('Inherited from %1%', array('%1%' => $ancestor)))) ?>
        <?php endif; ?>

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
    <?php $actorsShown[$item->id] = true; ?>
  <?php endif; ?>
<?php endforeach; ?>
