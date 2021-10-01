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

class arUnogPreservicaPluginDownloadMasterAction extends sfAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;

        if (!isset($this->resource)) {
            $this->forward404();
        }

        // Check, if I.O. isn't published, that user is authorized to access drafts
        $publicationStatusId = $this->resource->getPublicationStatus()->statusId;

        if (
            QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID != $publicationStatusId
            && !QubitAcl::check($this->resource, 'viewDraft')
        ) {
            $this->getResponse()->setStatusCode(403);

            return sfView::NONE;
        }

        // Attempt to fetch information object's digital object
        if (empty($do = $this->resource->getDigitalObject())) {
            $this->forward404();
        }

        // Make sure user has the right to read the master
        if (!QubitAcl::check($this->resource, 'readMaster')) {
            $this->getResponse()->setStatusCode(403);

            return sfView::NONE;
        }

        $this->digitalObjectId = $do->id;

        if ($this->needsPopup()) {
            $this->accessToken = bin2hex(random_bytes(32)); // URL friendly
            $this->context->user->setAttribute("token-{$this->digitalObjectId}", $this->accessToken, 'symfony/user/sfUser/copyrightStatementTmpAccess');

            $this->response->addMeta('robots', 'noindex,nofollow');
            $this->setTemplate('viewCopyrightStatement');

            $this->copyrightStatement = sfConfig::get('app_digitalobject_copyright_statement');

            return sfView::SUCCESS;
        }

        // Attempt to fetch Preservia ID
        $propertyName = arUnogPreservicaPluginConfiguration::PRESERVICA_UUID_PROPERTY_NAME;
        $preservicaUUID = $do->getPropertyByName($propertyName)->__toString();

        if (empty($preservicaUUID)) {
            $this->forward404();
        }

        $username = sfConfig::get('app_preservica_username');
        $password = sfConfig::get('app_preservica_password');
        $host = sfConfig::get('app_preservica_host');

        $client = new arUnogPreservicaPluginRestClient($host, $username, $password);

        // Fail if credentials are invalid
        if (empty($client->token)) {
            $this->getResponse()->setStatusCode(403);

            return sfView::NONE;
        }

        // Fail if no object with that UUID exists
        if (empty($objectData = $client->getObjectDetails($preservicaUUID))) {
            $this->forward404();
        }

        $cmisId = $objectData->value->id;
        $filename = $client->getObjectDetailsPropertyByName($objectData, 'cmis:contentStreamFileName');

        $client->streamAsset($cmisId, $filename);
    }

    protected function needsPopup()
    {
        // Only if the user is reading the master digital object, and the resource
        // has a PREMIS conditional copyright restriction
        if (!$this->resource->getDigitalObject()->hasConditionalCopyright()) {
            return false;
        }

        // Show the pop-up if a valid access token was not submitted
        return false === $this->isAccessTokenValid();
    }

    private function isAccessTokenValid()
    {
        $providedToken = $this->request->token;
        $internalToken = $this->context->user->getAttribute("token-{$this->digitalObjectId}", null, 'symfony/user/sfUser/copyrightStatementTmpAccess');

        if (empty($providedToken) || empty($internalToken)) {
            return false;
        }

        if ($providedToken !== $internalToken) {
            return false;
        }

        return true;
    }
}
