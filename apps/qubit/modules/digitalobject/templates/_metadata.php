<?php use_helper('Date') ?>

<section>

  <?php if ($resource->object instanceOf QubitInformationObject): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationobject'), '<h2>'.__('%1% metadata', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))))) ?>
  <?php elseif ($resource->object instanceOf QubitActor): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor'), '<h2>'.__('%1% metadata', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))))) ?>
  <?php endif; ?>

  <?php if (sfConfig::get('app_toggleDigitalObjectMap') && is_numeric($latitude) && is_numeric($longitude) && $googleMapsApiKey): ?>
    <div id="front-map" class="simple-map" data-key="<?php echo $googleMapsApiKey ?>" data-latitude="<?php echo $latitude ?>" data-longitude="<?php echo $longitude ?>"></div>
  <?php endif; ?>

  <?php if (!QubitAcl::check($resource->object, 'readReference')): ?>
    <?php echo render_show(__('Access'), __('Restricted')) ?>
  <?php endif; ?>

  <?php if (QubitTerm::EXTERNAL_URI_ID == $resource->usageId): ?>
    <?php if (check_field_visibility('app_element_visibility_digital_object_url')): ?>
      <?php echo render_show(__('URL'), render_value($resource->path), array('fieldLabel' => 'url')) ?>
    <?php endif; ?>
  <?php else: ?>
    <?php if (check_field_visibility('app_element_visibility_digital_object_file_name') && !$denyFileNameByPremis): ?>
      <?php echo render_show(__('Filename'), render_value($resource->name), array('fieldLabel' => 'filename')) ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_geolocation')): ?>
    <?php echo render_show(__('Latitude'), render_value($latitude), array('fieldLabel' => 'latitude')) ?>
    <?php echo render_show(__('Longitude'), render_value($longitude), array('fieldLabel' => 'longitude')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_media_type')): ?>
    <?php echo render_show(__('Media type'), render_value($resource->mediaType), array('fieldLabel' => 'mediaType')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_mime_type')): ?>
    <?php echo render_show(__('Mime-type'), render_value($resource->mimeType), array('fieldLabel' => 'mimeType')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_file_size')): ?>
    <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize), array('fieldLabel' => 'filesize')) ?>
  <?php endif; ?>

  <?php if (check_field_visibility('app_element_visibility_digital_object_uploaded')): ?>
    <?php echo render_show(__('Uploaded'), format_date($resource->createdAt, 'f'), array('fieldLabel' => 'uploaded')) ?>
  <?php endif; ?>

  <?php if ($sf_user->isAuthenticated() && $relatedToIo): ?>
    <?php echo render_show(__('Object UUID'), render_value($resource->object->objectUUID), array('fieldLabel' => 'objectUUID')) ?>
    <?php echo render_show(__('AIP UUID'), render_value($resource->object->aipUUID), array('fieldLabel' => 'aipUUID')) ?>
    <?php echo render_show(__('Format name'), render_value($resource->object->formatName), array('fieldLabel' => 'formatName')) ?>
    <?php echo render_show(__('Format version'), render_value($resource->object->formatVersion), array('fieldLabel' => 'formatVersion')) ?>
    <?php echo render_show(__('Format registry key'), render_value($resource->object->formatRegistryKey), array('fieldLabel' => 'formatRegistryKey')) ?>
    <?php echo render_show(__('Format registry name'), render_value($resource->object->formatRegistryName), array('fieldLabel' => 'formatRegistryName')) ?>
  <?php endif; ?>

</section>
