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

class InformationObjectFindingAidComponent extends sfComponent
{
  public function execute($request)
  {
    // Only allowed for top-level and non draft descriptions
    if ($this->resource->parentId != QubitInformationObject::ROOT_ID
      || $this->resource->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_DRAFT_ID)
    {
      return sfView::NONE;
    }

    $this->path = arGenerateFindingAidJob::getFindingAidPath($this->resource->id);
    $this->status = arGenerateFindingAidJob::getStatus($this->resource->id);
    $this->statusString = arGenerateFindingAidJob::getStatusString($this->resource->id);

    // Ensure file is actually there
    if (!file_exists($this->path))
    {
      // Show nothing for public users
      if (!$this->getUser()->isAuthenticated())
      {
        return sfView::NONE;
      }

      // Fix status for authenticated users
      if($this->status === QubitTerm::JOB_STATUS_COMPLETED_ID)
      {
        $this->status = QubitTerm::JOB_STATUS_ERROR_ID;
        $this->statusString = 'File missing';
      }
    }
  }
}
