<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Digital Object metadata component.
 *
 * @author     Mike G <mikeg@artefactual.com>
 */
class DigitalObjectMetadataComponent extends sfComponent
{
    public function execute($request)
    {
        if (!isset($this->resource)) {
            return;
        }

        // Check related object type to display IO properties in the template
        $this->relatedToIo = $this->resource->object instanceof QubitInformationObject;
        $this->relatedToActor = $this->resource->object instanceof QubitActor;

        $this->user = $this->context->getUser();
        $this->storageServicePluginEnabled = (
            $this->context->getConfiguration()->isPluginEnabled('arStorageServicePlugin')
            && arStorageServiceUtils::getAipDownloadEnabled()
        );

        $this->referenceCopy = $this->resource->getRepresentationByUsage(QubitTerm::REFERENCE_ID);
        $this->thumbnailCopy = $this->resource->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID);

        // An authenticated user can download the original preservation file only if the Storage Service plugin is enabled
        $this->canAccessOriginalFile = (
            $this->user->isAuthenticated()
            && $this->relatedToIo
            && $this->storageServicePluginEnabled
        );

        // The preservation copy cannot be downloaded from AtoM
        $this->canAccessPreservationCopy = false;

        // AtoM's representations cannot be accessed by default
        $this->canAccessMasterFile = false;
        $this->canAccessReferenceCopy = false;
        $this->canAccessThumbnailCopy = false;

        // Reasons for denying access to a DO representation based on PREMIS rights
        $this->masterFileDenyReason = false;
        $this->referenceCopyDenyReason = false;
        $this->thumbnailCopyDenyReason = false;

        // If the digital object is related to an information object, and the user
        // is not authenticated, access may be restricted by PREMIS rights, as well
        // as standard ACL rules
        if ($this->relatedToIo && !$this->user->isAuthenticated()) {
            $this->canAccessMasterFile = QubitGrantedRight::checkPremis(
                $this->resource->object->id,
                'readMaster',
                $this->masterFileDenyReason
            ) && QubitAcl::check($this->resource->object, 'readMaster');

            $this->canAccessReferenceCopy = QubitGrantedRight::checkPremis(
                $this->resource->object->id,
                'readReference',
                $this->referenceCopyDenyReason
            ) && QubitAcl::check($this->resource->object, 'readReference');

            $this->canAccessThumbnailCopy = QubitGrantedRight::checkPremis(
                $this->resource->object->id,
                'readThumbnail',
                $this->thumbnailCopyDenyReason
            ) && QubitAcl::check($this->resource->object, 'readThumbnail');
        } else {
            // Check ACL authorization
            $this->canAccessMasterFile = QubitAcl::check(
                $this->resource->object,
                'readMaster'
            );

            $this->canAccessReferenceCopy = QubitAcl::check(
                $this->resource->object,
                'readReference'
            );

            $this->canAccessThumbnailCopy = QubitAcl::check(
                $this->resource->object,
                'readThumbnail'
            );
        }

        // Statement shown as the Permissions field for preservation copies
        $this->accessStatement = $this->getPreservationSystemAccessStatement();

        // Determine which metadata sections should be shown
        if ($this->relatedToIo) {
            // Only an information object can have Archivematica "original file" and
            // "preservation copy" metadata
            $this->showOriginalFileMetadata = $this->setOriginalFileShowProperties();
            $this->showPreservationCopyMetadata = $this->setPreservationCopyShowProperties();
        }

        // Metadata for information objects and actors
        $this->showMasterFileMetadata = $this->setMasterFileShowProperties();
        $this->showReferenceCopyMetadata = $this->setReferenceCopyShowProperties();
        $this->showThumbnailCopyMetadata = $this->setThumbnailCopyShowProperties();
    }

    protected function isPreservationSystemAccessStatementEnabled()
    {
        return sfConfig::get('app_digitalobject_preservation_system_access_statement_enabled', false);
    }

    protected function getPreservationSystemAccessStatement()
    {
        if ($this->isPreservationSystemAccessStatementEnabled()) {
            return sfConfig::get('app_digitalobject_preservation_system_access_statement');
        }
    }

    protected function isEmpty($value)
    {
        return null === $value || '' === (string) $value;
    }

    protected function setMasterFileShowProperties()
    {
        // Provide Google Maps API key to template
        $this->googleMapsApiKey = sfConfig::get('app_google_maps_api_key');

        // Provide latitude to template
        $latitudeProperty = $this->resource->object->digitalObjectsRelatedByobjectId[0]->getPropertyByName('latitude');
        $this->latitude = $latitudeProperty->value;

        // Provide longitude to template
        $longitudeProperty = $this->resource->object->digitalObjectsRelatedByobjectId[0]->getPropertyByName('longitude');
        $this->longitude = $longitudeProperty->value;

        $this->showMasterFileGoogleMap = (
            sfConfig::get('app_toggleDigitalObjectMap', false)
            && is_numeric($this->latitude)
            && is_numeric($this->longitude)
            && (bool) $this->googleMapsApiKey
        );

        $this->showMasterFileGeolocation = (
            check_field_visibility('app_element_visibility_digital_object_geolocation')
            && (is_numeric($this->latitude) || is_numeric($this->longitude))
        );

        $this->showMasterFileURL = (
            QubitTerm::EXTERNAL_URI_ID == $this->resource->usageId
            && check_field_visibility('app_element_visibility_digital_object_url')
            && !$this->isEmpty($this->resource->path)
        );

        $this->showMasterFileName = (
            QubitTerm::EXTERNAL_URI_ID != $this->resource->usageId
            && check_field_visibility('app_element_visibility_digital_object_file_name')
            && !$this->isEmpty($this->resource->name)
        );

        $this->showMasterFileMediaType = (
            check_field_visibility('app_element_visibility_digital_object_media_type')
            && !$this->isEmpty($this->resource->mediaType)
        );

        $this->showMasterFileMimeType = (
            check_field_visibility('app_element_visibility_digital_object_mime_type')
            && !$this->isEmpty($this->resource->mimeType)
        );

        $this->showMasterFileSize = (
            check_field_visibility('app_element_visibility_digital_object_file_size')
            && !$this->isEmpty($this->resource->byteSize)
        );

        $this->showMasterFileCreatedAt = (
            check_field_visibility('app_element_visibility_digital_object_uploaded')
            && !$this->isEmpty($this->resource->createdAt)
        );

        $this->showMasterFilePermissions = (
            check_field_visibility('app_element_visibility_digital_object_permissions')
            && !$this->isEmpty($this->masterFileDenyReason)
        );

        return
            $this->showMasterFileGoogleMap
            || $this->showMasterFileGeolocation
            || $this->showMasterFileURL
            || $this->showMasterFileName
            || $this->showMasterFileMediaType
            || $this->showMasterFileMimeType
            || $this->showMasterFileSize
            || $this->showMasterFileCreatedAt;
    }

    protected function setReferenceCopyShowProperties()
    {
        $this->showReferenceCopyFileName = (
            check_field_visibility('app_element_visibility_digital_object_reference_file_name')
            && !$this->isEmpty($this->referenceCopy->name)
        );
        $this->showReferenceCopyMediaType = (
            check_field_visibility('app_element_visibility_digital_object_reference_media_type')
            && !$this->isEmpty($this->referenceCopy->mediaType)
        );
        $this->showReferenceCopyMimeType = (
            check_field_visibility('app_element_visibility_digital_object_reference_mime_type')
            && !$this->isEmpty($this->referenceCopy->mimeType)
        );
        $this->showReferenceCopyFileSize = (
            check_field_visibility('app_element_visibility_digital_object_reference_file_size')
            && !$this->isEmpty($this->referenceCopy->byteSize)
        );
        $this->showReferenceCopyCreatedAt = (
            check_field_visibility('app_element_visibility_digital_object_reference_uploaded')
            && !$this->isEmpty($this->referenceCopy->createdAt)
        );
        $this->showReferenceCopyPermissions = (
            check_field_visibility('app_element_visibility_digital_object_reference_permissions')
            && !$this->isEmpty($this->referenceCopyDenyReason)
        );

        return (
            $this->showReferenceCopyFileName
            || $this->showReferenceCopyMediaType
            || $this->showReferenceCopyMimeType
            || $this->showReferenceCopyFileSize
            || $this->showReferenceCopyCreatedAt
        ) && null !== $this->referenceCopy;
    }

    protected function setThumbnailCopyShowProperties()
    {
        $this->showThumbnailCopyFileName = (
            check_field_visibility('app_element_visibility_digital_object_thumbnail_file_name')
            && !$this->isEmpty($this->thumbnailCopy->name)
        );
        $this->showThumbnailCopyMediaType = (
            check_field_visibility('app_element_visibility_digital_object_thumbnail_media_type')
            && !$this->isEmpty($this->thumbnailCopy->mediaType)
        );
        $this->showThumbnailCopyMimeType = (
            check_field_visibility('app_element_visibility_digital_object_thumbnail_mime_type')
            && !$this->isEmpty($this->thumbnailCopy->mimeType)
        );
        $this->showThumbnailCopyFileSize = (
            check_field_visibility('app_element_visibility_digital_object_thumbnail_file_size')
            && !$this->isEmpty($this->thumbnailCopy->byteSize)
        );
        $this->showThumbnailCopyCreatedAt = (
            check_field_visibility('app_element_visibility_digital_object_thumbnail_uploaded')
            && !$this->isEmpty($this->thumbnailCopy->createdAt)
        );
        $this->showThumbnailPermissions = (
            check_field_visibility('app_element_visibility_digital_object_reference_permissions')
            && !$this->isEmpty($this->thumbnailCopyDenyReason)
        );

        return (
            $this->showThumbnailCopyFileName
            || $this->showThumbnailCopyMediaType
            || $this->showThumbnailCopyMimeType
            || $this->showThumbnailCopyFileSize
            || $this->showThumbnailCopyCreatedAt
        ) && null !== $this->thumbnailCopy;
    }

    protected function setOriginalFileShowProperties()
    {
        $this->showOriginalFileName = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_original_file_name')
            && !$this->isEmpty($this->resource->object->originalFileName)
        );
        $this->showOriginalFormatName = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_original_format_name')
            && !$this->isEmpty($this->resource->object->formatName)
        );
        $this->showOriginalFormatVersion = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_original_format_version')
            && !$this->isEmpty($this->resource->object->formatVersion)
        );
        $this->showOriginalFormatRegistryKey = (
            check_field_visibility(
                'app_element_visibility_digital_object_preservation_system_original_format_registry_key'
            )
            && !$this->isEmpty($this->resource->object->formatRegistryKey)
        );
        $this->showOriginalFormatRegistryName = (
            check_field_visibility(
                'app_element_visibility_digital_object_preservation_system_original_format_registry_name'
            )
            && !$this->isEmpty($this->resource->object->formatRegistryName)
        );
        $this->showOriginalFileSize = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_original_file_size')
            && !$this->isEmpty($this->resource->object->originalFileSize)
        );
        // Convert this UTC string property to local time
        $this->originalFileIngestedAt = $this->localizeUTCDateTime(
            (string) $this->resource->object->originalFileIngestedAt
        );
        $this->showOriginalFileIngestedAt = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_original_ingested')
            && !$this->isEmpty($this->originalFileIngestedAt)
        );
        $this->showOriginalFilePermissions =
            check_field_visibility('app_element_visibility_digital_object_preservation_system_original_permissions');

        return $this->showOriginalFileName
            || $this->showOriginalFormatName
            || $this->showOriginalFormatVersion
            || $this->showOriginalFormatRegistryKey
            || $this->showOriginalFormatRegistryName
            || $this->showOriginalFileSize
            || $this->showOriginalFileIngestedAt;
    }

    protected function setPreservationCopyShowProperties()
    {
        $this->showPreservationCopyFileName = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_preservation_file_name')
            && !$this->isEmpty($this->resource->object->preservationCopyFileName)
        );
        $this->showPreservationCopyFileSize = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_preservation_file_size')
            && !$this->isEmpty($this->resource->object->preservationCopyFileSize)
        );
        // Convert this UTC string property to local time
        $this->preservationCopyNormalizedAt = $this->localizeUTCDateTime(
            (string) $this->resource->object->preservationCopyNormalizedAt
        );
        $this->showPreservationCopyNormalizedAt = (
            check_field_visibility('app_element_visibility_digital_object_preservation_system_preservation_normalized')
            && !$this->isEmpty($this->preservationCopyNormalizedAt)
        );
        $this->showPreservationCopyPermissions =
            check_field_visibility('app_element_visibility_digital_object_preservation_system_preservation_permissions');

        return $this->showPreservationCopyFileName
            || $this->showPreservationCopyFileSize
            || $this->showPreservationCopyNormalizedAt;
    }

    protected function localizeUTCDateTime($dateTime)
    {
        if (false !== $timestamp = strtotime($dateTime)) {
            return date('Y-m-d\TH:i:s\Z', $timestamp);
        }
    }
}
