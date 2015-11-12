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
 * Digital Object display component
 *
 * @package    AccesstoMemory
 * @subpackage digitalObject
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectShowComponent extends sfComponent
{
  /**
   * Show digital object representation
   *
   * @param sfWebRequest $request
   *
   * @todo add components for non-image digital objects
   */
  public function execute($request)
  {
    // If type of display not specified, show a thumbnail
    if (!isset($this->usageType))
    {
      $this->usageType = QubitTerm::THUMBNAIL_ID;
    }

    if (QubitTerm::MASTER_ID == $this->usageType && !QubitAcl::check($this->resource->informationObject, 'readMaster'))
    {
      return sfView::NONE;
    }

    $this->setComponentType();

    // Check to see if the user has permission to view this representation,
    // if not, we'll show a generic icon.
    if ($this->checkShowGenericIcon())
    {
      $this->showComponent = 'showGenericIcon';
    }

    if ($this->usageType == QubitTerm::REFERENCE_ID)
    {
      $this->accessWarning = $this->getAccessWarning();
    }

    if (!isset($this->link))
    {
      $this->link = null;
    }

    if (!isset($this->iconOnly))
    {
      $this->iconOnly = false;
    }
  }

  /**
   * Check permissions to tell if we should show a generic icon or not.
   */
  private function checkShowGenericIcon()
  {
    $curUser = sfContext::getInstance()->getUser();
    $curInfoObjectId = $this->resource->informationObject->id;

    switch ($this->usageType)
    {
      case QubitTerm::REFERENCE_ID:
        // Non-authenticated user: check against PREMIS rules.
        if (!$curUser->isAuthenticated() && QubitGrantedRight::hasGrantedRights($curInfoObjectId))
        {
          return !QubitGrantedRight::checkPremis($curInfoObjectId, 'readReference');
        }

        // Authenticated, check regular ACL rules...
        return !QubitAcl::check($this->resource->informationObject, 'readReference');

      case QubitTerm::THUMBNAIL_ID:
        return !QubitAcl::check($this->resource->informationObject, 'readThumbnail');
    }
  }

  /**
   * Get warning messages if access denied via 'deny' or 'conditional' PREMIS rules.
   * @return  A string of the warning if reference access denied, otherwise bool false
   */
  private function getAccessWarning()
  {
    $curInfoObjectId = $this->resource->informationObject->id;
    $denyReason = '';

    if (!QubitGrantedRight::checkPremis($curInfoObjectId, 'readReference', $denyReason) ||
        !QubitAcl::check($this->resource->informationObject, 'readReference'))
    {
      return $denyReason;
    }

    return false;
  }

  private function setComponentType()
  {
    // Figure out which show component to call
    switch ($this->resource->mediaTypeId)
    {
      case QubitTerm::IMAGE_ID:

        if ($this->resource->showAsCompoundDigitalObject())
        {
          $this->showComponent = 'showCompound';
        }
        else if ($this->resource->isWebCompatibleImageFormat())
        {
          $this->showComponent = 'showImage';
        }
        else
        {
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

        if ($this->resource->showAsCompoundDigitalObject())
        {
          $this->showComponent = 'showCompound';
        }
        else
        {
          $this->showComponent = 'showText';
        }

        break;

      default:
        $this->showComponent = 'showDownload';

        break;
    }
  }
}
