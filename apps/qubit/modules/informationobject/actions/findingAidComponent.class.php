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
    // Get finding aid data from top-level
    if ($this->resource->parentId != QubitInformationObject::ROOT_ID)
    {
      $this->resource = $this->resource->getCollectionRoot();
    }

    $this->showDownload = $this->showStatus = $this->showUpload = $this->showGenerate = $this->showDelete = false;

    $this->path = arFindingAidJob::getFindingAidPathForDownload($this->resource->id);

    // Public users can only see the download link if the file exists
    if (!$this->getUser()->isAuthenticated())
    {
      if (isset($this->path))
      {
        $this->showDownload = true;

        return;
      }
      else
      {
        return sfView::NONE;
      }
    }

    $lastJobStatus = arFindingAidJob::getStatus($this->resource->id);

    // For auth. users, if no job has been executed,
    // show allowed actions or nothing
    if (!isset($lastJobStatus))
    {
      // Edge case where the job status is missing but the file exists,
      if (isset($this->path))
      {
        $this->showDownload = true;

        // Check ACL to show delete option
        if (QubitAcl::check($this->resource, 'update'))
        {
          $this->showDelete = true;
        }

        return;
      }

      if (!$this->showActions())
      {
        return sfView::NONE;
      }

      return;
    }

    $i18n = $this->context->i18n;

    // If there is a job in progress, show only status
    if ($lastJobStatus == QubitTerm::JOB_STATUS_IN_PROGRESS_ID)
    {
      $this->showStatus = true;
      $this->status = $i18n->__('In progress');

      return;
    }

    // If the last job failed, show error status and allowed actions
    if ($lastJobStatus == QubitTerm::JOB_STATUS_ERROR_ID)
    {
      $this->showStatus = true;
      $this->showActions();
      $this->status = $i18n->__('Error');

      return;
    }

    // If the last job completed, get finding aid status property
    if ($lastJobStatus == QubitTerm::JOB_STATUS_COMPLETED_ID)
    {
      $findingAidStatus = $this->resource->getFindingAidStatus();

      // If the property is missing, the finding aid was deleted,
      // show allowed actions or nothing
      if (!isset($findingAidStatus))
      {
        if (!$this->showActions())
        {
          return sfView::NONE;
        }

        return;
      }

      // If the property is set but the file is missing,
      // show status and allowed actions
      if (!isset($this->path))
      {
        $this->showStatus = true;
        $this->showActions();
        $this->status = $i18n->__('File missing');

        return;
      }

      // Show status and download link
      $this->showStatus = $this->showDownload = true;

      switch ((integer)$findingAidStatus)
      {
        case arFindingAidJob::GENERATED_STATUS:
          $this->status = $i18n->__('Generated');

          break;

        case arFindingAidJob::UPLOADED_STATUS:
          $this->status = $i18n->__('Uploaded');

          break;

        // It should never get here if we don't add more finding aid statuses
        default:
          $this->status = $i18n->__('Unknown');
      }

      // Check ACL to show delete option
      if (QubitAcl::check($this->resource, 'update'))
      {
        $this->showDelete = true;
      }

      return;
    }

    // It should never get here if we don't add more job statuses
    $this->showStatus = true;
    $this->showActions();
    $this->status = $i18n->__('Unknown');
  }

  public function showActions()
  {
    // Actions only allowed for users with permissions
    if (!QubitAcl::check($this->resource, 'update'))
    {
      return false;
    }

    // Upload is allowed for drafts
    $this->showUpload = true;

    // Generate is allowed for published descriptions and for drafts
    // if the public finding aid setting is set to false
    $setting = QubitSetting::getByName('publicFindingAid');
    if ($this->resource->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID
      || (isset($setting) && !$setting->getValue(array('sourceCulture' => true))))
    {
      $this->showGenerate = true;
    }

    return true;
  }
}
