<div class="field">
    <h3><?php echo __('File UUID'); ?></h3>
    <div class="aip-download">
        <?php echo render_value_inline($resource->object->objectUUID); ?>
        <?php if ($sf_user->checkModuleActionAccess('arStorageService', 'extractFile')) { ?>
          <a href="<?php echo url_for([$resource, 'module' => 'arStorageService', 'action' => 'extractFile']); ?>" target="_blank">
            <i class="fa fa-download"></i>
            <?php echo __('Download file'); ?>
          </a>
        <?php } ?>
    </div>
</div>

<div class="field">
    <h3><?php echo __('AIP UUID'); ?></h3>
    <div class="aip-download">
        <?php echo render_value_inline($resource->object->aipUUID); ?>
        <?php if ($sf_user->checkModuleActionAccess('arStorageService', 'download')) { ?>
          <a href="<?php echo url_for([$resource, 'module' => 'arStorageService', 'action' => 'download']); ?>" target="_blank">
            <i class="fa fa-download"></i>
            <?php echo __('Download AIP'); ?>
          </a>
        <?php } ?>
    </div>
</div>
