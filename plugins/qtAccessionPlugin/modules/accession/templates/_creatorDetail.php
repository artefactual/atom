<?php foreach ($resource->getCreators() as $item) { ?>
  <div class="field">
    <h3><?php echo __('Name of creator'); ?></h3>
    <div>

      <?php echo link_to(render_title($item), [$item]); ?>

      <?php if (isset($item->datesOfExistence)) { ?>
        (<?php echo $item->getDatesOfExistence(['cultureFallback' => true]); ?>)
      <?php } ?>

      <?php if (0 < count($resource->getCreators())) { ?>
        <div class="field">
          <h3>
            <?php if (QubitTerm::CORPORATE_BODY_ID == $item->entityTypeId) { ?>
              <?php echo __('Administrative history'); ?>
            <?php } else { ?>
              <?php echo __('Biographical history'); ?>
            <?php } ?>
          </h3><div>
            <?php echo render_value($item->getHistory(['cultureFallback' => true])); ?>
          </div>
        </div>
      <?php } ?>

    </div>
  </div>
<?php } ?>
