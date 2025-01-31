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
 * Digital Object display component.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectShowComponent extends sfComponent
{
    /**
     * Show digital object representation.
     *
     * @param sfWebRequest $request
     *
     * @todo add components for non-image digital objects
     */
    public function execute($request)
    {
        // If type of display not specified, show a thumbnail
        if (!isset($this->usageType)) {
            $this->usageType = QubitTerm::THUMBNAIL_ID;
        }

        // Don't show anything if trying to view a master DO without authorization
        if (
            QubitTerm::MASTER_ID == $this->usageType
            && !QubitAcl::check($this->resource->object, 'readMaster')
        ) {
            return sfView::NONE;
        }

        $this->setComponentType();

        // Check to see if the user has permission to view this representation,
        // if not, we'll show a generic icon.
        if ($this->checkShowGenericIcon()) {
            $this->showComponent = 'showGenericIcon';
        }

        // Check PREMIS granted rights, and show an access warning if they prevent
        // access
        if (QubitTerm::REFERENCE_ID == $this->usageType) {
            $this->accessWarning = $this->getAccessWarning();
        }

        if (!isset($this->link)) {
            $this->link = null;
        }

        if (!isset($this->iconOnly)) {
            $this->iconOnly = false;
        }
    }

    /**
     * Check permissions to tell if we should show a generic icon or not.
     */
    private function checkShowGenericIcon()
    {
        $resourceObject = $this->resource->object;

        // Get the parent resouce (IO or Actor) for Acl check
        // if resource->object is empty and parent exists
        if (!$resourceObject && $this->resouce->parent) {
            $resourceObject = $this->resource->parent->object;
        }

        switch ($this->usageType) {
            case QubitTerm::REFERENCE_ID:
                return !QubitAcl::check($resourceObject, 'readReference');

            case QubitTerm::THUMBNAIL_ID:
                return !QubitAcl::check($resourceObject, 'readThumbnail');
        }
    }

    /**
     * Get warning messages if access denied via 'deny' or 'conditional' PREMIS
     * rules.
     *
     * @return string Custom PREMIS "access denied" message, or an empty string
     */
    private function getAccessWarning()
    {
        $denyReason = '';

        if ($this->resource->object instanceof QubitActor) {
            return '';
        }

        QubitGrantedRight::checkPremis(
            $this->resource->object->id,
            'readReference',
            $denyReason
        );

        return $denyReason;
    }

    private function setComponentType()
    {
        // Figure out which show component to call
        switch ($this->resource->mediaTypeId) {
            case QubitTerm::IMAGE_ID:
                if (
                    $this->resource->showAsCompoundDigitalObject()
                    && $this->resource->object instanceof QubitInformationObject
                ) {
                    $this->showComponent = 'showCompound';
                } elseif ($this->resource->isWebCompatibleImageFormat()) {
                    $this->showComponent = 'showImage';
                } else {
                    $this->showComponent = 'showDownload';
                }

                break;

            case QubitTerm::AUDIO_ID:
                $this->showComponent = 'showAudio';

                break;

            case QubitTerm::VIDEO_ID:
                $this->showComponent = 'showVideo';

                break;

            case QubitTerm::TEXT_ID:
                if (
                    $this->resource->showAsCompoundDigitalObject()
                    && $this->resource->object instanceof QubitInformationObject
                ) {
                    $this->showComponent = 'showCompound';
                } else {
                    $this->showComponent = 'showText';
                }

                break;

            default:
                $this->showComponent = 'showDownload';

                break;
        }
    }
}
