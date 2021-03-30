<?php use_helper('Date'); ?>

<section>

  <?php if ($relatedToIo) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationobject'), '<h2>'.__('%1% metadata', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]).'</h2>', [$resource, 'module' => 'digitalobject', 'action' => 'edit'], ['title' => __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))])]); ?>
  <?php } elseif ($relatedToActor) { ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor'), '<h2>'.__('%1% metadata', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]).'</h2>', [$resource, 'module' => 'digitalobject', 'action' => 'edit'], ['title' => __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))])]); ?>
  <?php } ?>

  <?php if ($showOriginalFileMetadata || $showPreservationCopyMetadata) { ?>

    <fieldset class="collapsible digital-object-metadata single">
      <legend><?php echo __('Preservation Copies'); ?></legend>

      <?php if ($showOriginalFileMetadata) { ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Original file'); ?> <i class="fa fa-archive<?php if (!$canAccessOriginalFile) { ?> inactive<?php } ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showOriginalFileName) { ?>
            <?php echo render_show(__('Filename'), render_value($resource->object->originalFileName), ['fieldLabel' => 'originalFileName']); ?>
          <?php } ?>

          <?php if ($showOriginalFormatName) { ?>
            <?php echo render_show(__('Format name'), render_value($resource->object->formatName), ['fieldLabel' => 'originalFileFormatName']); ?>
          <?php } ?>

          <?php if ($showOriginalFormatVersion) { ?>
            <?php echo render_show(__('Format version'), render_value($resource->object->formatVersion), ['fieldLabel' => 'originalFileFormatVersion']); ?>
          <?php } ?>

          <?php if ($showOriginalFormatRegistryKey) { ?>
            <?php echo render_show(__('Format registry key'), render_value($resource->object->formatRegistryKey), ['fieldLabel' => 'originalFileFormatRegistryKey']); ?>
          <?php } ?>

          <?php if ($showOriginalFormatRegistryName) { ?>
            <?php echo render_show(__('Format registry name'), render_value($resource->object->formatRegistryName), ['fieldLabel' => 'originalFileFormatRegistryName']); ?>
          <?php } ?>

          <?php if ($showOriginalFileSize) { ?>
            <?php echo render_show(__('Filesize'), hr_filesize(intval((string) $resource->object->originalFileSize)), ['fieldLabel' => 'originalFileSize']); ?>
          <?php } ?>

          <?php if ($showOriginalFileIngestedAt) { ?>
            <?php echo render_show(__('Ingested'), format_date($originalFileIngestedAt, 'f'), ['fieldLabel' => 'originalFileIngestedAt']); ?>
          <?php } ?>

          <?php if ($showOriginalFilePermissions) { ?>
            <?php echo render_show(__('Permissions'), render_value($accessStatement), ['fieldLabel' => 'originalFilePermissions']); ?>
          <?php } ?>

          <?php if ($sf_user->isAuthenticated() && $relatedToIo) { ?>
            <?php if ($storageServicePluginEnabled) { ?>
              <?php include_partial(
                'arStorageService/aipDownload', ['resource' => $resource]
              ); ?>
            <?php } else { ?>
              <?php echo render_show(
                __('File UUID'),
                render_value($resource->object->objectUUID),
                ['fieldLabel' => 'objectUUID']
              ); ?>
              <?php echo render_show(
                __('AIP UUID'),
                render_value($resource->object->aipUUID),
                ['fieldLabel' => 'aipUUID']
              ); ?>
            <?php } ?>
          <?php } ?>

        </div>

      <?php } ?>

      <?php if ($showPreservationCopyMetadata) { ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Preservation copy'); ?> <i class="fa fa-archive<?php if (!$canAccessPreservationCopy) { ?> inactive<?php } ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showPreservationCopyFileName) { ?>
            <?php echo render_show(__('Filename'), render_value($resource->object->preservationCopyFileName), ['fieldLabel' => 'preservationCopyFileName']); ?>
          <?php } ?>

          <?php if ($showPreservationCopyFileSize) { ?>
            <?php echo render_show(__('Filesize'), hr_filesize(intval((string) $resource->object->preservationCopyFileSize)), ['fieldLabel' => 'preservationCopyFileSize']); ?>
          <?php } ?>

          <?php if ($showPreservationCopyNormalizedAt) { ?>
            <?php echo render_show(__('Normalized'), format_date($preservationCopyNormalizedAt, 'f'), ['fieldLabel' => 'preservactionCopyNormalizedAt']); ?>
          <?php } ?>

          <?php if ($showPreservationCopyPermissions) { ?>
            <?php echo render_show(__('Permissions'), render_value($accessStatement), ['fieldLabel' => 'preservationCopyPermissions']); ?>
          <?php } ?>

        </div>

      <?php } ?>

    </fieldset>

  <?php } ?>

  <?php if ($showMasterFileMetadata || $showReferenceCopyMetadata || $showThumbnailCopyMetadata) { ?>

    <fieldset class="collapsible digital-object-metadata single">
      <legend><?php echo __('Access Copies'); ?></legend>

      <?php if ($showMasterFileMetadata) { ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Master file'); ?> <i class="fa fa-file<?php if (!$canAccessMasterFile) { ?> inactive<?php } ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showMasterFileGoogleMap) { ?>
            <div id="front-map" class="simple-map" data-key="<?php echo $googleMapsApiKey; ?>" data-latitude="<?php echo $latitude; ?>" data-longitude="<?php echo $longitude; ?>"></div>
          <?php } ?>

          <?php if ($showMasterFileGeolocation) { ?>
            <?php echo render_show(__('Latitude'), render_value($latitude), ['fieldLabel' => 'latitude']); ?>
            <?php echo render_show(__('Longitude'), render_value($longitude), ['fieldLabel' => 'longitude']); ?>
          <?php } ?>

          <?php if ($showMasterFileURL) { ?>
            <?php echo render_show(__('URL'), render_value($resource->path), ['fieldLabel' => 'url']); ?>
          <?php } ?>

          <?php if ($showMasterFileName) { ?>
            <?php if ($canAccessMasterFile) { ?>
              <?php echo render_show(__('Filename'), link_to(render_value_inline($resource->name), $resource->object->getDigitalObjectUrl(), ['target' => '_blank']), ['fieldLabel' => 'filename']); ?>
            <?php } else { ?>
              <?php echo render_show(__('Filename'), render_value($resource->name), ['fieldLabel' => 'filename']); ?>
            <?php } ?>
          <?php } ?>

          <?php if ($showMasterFileMediaType) { ?>
            <?php echo render_show(__('Media type'), render_value($resource->mediaType), ['fieldLabel' => 'mediaType']); ?>
          <?php } ?>

          <?php if ($showMasterFileMimeType) { ?>
            <?php echo render_show(__('Mime-type'), render_value($resource->mimeType), ['fieldLabel' => 'mimeType']); ?>
          <?php } ?>

          <?php if ($showMasterFileSize) { ?>
            <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize), ['fieldLabel' => 'filesize']); ?>
          <?php } ?>

          <?php if ($showMasterFileCreatedAt) { ?>
            <?php echo render_show(__('Uploaded'), format_date($resource->createdAt, 'f'), ['fieldLabel' => 'uploaded']); ?>
          <?php } ?>

          <?php if ($showMasterFilePermissions) { ?>
            <?php echo render_show(__('Permissions'), render_value($masterFileDenyReason), ['fieldLabel' => 'masterFilePermissions']); ?>
          <?php } ?>

        </div>

      <?php } ?>

      <?php if ($showReferenceCopyMetadata) { ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Reference copy'); ?> <i class="fa fa-file<?php if (!$canAccessReferenceCopy) { ?> inactive<?php } ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showReferenceCopyFileName) { ?>
            <?php if ($canAccessReferenceCopy && $sf_user->isAuthenticated()) { ?>
              <?php echo render_show(__('Filename'), link_to(render_value_inline($referenceCopy->name), $referenceCopy->getFullPath(), ['target' => '_blank']), ['fieldLabel' => 'referenceCopyFileName']); ?>
            <?php } else { ?>
              <?php echo render_show(__('Filename'), render_value($referenceCopy->name), ['fieldLabel' => 'referenceCopyFileName']); ?>
            <?php } ?>
          <?php } ?>

          <?php if ($showReferenceCopyMediaType) { ?>
            <?php echo render_show(__('Media type'), render_value($referenceCopy->mediaType), ['fieldLabel' => 'referenceCopyFileName']); ?>
          <?php } ?>

          <?php if ($showReferenceCopyMimeType) { ?>
            <?php echo render_show(__('Mime-type'), render_value($referenceCopy->mimeType), ['fieldLabel' => 'referenceCopyMimeType']); ?>
          <?php } ?>

          <?php if ($showReferenceCopyFileSize) { ?>
            <?php echo render_show(__('Filesize'), hr_filesize($referenceCopy->byteSize), ['fieldLabel' => 'referenceCopyFileSize']); ?>
          <?php } ?>

          <?php if ($showReferenceCopyCreatedAt) { ?>
            <?php echo render_show(__('Uploaded'), format_date($referenceCopy->createdAt, 'f'), ['fieldLabel' => 'referenceCopyUploaded']); ?>
          <?php } ?>

          <?php if ($showReferenceCopyPermissions) { ?>
            <?php echo render_show(__('Permissions'), render_value($referenceCopyDenyReason), ['fieldLabel' => 'referenceCopyPermissions']); ?>
          <?php } ?>

        </div>

      <?php } ?>

      <?php if ($showThumbnailCopyMetadata) { ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Thumbnail copy'); ?> <i class="fa fa-file<?php if (!$canAccessThumbnailCopy) { ?> inactive<?php } ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showThumbnailCopyFileName) { ?>
            <?php if ($canAccessThumbnailCopy) { ?>
              <?php echo render_show(__('Filename'), link_to(render_value_inline($thumbnailCopy->name), $thumbnailCopy->getFullPath(), ['target' => '_blank']), ['fieldLabel' => 'thumbnailCopyFileName']); ?>
            <?php } else { ?>
              <?php echo render_show(__('Filename'), render_value($thumbnailCopy->name), ['fieldLabel' => 'thumbnailCopyFileName']); ?>
            <?php } ?>
          <?php } ?>

          <?php if ($showThumbnailCopyMediaType) { ?>
            <?php echo render_show(__('Media type'), render_value($thumbnailCopy->mediaType), ['fieldLabel' => 'thumbnailCopyFileName']); ?>
          <?php } ?>

          <?php if ($showThumbnailCopyMimeType) { ?>
            <?php echo render_show(__('Mime-type'), render_value($thumbnailCopy->mimeType), ['fieldLabel' => 'thumbnailCopyMimeType']); ?>
          <?php } ?>

          <?php if ($showThumbnailCopyFileSize) { ?>
            <?php echo render_show(__('Filesize'), hr_filesize($thumbnailCopy->byteSize), ['fieldLabel' => 'thumbnailCopyFileSize']); ?>
          <?php } ?>

          <?php if ($showThumbnailCopyCreatedAt) { ?>
            <?php echo render_show(__('Uploaded'), format_date($thumbnailCopy->createdAt, 'f'), ['fieldLabel' => 'thumbnailCopyUploaded']); ?>
          <?php } ?>

          <?php if (!empty($thumbnailCopyDenyReason)) { ?>
            <?php echo render_show(__('Permissions'), render_value($thumbnailCopyDenyReason), ['fieldLabel' => 'thumbnailCopyPermissions']); ?>
          <?php } ?>

        </div>

      <?php } ?>

    </fieldset>

  <?php } ?>

</section>
