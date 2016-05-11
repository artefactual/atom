<?php use_helper('Date') ?>

<section>

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object metadata').'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

  <?php if (!QubitAcl::check($resource->informationObject, 'readReference')): ?>
    <?php echo render_show(__('Access'), __('Restricted')) ?>
  <?php endif; ?>

  <?php if (QubitTerm::EXTERNAL_URI_ID == $resource->usageId): ?>
    <?php if (check_field_visibility('app_element_visibility_digital_object_url')): ?>
      <?php echo render_show(__('URL'), render_value($resource->path), array('fieldLabel' => 'url')) ?>
    <?php endif; ?>
  <?php else: ?>
    <?php if (check_field_visibility('app_element_visibility_digital_object_file_name') && !$denyFileNameByPremis): ?>
      <?php echo render_show(__('Filename'), $resource->name, array('fieldLabel' => 'filename')) ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_media_type')): ?>
    <?php echo render_show(__('Media type'), $resource->mediaType, array('fieldLabel' => 'mediaType')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_mime_type')): ?>
    <?php echo render_show(__('Mime-type'), $resource->mimeType, array('fieldLabel' => 'mimeType')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_file_size')): ?>
    <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize), array('fieldLabel' => 'filesize')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_uploaded')): ?>
    <?php echo render_show(__('Uploaded'), format_date($resource->createdAt, 'f'), array('fieldLabel' => 'uploaded')) ?>
  <?php endif; ?>

  <?php if ($sf_user->isAuthenticated()): ?>
    <?php echo render_show(__('Object UUID'), $resource->informationObject->objectUUID, array('fieldLabel' => 'objectUUID')) ?>
    <?php echo render_show(__('AIP UUID'), $resource->informationObject->aipUUID, array('fieldLabel' => 'aipUUID')) ?>
  <?php endif; ?>

</section>
