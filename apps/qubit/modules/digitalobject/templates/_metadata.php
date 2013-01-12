<?php use_helper('Date') ?>

<section>

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object metadata').'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

  <?php if (!QubitAcl::check($resource->informationObject, 'readReference')): ?>
    <?php echo render_show(__('Access'), __('Restricted')) ?>
  <?php endif; ?>

  <?php if (QubitAcl::check($resource->informationObject, 'readMaster')): ?>
    <?php if (QubitTerm::EXTERNAL_URI_ID == $resource->usageId): ?>
      <?php if (check_field_visibility('app_element_visibility_digital_object_url')): ?>
        <?php echo render_show(__('URL'), render_value($resource->path)) ?>
      <?php endif; ?>
    <?php else: ?>
      <?php if (check_field_visibility('app_element_visibility_digital_object_file_name')): ?>
        <?php echo render_show(__('Filename'), $resource->name) ?>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_media_type')): ?>
    <?php echo render_show(__('Media type'), $resource->mediaType) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_mime_type')): ?>
    <?php echo render_show(__('Mime-type'), $resource->mimeType) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_file_size')): ?>
    <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize)) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_uploaded')): ?>
    <?php echo render_show(__('Uploaded'), format_date($resource->createdAt, 'f')) ?>
  <?php endif; ?>

  <?php if ($sf_user->isAuthenticated()): ?>
    <?php echo render_show(__('Object UUID'), $resource->informationObject->objectUUID) ?>
    <?php echo render_show(__('AIP UUID'), $resource->informationObject->aipUUID) ?>
  <?php endif; ?>

</section>
