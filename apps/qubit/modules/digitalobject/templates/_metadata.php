<?php use_helper('Date') ?>

<section>

  <?php if ($relatedToIo): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationobject'), '<h2>'.__('%1% metadata', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))))) ?>
  <?php elseif ($relatedToActor): ?>
    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'actor'), '<h2>'.__('%1% metadata', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit %1%', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')))))) ?>
  <?php endif; ?>

  <?php if ($showOriginalFileMetadata || $showPreservationCopyMetadata): ?>

    <fieldset class="collapsible digital-object-metadata single">
      <legend><?php echo __('Preservation Copies') ?></legend>

      <?php if ($showOriginalFileMetadata): ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Original file') ?> <i class="fa fa-archive<?php if (!$canAccessOriginalFile): ?> inactive<?php endif; ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showOriginalFileName): ?>
            <?php echo render_show(__('Filename'), render_value($resource->object->originalFileName), array('fieldLabel' => 'originalFileName')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFormatName): ?>
            <?php echo render_show(__('Format name'), render_value($resource->object->formatName), array('fieldLabel' => 'originalFileFormatName')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFormatVersion): ?>
            <?php echo render_show(__('Format version'), render_value($resource->object->formatVersion), array('fieldLabel' => 'originalFileFormatVersion')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFormatRegistryKey): ?>
            <?php echo render_show(__('Format registry key'), render_value($resource->object->formatRegistryKey), array('fieldLabel' => 'originalFileFormatRegistryKey')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFormatRegistryName): ?>
            <?php echo render_show(__('Format registry name'), render_value($resource->object->formatRegistryName), array('fieldLabel' => 'originalFileFormatRegistryName')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFileSize): ?>
            <?php echo render_show(__('Filesize'), hr_filesize(intval((string)$resource->object->originalFileSize)), array('fieldLabel' => 'originalFileSize')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFileIngestedAt): ?>
            <?php echo render_show(__('Ingested'), format_date($originalFileIngestedAt, 'f'), array('fieldLabel' => 'originalFileIngestedAt')) ?>
          <?php endif; ?>

          <?php if ($showOriginalFilePermissions): ?>
            <?php echo render_show(__('Permissions'), render_value($accessStatement), array('fieldLabel' => 'originalFilePermissions')) ?>
          <?php endif; ?>

          <?php if ($sf_user->isAuthenticated() && $relatedToIo): ?>
            <?php if ($storageServicePluginEnabled): ?>
              <?php include_partial(
                'arStorageService/aipDownload', ['resource' => $resource]
              ) ?>
            <?php else: // arStorageService is disabled ?>
              <?php echo render_show(
                __('File UUID'),
                render_value($resource->object->objectUUID),
                array('fieldLabel' => 'objectUUID')
              ) ?>
              <?php echo render_show(
                __('AIP UUID'),
                render_value($resource->object->aipUUID),
                array('fieldLabel' => 'aipUUID')
              ) ?>
            <?php endif; // arStorageService is disabled ?>
          <?php endif; ?>

        </div>

      <?php endif; ?>

      <?php if ($showPreservationCopyMetadata): ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Preservation copy') ?> <i class="fa fa-archive<?php if (!$canAccessPreservationCopy): ?> inactive<?php endif; ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showPreservationCopyFileName): ?>
            <?php echo render_show(__('Filename'), render_value($resource->object->preservationCopyFileName), array('fieldLabel' => 'preservationCopyFileName')) ?>
          <?php endif; ?>

          <?php if ($showPreservationCopyFileSize): ?>
            <?php echo render_show(__('Filesize'), hr_filesize(intval((string)$resource->object->preservationCopyFileSize)), array('fieldLabel' => 'preservationCopyFileSize')) ?>
          <?php endif; ?>

          <?php if ($showPreservationCopyNormalizedAt): ?>
            <?php echo render_show(__('Normalized'), format_date($preservationCopyNormalizedAt, 'f'), array('fieldLabel' => 'preservactionCopyNormalizedAt')) ?>
          <?php endif; ?>

          <?php if ($showPreservationCopyPermissions): ?>
            <?php echo render_show(__('Permissions'), render_value($accessStatement), array('fieldLabel' => 'preservationCopyPermissions')) ?>
          <?php endif; ?>

        </div>

      <?php endif; ?>

    </fieldset>

  <?php endif; ?>

  <?php if ($showMasterFileMetadata || $showReferenceCopyMetadata || $showThumbnailCopyMetadata): ?>

    <fieldset class="collapsible digital-object-metadata single">
      <legend><?php echo __('Access Copies') ?></legend>

      <?php if ($showMasterFileMetadata): ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Master file') ?> <i class="fa fa-file<?php if (!$canAccessMasterFile): ?> inactive<?php endif; ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showMasterFileGoogleMap): ?>
            <div id="front-map" class="simple-map" data-key="<?php echo $googleMapsApiKey ?>" data-latitude="<?php echo $latitude ?>" data-longitude="<?php echo $longitude ?>"></div>
          <?php endif; ?>

          <?php if ($showMasterFileGeolocation): ?>
            <?php echo render_show(__('Latitude'), render_value($latitude), array('fieldLabel' => 'latitude')) ?>
            <?php echo render_show(__('Longitude'), render_value($longitude), array('fieldLabel' => 'longitude')) ?>
          <?php endif; ?>

          <?php if ($showMasterFileURL): ?>
            <?php echo render_show(__('URL'), render_value($resource->path), array('fieldLabel' => 'url')) ?>
          <?php endif; ?>

          <?php if ($showMasterFileName): ?>
            <?php if ($canAccessMasterFile): ?>
              <?php echo render_show(__('Filename'), link_to(render_value_inline($resource->name), $resource->object->getDigitalObjectLink(), array('target' => '_blank')), array('fieldLabel' => 'filename')) ?>
            <?php else: ?>
              <?php echo render_show(__('Filename'), render_value($resource->name), array('fieldLabel' => 'filename')) ?>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($showMasterFileMediaType): ?>
            <?php echo render_show(__('Media type'), render_value($resource->mediaType), array('fieldLabel' => 'mediaType')) ?>
          <?php endif; ?>

          <?php if ($showMasterFileMimeType): ?>
            <?php echo render_show(__('Mime-type'), render_value($resource->mimeType), array('fieldLabel' => 'mimeType')) ?>
          <?php endif; ?>

          <?php if ($showMasterFileSize): ?>
            <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize), array('fieldLabel' => 'filesize')) ?>
          <?php endif; ?>

          <?php if ($showMasterFileCreatedAt): ?>
            <?php echo render_show(__('Uploaded'), format_date($resource->createdAt, 'f'), array('fieldLabel' => 'uploaded')) ?>
          <?php endif; ?>

          <?php if ($showMasterFilePermissions): ?>
            <?php echo render_show(__('Permissions'), render_value($masterFileDenyReason), array('fieldLabel' => 'masterFilePermissions')) ?>
          <?php endif; ?>

        </div>

      <?php endif; ?>

      <?php if (null !== $referenceCopy && $showReferenceCopyMetadata): ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Reference copy') ?> <i class="fa fa-file<?php if (!$canAccessReferenceCopy): ?> inactive<?php endif; ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showReferenceCopyFileName): ?>
            <?php if ($canAccessReferenceCopy && $sf_user->isAuthenticated()): ?>
              <?php echo render_show(__('Filename'), link_to(render_value_inline($referenceCopy->name), $referenceCopy->getFullPath(), array('target' => '_blank')), array('fieldLabel' => 'referenceCopyFileName')) ?>
            <?php else: ?>
              <?php echo render_show(__('Filename'), render_value($referenceCopy->name), array('fieldLabel' => 'referenceCopyFileName')) ?>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($showReferenceCopyMediaType): ?>
            <?php echo render_show(__('Media type'), render_value($referenceCopy->mediaType), array('fieldLabel' => 'referenceCopyFileName')) ?>
          <?php endif; ?>

          <?php if ($showReferenceCopyMimeType): ?>
            <?php echo render_show(__('Mime-type'), render_value($referenceCopy->mimeType), array('fieldLabel' => 'referenceCopyMimeType')) ?>
          <?php endif; ?>

          <?php if ($showReferenceCopyFileSize): ?>
            <?php echo render_show(__('Filesize'), hr_filesize($referenceCopy->byteSize), array('fieldLabel' => 'referenceCopyFileSize')) ?>
          <?php endif; ?>

          <?php if ($showReferenceCopyCreatedAt): ?>
            <?php echo render_show(__('Uploaded'), format_date($referenceCopy->createdAt, 'f'), array('fieldLabel' => 'referenceCopyUploaded')) ?>
          <?php endif; ?>

          <?php if ($showReferenceCopyPermissions): ?>
            <?php echo render_show(__('Permissions'), render_value($referenceCopyDenyReason), array('fieldLabel' => 'referenceCopyPermissions')) ?>
          <?php endif; ?>

        </div>

      <?php endif; ?>

      <?php if (null !== $thumbnailCopy && $showThumbnailCopyMetadata): ?>

        <div class="digital-object-metadata-header">
          <h3><?php echo __('Thumbnail copy') ?> <i class="fa fa-file<?php if (!$canAccessThumbnailCopy): ?> inactive<?php endif; ?>" aria-hidden="true"></i></h3>
        </div>

        <div class="digital-object-metadata-body">
          <?php if ($showThumbnailCopyFileName): ?>
            <?php if ($canAccessThumbnailCopy): ?>
              <?php echo render_show(__('Filename'), link_to(render_value_inline($thumbnailCopy->name), $thumbnailCopy->getFullPath(), array('target'=> '_blank')), array('fieldLabel' => 'thumbnailCopyFileName')) ?>
            <?php else: ?>
              <?php echo render_show(__('Filename'), render_value($thumbnailCopy->name), array('fieldLabel' => 'thumbnailCopyFileName')) ?>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($showThumbnailCopyMediaType): ?>
            <?php echo render_show(__('Media type'), render_value($thumbnailCopy->mediaType), array('fieldLabel' => 'thumbnailCopyFileName')) ?>
          <?php endif; ?>

          <?php if ($showThumbnailCopyMimeType): ?>
            <?php echo render_show(__('Mime-type'), render_value($thumbnailCopy->mimeType), array('fieldLabel' => 'thumbnailCopyMimeType')) ?>
          <?php endif; ?>

          <?php if ($showThumbnailCopyFileSize): ?>
            <?php echo render_show(__('Filesize'), hr_filesize($thumbnailCopy->byteSize), array('fieldLabel' => 'thumbnailCopyFileSize')) ?>
          <?php endif; ?>

          <?php if ($showThumbnailCopyCreatedAt): ?>
            <?php echo render_show(__('Uploaded'), format_date($thumbnailCopy->createdAt, 'f'), array('fieldLabel' => 'thumbnailCopyUploaded')) ?>
          <?php endif; ?>

          <?php if (!empty($thumbnailCopyDenyReason)): ?>
            <?php echo render_show(__('Permissions'), render_value($thumbnailCopyDenyReason), array('fieldLabel' => 'thumbnailCopyPermissions')) ?>
          <?php endif; ?>

        </div>

      <?php endif; ?>

    </fieldset>

  <?php endif; ?>

</section>
