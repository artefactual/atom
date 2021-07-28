<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('File UUID'), ['isSubField' => true]); ?>
  <div class="aip-download <?php echo render_b5_show_value_css_classes(['isSubField' => true]); ?>">
    <?php echo render_value_inline($resource->object->objectUUID); ?>
    <?php if ($sf_user->checkModuleActionAccess('arStorageService', 'extractFile')) { ?>
      <a href="<?php echo url_for([$resource, 'module' => 'arStorageService', 'action' => 'extractFile']); ?>" target="_blank">
        <i class="fa fa-download me-1" aria-hidden="true"></i><?php echo __('Download file'); ?>
      </a>
    <?php } ?>
  </div>
</div>

<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('AIP UUID'), ['isSubField' => true]); ?>
  <div class="aip-download <?php echo render_b5_show_value_css_classes(['isSubField' => true]); ?>">
    <?php echo render_value_inline($resource->object->aipUUID); ?>
    <?php if ($sf_user->checkModuleActionAccess('arStorageService', 'download')) { ?>
      <a href="<?php echo url_for([$resource, 'module' => 'arStorageService', 'action' => 'download']); ?>" target="_blank">
        <i class="fa fa-download me-1" aria-hidden="true"></i><?php echo __('Download AIP'); ?>
      </a>
    <?php } ?>
  </div>
</div>
