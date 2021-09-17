<ul class="actions mb-3 nav gap-2">
  <?php if (QubitInformationObject::ROOT_ID != $resource->id) { ?>
    <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    <?php if (isset($sf_request->parent)) { ?>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
    <?php } else { ?>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    <?php } ?>
  <?php } else { ?>
    <li><?php echo link_to(__('Cancel'), ['module' => 'informationobject', 'action' => 'browse'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
  <?php } ?>
</ul>
