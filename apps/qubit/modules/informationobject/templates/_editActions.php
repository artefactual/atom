<section class="actions">
  <ul>
    <?php if ($resource->id != QubitInformationObject::ROOT_ID): ?>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      <?php if (isset($sf_request->parent)): ?>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
      <?php else: ?>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      <?php endif; ?>
    <?php else: ?>
      <li><?php echo link_to(__('Cancel'), array('module' => 'informationobject', 'action' => 'browse'), array('class' => 'c-btn')) ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
    <?php endif; ?>
  </ul>
</section>
