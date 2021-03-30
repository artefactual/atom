<section class="actions">
  <ul>
    <?php if (QubitInformationObject::ROOT_ID != $resource->id) { ?>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'c-btn']); ?></li>
      <?php if (isset($sf_request->parent)) { ?>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
      <?php } else { ?>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      <?php } ?>
    <?php } else { ?>
      <li><?php echo link_to(__('Cancel'), ['module' => 'informationobject', 'action' => 'browse'], ['class' => 'c-btn']); ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
    <?php } ?>
  </ul>
</section>
