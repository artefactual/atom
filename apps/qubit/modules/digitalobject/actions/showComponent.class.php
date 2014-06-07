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

    if (
        (QubitTerm::REFERENCE_ID == $this->usageType && !QubitAcl::check($this->resource->informationObject, 'readReference'))
        ||
        (QubitTerm::THUMBNAIL_ID == $this->usageType && !QubitAcl::check($this->resource->informationObject, 'read'))
       )
    {
      $showGenericIcon = true;
    }

    // Figure out which show component to call
    switch ($this->resource->mediaTypeId)
    {
      case $showGenericIcon:
        $this->showComponent = 'showGenericIcon';

        break;

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

    if (!isset($this->link))
    {
      $this->link = null;
    }

    if (!isset($this->iconOnly))
    {
      $this->iconOnly = false;
    }
  }
}
