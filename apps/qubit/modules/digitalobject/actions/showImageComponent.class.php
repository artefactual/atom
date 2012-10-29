<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Display an 'image' digital asset
 *
 * @package    AtoM
 * @subpackage digital object
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectShowImageComponent extends sfComponent
{
  /**
   * Show a representation of a digital object image.
   *
   * @param sfWebRequest $request
   *
   */
  public function execute($request)
  {
    // Get representation by usage type
    $this->representation = $this->resource->getRepresentationByUsage($this->usageType);

    // If we can't find a representation for this object, try their parent
    if (!$this->representation && ($parent = $this->resource->parent))
    {
      $this->representation = $parent->getRepresentationByUsage($this->usageType);
    }

    // If representation is not a valid digital object, return a generic icon
    if (!$this->representation)
    {
       $this->representation = QubitDigitalObject::getGenericRepresentation($this->resource->mimeType, $this->usageType);
    }
  }
}
