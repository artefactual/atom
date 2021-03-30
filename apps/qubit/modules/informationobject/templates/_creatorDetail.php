<?php $actorsShown = []; ?>
<?php foreach ($ancestor->getCreators() as $item) { ?>
  <?php if (!isset($actorsShown[$item->id])) { ?>
    <div class="field">
      <h3><?php echo __('Name of creator'); ?></h3>
      <div>

        <div class="creator">
          <?php if (0 < count($resource->getCreators())) { ?>
            <?php echo link_to(render_title($item), [$item]); ?>
          <?php } else { ?>
            <?php echo link_to(render_title($item), [$item], ['title' => __('Inherited from %1%', ['%1%' => $ancestor])]); ?>
          <?php } ?>
        </div>

        <?php if (isset($item->datesOfExistence)) { ?>
          <div class="datesOfExistence">
            (<?php echo render_value_inline($item->getDatesOfExistence(['cultureFallback' => true])); ?>)
          </div>
        <?php } ?>

        <?php if (0 < count($resource->getCreators())) { ?>
          <div class="field">
            <?php if (QubitTerm::CORPORATE_BODY_ID == $item->entityTypeId) { ?>
              <?php $history_kind = __('Administrative history'); ?>
            <?php } else { ?>
              <?php $history_kind = __('Biographical history'); ?>
            <?php } ?>
            <h3><?php echo $history_kind; ?></h3>
            <div class="history">
              <?php echo render_value($item->getHistory(['cultureFallback' => true])); ?>
            </div>
          </div>
        <?php } ?>

      </div>
    </div>
    <?php $actorsShown[$item->id] = true; ?>
  <?php } ?>
<?php } ?>
