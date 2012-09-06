<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (isset($resource->id)): ?>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      <?php else: ?>
        <?php if (isset($sf_request->parent)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource->parent, 'module' => 'informationobject')) ?></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'informationobject', 'action' => 'list')) ?></li>
        <?php endif; ?>
        <li><input class="form-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
