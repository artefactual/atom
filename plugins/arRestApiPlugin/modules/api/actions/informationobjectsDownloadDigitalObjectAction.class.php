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

class ApiInformationObjectsDownloadDigitalObjectAction extends QubitApiAction
{
    protected function get($request)
    {
        // Check that Information Object with provided slug exists
        if (null === $this->resource = QubitInformationObject::getBySlug($this->request->slug)) {
            throw new QubitApi404Exception('Information object not found');
        }

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            throw new QubitApi404Exception('Information object not found');
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'readMaster')) {
            throw new QubitApiNotAuthorizedException();
        }

        // Check, if I.O. isn't published, that user is authorized to access drafts
        $publicationStatusId = $this->resource->getPublicationStatus()->statusId;

        if (
            QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID != $publicationStatusId
            && !QubitAcl::check($this->resource, 'viewDraft')
        ) {
            throw new QubitApiNotAuthorizedException();
        }

        // Check that a master or external digital object exists
        $digitalObjectTypes = [QubitTerm::MASTER_ID, QubitTerm::EXTERNAL_URI_ID];
        $criteria = new Criteria();
        $criteria->add(QubitDigitalObject::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitDigitalObject::USAGE_ID, $digitalObjectTypes, Criteria::IN);
        if (null === $this->do = QubitDigitalObject::getOne($criteria)) {
            throw new QubitApi404Exception('Digital object not found');
        }

        // Get path (filepath or URI) to digital object
        if (QubitTerm::EXTERNAL_URI_ID == $this->do->usageId) {
            $this->path = $this->do->getPath();
        } else {
            $this->path = $this->do->getAbsolutePath();

            if (!file_exists($this->path)) {
                throw new QubitApi404Exception('Digital object not found');
            }
        }

        $this->downloadDigitalObject($request);
    }

    protected function downloadDigitalObject($request)
    {
        $filename = $this->do->getName();
        $mime = $this->do->getMimeType();
        $bytes = $this->do->getByteSize();

        $this->getResponse()->clearHttpheaders();

        // MIME headers
        $this->getResponse()->setHttpHeader('Content-Description', 'File Transfer');
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');

        // Content headers
        $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename='.$filename);
        $this->getResponse()->setHttpHeader('Content-Length', $bytes);
        $this->getResponse()->setHttpHeader('Content-Type', $mime);

        // Cache headers
        $this->getResponse()->setHttpHeader('Cache-Control', 'public, must-revalidate');
        $this->getResponse()->setHttpHeader('Pragma', 'public');

        $this->getResponse()->sendHttpHeaders();

        // Disable output buffering to avoid memory issues
        if (ob_get_level()) {
            ob_end_clean();
        }

        $this->getResponse()->setContent(readfile($this->path));
        $this->getResponse()->sendContent();

        return 'DigitalObject';
    }
}
