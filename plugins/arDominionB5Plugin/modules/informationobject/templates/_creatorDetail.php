<?php $actorsShown = []; ?>
<?php foreach ($ancestor->getCreators() as $item) { ?>
  <?php if (!isset($actorsShown[$item->id])) { ?>
    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(__('Name of creator')); ?>
      <div class="<?php echo render_b5_show_value_css_classes(); ?>">

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
          <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
            <?php if (QubitTerm::CORPORATE_BODY_ID == $item->entityTypeId) { ?>
              <?php $history_kind = __('Administrative history'); ?>
            <?php } else { ?>
              <?php $history_kind = __('Biographical history'); ?>
            <?php } ?>
            <?php echo render_show($history_kind, render_value($item->getHistory(['cultureFallback' => true])), ['isSubField' => true]); ?>
          </div>
        <?php } ?>

      </div>
    </div>
    <?php $actorsShown[$item->id] = true; ?>
  <?php } ?>
<?php } ?>
