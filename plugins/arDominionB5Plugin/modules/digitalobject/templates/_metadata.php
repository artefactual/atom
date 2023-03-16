<?php use_helper('Date'); ?>

<?php if ($showOriginalFileMetadata || $showPreservationCopyMetadata || $showMasterFileMetadata || $showReferenceCopyMetadata || $showThumbnailCopyMetadata) { ?>
  <section>

    <?php if ($relatedToIo) { ?>
      <?php $headingCondition = SecurityPrivileges::editCredentials($sf_user, 'informationobject'); ?>
    <?php } elseif ($relatedToActor) { ?>
      <?php $headingCondition = SecurityPrivileges::editCredentials($sf_user, 'actor'); ?>
    <?php } ?>
    <?php echo render_b5_section_heading(
        __('%1% metadata', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]),
        $headingCondition,
        [$resource, 'module' => 'digitalobject', 'action' => 'edit'],
        [
            'anchor' => 'content-collapse',
            'title' => __('Edit %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]),
        ]
    ); ?>

    <div class="accordion accordion-flush">

      <?php if ($showOriginalFileMetadata || $showPreservationCopyMetadata) { ?>

        <div class="accordion-item<?php echo ($showMasterFileMetadata || $showReferenceCopyMetadata || $showThumbnailCopyMetadata) ? '' : ' rounded-bottom'; ?>">
          <h3 class="accordion-header" id="preservation-heading">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#preservation-collapse" aria-expanded="true" aria-controls="preservation-collapse">
              <?php echo __('Preservation Copies'); ?>
            </button>
          </h3>
          <div id="preservation-collapse" class="accordion-collapse collapse show" aria-labelledby="preservation-heading">
            <div class="accordion-body p-0">
              <?php if ($showOriginalFileMetadata) { ?>

                <div class="<?php echo render_b5_show_field_css_classes(); ?>">

                  <h3 class="<?php echo render_b5_show_label_css_classes(); ?>"><?php echo __('Original file'); ?><i class="fa fa-archive ms-2 text-dark<?php if (!$canAccessOriginalFile) { ?> text-muted<?php } ?>" aria-hidden="true"></i></h3>

                  <div class="digital-object-metadata-body <?php echo render_b5_show_value_css_classes(); ?>">
                    <?php if ($showOriginalFileName) { ?>
                      <?php echo render_show(__('Filename'), render_value_inline($resource->object->originalFileName), ['fieldLabel' => 'originalFileName', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFormatName) { ?>
                      <?php echo render_show(__('Format name'), render_value_inline($resource->object->formatName), ['fieldLabel' => 'originalFileFormatName', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFormatVersion) { ?>
                      <?php echo render_show(__('Format version'), render_value_inline($resource->object->formatVersion), ['fieldLabel' => 'originalFileFormatVersion', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFormatRegistryKey) { ?>
                      <?php echo render_show(__('Format registry key'), render_value_inline($resource->object->formatRegistryKey), ['fieldLabel' => 'originalFileFormatRegistryKey', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFormatRegistryName) { ?>
                      <?php echo render_show(__('Format registry name'), render_value_inline($resource->object->formatRegistryName), ['fieldLabel' => 'originalFileFormatRegistryName', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFileSize) { ?>
                      <?php echo render_show(__('Filesize'), hr_filesize(intval((string) $resource->object->originalFileSize)), ['fieldLabel' => 'originalFileSize', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFileIngestedAt) { ?>
                      <?php echo render_show(__('Ingested'), format_date($originalFileIngestedAt, 'f'), ['fieldLabel' => 'originalFileIngestedAt', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showOriginalFilePermissions) { ?>
                      <?php echo render_show(__('Permissions'), render_value($accessStatement), ['fieldLabel' => 'originalFilePermissions', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($sf_user->isAuthenticated() && $relatedToIo) { ?>
                      <?php if ($storageServicePluginEnabled) { ?>
                        <?php include_partial(
                          'arStorageService/aipDownload', ['resource' => $resource]
                        ); ?>
                      <?php } else { ?>
                        <?php echo render_show(
                          __('File UUID'),
                          render_value_inline($resource->object->objectUUID),
                          ['fieldLabel' => 'objectUUID', 'isSubField' => true]
                        ); ?>
                        <?php echo render_show(
                          __('AIP UUID'),
                          render_value_inline($resource->object->aipUUID),
                          ['fieldLabel' => 'aipUUID', 'isSubField' => true]
                        ); ?>
                      <?php } ?>
                    <?php } ?>
                   </div>

                </div>

              <?php } ?>

              <?php if ($showPreservationCopyMetadata) { ?>

                <div class="<?php echo render_b5_show_field_css_classes(); ?>">

                  <h3 class="<?php echo render_b5_show_label_css_classes(); ?>"><?php echo __('Preservation copy'); ?><i class="fa fa-archive ms-2 text-dark<?php if (!$canAccessPreservationCopy) { ?> text-muted<?php } ?>" aria-hidden="true"></i></h3>

                  <div class="digital-object-metadata-body <?php echo render_b5_show_value_css_classes(); ?>">
                    <?php if ($showPreservationCopyFileName) { ?>
                      <?php echo render_show(__('Filename'), render_value_inline($resource->object->preservationCopyFileName), ['fieldLabel' => 'preservationCopyFileName', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showPreservationCopyFileSize) { ?>
                      <?php echo render_show(__('Filesize'), hr_filesize(intval((string) $resource->object->preservationCopyFileSize)), ['fieldLabel' => 'preservationCopyFileSize', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showPreservationCopyNormalizedAt) { ?>
                      <?php echo render_show(__('Normalized'), format_date($preservationCopyNormalizedAt, 'f'), ['fieldLabel' => 'preservactionCopyNormalizedAt', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showPreservationCopyPermissions) { ?>
                      <?php echo render_show(__('Permissions'), render_value($accessStatement), ['fieldLabel' => 'preservationCopyPermissions', 'isSubField' => true]); ?>
                    <?php } ?>
                  </div>

                </div>

              <?php } ?>
            </div>
          </div>
        </div>

      <?php } ?>

      <?php if ($showMasterFileMetadata || $showReferenceCopyMetadata || $showThumbnailCopyMetadata) { ?>

        <div class="accordion-item rounded-bottom">
          <h3 class="accordion-header" id="access-heading">
            <button class="accordion-button<?php echo ($showOriginalFileMetadata || $showPreservationCopyMetadata) ? ' collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#access-collapse" aria-expanded="<?php echo ($showOriginalFileMetadata || $showPreservationCopyMetadata) ? 'false' : 'true'; ?>" aria-controls="access-collapse">
              <?php echo __('Access Copies'); ?>
            </button>
          </h3>
          <div id="access-collapse" class="accordion-collapse collapse<?php echo ($showOriginalFileMetadata || $showPreservationCopyMetadata) ? '' : ' show'; ?>" aria-labelledby="access-heading">
            <div class="accordion-body p-0">
              <?php if ($showMasterFileMetadata) { ?>

                <div class="<?php echo render_b5_show_field_css_classes(); ?>">

                  <h3 class="<?php echo render_b5_show_label_css_classes(); ?>"><?php echo __('Master file'); ?><i class="fa fa-file ms-2 text-dark<?php if (!$canAccessMasterFile) { ?> text-muted<?php } ?>" aria-hidden="true"></i></h3>

                  <div class="digital-object-metadata-body <?php echo render_b5_show_value_css_classes(); ?>">
                    <?php if ($showMasterFileGoogleMap) { ?>
                      <div class="p-1">
                        <div id="front-map" class="simple-map" data-key="<?php echo $googleMapsApiKey; ?>" data-latitude="<?php echo $latitude; ?>" data-longitude="<?php echo $longitude; ?>"></div>
                      </div>
                    <?php } ?>

                    <?php if ($showMasterFileGeolocation) { ?>
                      <?php echo render_show(__('Latitude'), render_value_inline($latitude), ['fieldLabel' => 'latitude', 'isSubField' => true]); ?>
                      <?php echo render_show(__('Longitude'), render_value_inline($longitude), ['fieldLabel' => 'longitude', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showMasterFileURL) { ?>
                      <?php echo render_show(__('URL'), render_value($resource->path), ['fieldLabel' => 'url', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showMasterFileName) { ?>
                      <?php if ($canAccessMasterFile) { ?>
                        <?php echo render_show(__('Filename'), link_to($resource->name, $resource->object->getDigitalObjectUrl(), ['target' => '_blank']), ['fieldLabel' => 'filename', 'isSubField' => true]); ?>
                      <?php } else { ?>
                        <?php echo render_show(__('Filename'), $resource->name, ['fieldLabel' => 'filename', 'isSubField' => true]); ?>
                      <?php } ?>
                    <?php } ?>

                    <?php if ($showMasterFileMediaType) { ?>
                      <?php echo render_show(__('Media type'), render_value_inline($resource->mediaType), ['fieldLabel' => 'mediaType', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showMasterFileMimeType) { ?>
                      <?php echo render_show(__('Mime-type'), render_value_inline($resource->mimeType), ['fieldLabel' => 'mimeType', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showMasterFileSize) { ?>
                      <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize), ['fieldLabel' => 'filesize', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showMasterFileCreatedAt) { ?>
                      <?php echo render_show(__('Uploaded'), format_date($resource->createdAt, 'f'), ['fieldLabel' => 'uploaded', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showMasterFilePermissions) { ?>
                      <?php echo render_show(__('Permissions'), render_value($masterFileDenyReason), ['fieldLabel' => 'masterFilePermissions', 'isSubField' => true]); ?>
                    <?php } ?>
                  </div>

                </div>

              <?php } ?>

              <?php if ($showReferenceCopyMetadata) { ?>

                <div class="<?php echo render_b5_show_field_css_classes(); ?>">

                  <h3 class="<?php echo render_b5_show_label_css_classes(); ?>"><?php echo __('Reference copy'); ?><i class="fa fa-file ms-2 text-dark<?php if (!$canAccessReferenceCopy) { ?> text-muted<?php } ?>" aria-hidden="true"></i></h3>

                  <div class="digital-object-metadata-body <?php echo render_b5_show_value_css_classes(); ?>">
                    <?php if ($showReferenceCopyFileName) { ?>
                      <?php if ($canAccessReferenceCopy && $sf_user->isAuthenticated()) { ?>
                        <?php echo render_show(__('Filename'), link_to($referenceCopy->name, $referenceCopy->getFullPath(), ['target' => '_blank']), ['fieldLabel' => 'referenceCopyFileName', 'isSubField' => true]); ?>
                      <?php } else { ?>
                        <?php echo render_show(__('Filename'), $referenceCopy->name, ['fieldLabel' => 'referenceCopyFileName', 'isSubField' => true]); ?>
                      <?php } ?>
                    <?php } ?>

                    <?php if ($showReferenceCopyMediaType) { ?>
                      <?php echo render_show(__('Media type'), render_value_inline($referenceCopy->mediaType), ['fieldLabel' => 'referenceCopyFileName', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showReferenceCopyMimeType) { ?>
                      <?php echo render_show(__('Mime-type'), render_value_inline($referenceCopy->mimeType), ['fieldLabel' => 'referenceCopyMimeType', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showReferenceCopyFileSize) { ?>
                      <?php echo render_show(__('Filesize'), hr_filesize($referenceCopy->byteSize), ['fieldLabel' => 'referenceCopyFileSize', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showReferenceCopyCreatedAt) { ?>
                      <?php echo render_show(__('Uploaded'), format_date($referenceCopy->createdAt, 'f'), ['fieldLabel' => 'referenceCopyUploaded', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showReferenceCopyPermissions) { ?>
                      <?php echo render_show(__('Permissions'), render_value($referenceCopyDenyReason), ['fieldLabel' => 'referenceCopyPermissions', 'isSubField' => true]); ?>
                    <?php } ?>
                  </div>

                </div>

              <?php } ?>

              <?php if ($showThumbnailCopyMetadata) { ?>

                <div class="<?php echo render_b5_show_field_css_classes(); ?>">

                  <h3 class="<?php echo render_b5_show_label_css_classes(); ?>"><?php echo __('Thumbnail copy'); ?><i class="fa fa-file ms-2 text-dark<?php if (!$canAccessThumbnailCopy) { ?> text-muted<?php } ?>" aria-hidden="true"></i></h3>

                  <div class="digital-object-metadata-body <?php echo render_b5_show_value_css_classes(); ?>">
                    <?php if ($showThumbnailCopyFileName) { ?>
                      <?php if ($canAccessThumbnailCopy) { ?>
                        <?php echo render_show(__('Filename'), link_to($thumbnailCopy->name, $thumbnailCopy->getFullPath(), ['target' => '_blank']), ['fieldLabel' => 'thumbnailCopyFileName', 'isSubField' => true]); ?>
                      <?php } else { ?>
                        <?php echo render_show(__('Filename'), $thumbnailCopy->name, ['fieldLabel' => 'thumbnailCopyFileName', 'isSubField' => true]); ?>
                      <?php } ?>
                    <?php } ?>

                    <?php if ($showThumbnailCopyMediaType) { ?>
                      <?php echo render_show(__('Media type'), render_value_inline($thumbnailCopy->mediaType), ['fieldLabel' => 'thumbnailCopyFileName', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showThumbnailCopyMimeType) { ?>
                      <?php echo render_show(__('Mime-type'), render_value_inline($thumbnailCopy->mimeType), ['fieldLabel' => 'thumbnailCopyMimeType', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showThumbnailCopyFileSize) { ?>
                      <?php echo render_show(__('Filesize'), hr_filesize($thumbnailCopy->byteSize), ['fieldLabel' => 'thumbnailCopyFileSize', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if ($showThumbnailCopyCreatedAt) { ?>
                      <?php echo render_show(__('Uploaded'), format_date($thumbnailCopy->createdAt, 'f'), ['fieldLabel' => 'thumbnailCopyUploaded', 'isSubField' => true]); ?>
                    <?php } ?>

                    <?php if (!empty($thumbnailCopyDenyReason)) { ?>
                      <?php echo render_show(__('Permissions'), render_value($thumbnailCopyDenyReason), ['fieldLabel' => 'thumbnailCopyPermissions', 'isSubField' => true]); ?>
                    <?php } ?>
                  </div>

                </div>

              <?php } ?>
            </div>
          </div>
        </div>

      <?php } ?>

    </div>

  </section>
<?php } ?>
