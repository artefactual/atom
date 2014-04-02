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
class DigitalObjectShowDownloadComponent extends sfComponent
{
  /**
   * Show a representation of a digital object image.
   *
   * @param sfWebRequest $request
   *
   */
  public function execute($request)
  {
    switch($this->usageType)
    {
      case QubitTerm::REFERENCE_ID:
        $this->representation = $this->resource->getRepresentationByUsage(QubitTerm::REFERENCE_ID);

        break;

      case QubitTerm::THUMBNAIL_ID:
        $this->representation = $this->resource->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID);

        break;

      case QubitTerm::MASTER_ID:
      default:
        $this->representation = QubitDigitalObject::getGenericRepresentation($this->resource->mimeType, $this->usageType);
    }

    // If no representation found, then default to generic rep
    if (!$this->representation)
    {
      $this->representation = QubitDigitalObject::getGenericRepresentation($this->resource->mimeType, $this->usageType);
    }

    // Build a fully qualified URL to this digital object asset
    if ((QubitTerm::IMAGE_ID != $this->resource->mediaTypeId || QubitTerm::REFERENCE_ID == $this->usageType)
        && QubitAcl::check($this->resource->informationObject, 'readMaster'))
    {
      $this->link = $this->resource->getPublicPath();
    }
  }
}
