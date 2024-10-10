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
        // If Finding Aids are explicitly disabled in QubitSettings, don't show
        // this contenxt menu
        if ('1' !== sfConfig::get('app_findingAidsEnabled', '1')) {
            return sfView::NONE;
        }

        $this->showDownload = false;
        $this->showStatus = false;
        $this->showUpload = false;
        $this->showGenerate = false;
        $this->showDelete = false;

        // Get finding aid data from top-level
        if (QubitInformationObject::ROOT_ID != $this->resource->parentId) {
            $this->resource = $this->resource->getCollectionRoot();
        }

        $findingAid = new QubitFindingAid($this->resource);

        // Public users can only see the download link if the file exists
        if (!$this->getUser()->isAuthenticated()) {
            if (empty($findingAid->getPath())) {
                return sfView::NONE;
            }

            $this->path = $findingAid->getPath();
            $this->showDownload = true;

            return;
        }

        $lastJobStatus = arFindingAidJob::getStatus($this->resource->id);

        // For auth. users, if no job has been executed,
        // show allowed actions or nothing
        if (!isset($lastJobStatus)) {
            // Edge case where the job status is missing but the file exists,
            if (!empty($findingAid->getPath())) {
                $this->path = $findingAid->getPath();
                $this->showDownload = true;

                // Check ACL to show delete option
                if (QubitAcl::check($this->resource, 'update')) {
                    $this->showDelete = true;
                }

                return;
            }

            if (!$this->showActions()) {
                return sfView::NONE;
            }

            return;
        }

        // If there is a job in progress, show only status
        if (QubitTerm::JOB_STATUS_IN_PROGRESS_ID == $lastJobStatus) {
            $this->showStatus = true;
            $this->status = $this->context->i18n->__('In progress');

            return;
        }

        // If the last job failed, show error status and allowed actions
        if (QubitTerm::JOB_STATUS_ERROR_ID == $lastJobStatus) {
            $this->showStatus = true;
            $this->showActions();
            $this->status = $this->context->i18n->__('Error');

            return;
        }

        // If the last job completed, get finding aid status property
        if (QubitTerm::JOB_STATUS_COMPLETED_ID == $lastJobStatus) {
            // If the property is missing, the finding aid was deleted,
            // show allowed actions or nothing
            if (empty($findingAid->getStatus())) {
                if (!$this->showActions()) {
                    return sfView::NONE;
                }

                return;
            }

            // If the property is set but the file is missing,
            // show status and allowed actions
            if (empty($findingAid->getPath())) {
                $this->showStatus = true;
                $this->showActions();
                $this->status = $this->context->i18n->__('File missing');

                return;
            }

            // Show status and download link
            $this->showStatus = true;
            $this->showDownload = true;
            $this->path = $findingAid->getPath();

            switch ((int) $findingAid->getStatus()) {
                case QubitFindingAid::GENERATED_STATUS:
                    $this->status = $this->context->i18n->__('Generated');

                    break;

                case QubitFindingAid::UPLOADED_STATUS:
                    $this->status = $this->context->i18n->__('Uploaded');

                    break;

                // It should never get here if we don't add more finding aid
                // statuses
                default:
                    $this->status = $this->context->i18n->__('Unknown');
            }

            // Check ACL to show delete option
            if (QubitAcl::check($this->resource, 'update')) {
                $this->showDelete = true;
            }

            return;
        }

        // It should never get here if we don't add more job statuses
        $this->showStatus = true;
        $this->showActions();
        $this->status = $this->context->i18n->__('Unknown');
    }

    public function showActions()
    {
        // Actions only allowed for users with permissions
        if (!QubitAcl::check($this->resource, 'update')) {
            return false;
        }

        // Upload is allowed for drafts
        $this->showUpload = true;

        // Generate is allowed for published descriptions and for drafts
        // if the public finding aid setting is set to false
        $setting = QubitSetting::getByName('publicFindingAid');
        if (
            QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID ==
                $this->resource->getPublicationStatus()->statusId
            || (
                isset($setting)
                && !$setting->getValue(['sourceCulture' => true])
            )
        ) {
            $this->showGenerate = true;
        }

        return true;
    }
}
