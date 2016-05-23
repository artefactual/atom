<section class="actions">
  <ul>
    <?php if (isset($resource->id)): ?>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
    <?php else: ?>
      <?php if (isset($sf_request->parent)): ?>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      <?php else: ?>
        <li><?php echo link_to(__('Cancel'), array('module' => 'informationobject', 'action' => 'browse'), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
    <?php endif; ?>
  </ul>
</section>
