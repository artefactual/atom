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

class InformationObjectFindingAidLinkComponent extends sfComponent
{
  public function execute($request)
  {
    // Get finding aid path and status from top-level
    if ($this->resource->parentId != QubitInformationObject::ROOT_ID)
    {
      $this->resource = $this->resource->getCollectionRoot();
    }

    $this->path = arFindingAidJob::getFindingAidPathForDownload($this->resource->id);
    if (!isset($this->path))
    {
      return sfView::NONE;
    }

    $parts = explode(DIRECTORY_SEPARATOR, $this->path);
    $this->filename = array_pop($parts);
    
    $i18n = $this->context->i18n;

    switch ((integer)$this->resource->getFindingAidStatus())
    {
      case arFindingAidJob::GENERATED_STATUS:
        $this->label = $i18n->__('Generated finding aid');

        break;

      case arFindingAidJob::UPLOADED_STATUS:
        $this->label = $i18n->__('Uploaded finding aid');

        break;

      // It should never get here if we don't add more finding aid statuses
      default:
        $this->label = $i18n->__('Finding aid');
    }
  }
}
