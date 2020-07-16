<div class="field">
    <h3><?php echo __('Object UUID') ?></h3>
    <div class="aip-download">
        <?php echo render_value_inline($resource->object->objectUUID) ?>
        <a href="<?php echo url_for(array($resource, 'module' => 'arStorageService', 'action' => 'extractFile')) ?>" target="_blank">
          <i class="fa fa-download"></i>
          <?php echo __('Download object') ?>
        </a>
    </div>
</div>

<div class="field">
    <h3><?php echo __('AIP UUID') ?></h3>
    <div class="aip-download">
        <?php echo render_value_inline($resource->object->aipUUID) ?>
        <a href="<?php echo url_for(array($resource, 'module' => 'arStorageService', 'action' => 'download')) ?>" target="_blank">
          <i class="fa fa-download"></i>
          <?php echo __('Download AIP') ?>
        </a>
    </div>
</div>
