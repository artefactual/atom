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

class arStorageServiceExtractFileAction extends sfAction
{
    public function execute($request)
    {
        $view = sfView::NONE;

        $this->resource = $this->getRoute()->resource;

        // Check that object exists and that it is not the root
        if (!isset($this->resource)) {
            $this->forward404();
        }

        try {
            $status = $this->extractFile($request);
        } catch (QubitApi404Exception $e) {
            $this->response->setStatusCode(404, $e->getMessage());

            throw $e;
        } catch (QubitApiNotAuthorizedException $e) {
            $this->response->setStatusCode(401, $e->getMessage());

            throw $e;
        } catch (QubitApiForbiddenException $e) {
            $this->response->setStatusCode(403, $e->getMessage());

            throw $e;
        } catch (QubitApiBadRequestException $e) {
            $this->response->setStatusCode(400, $e->getMessage());

            throw $e;
        } catch (Exception $e) {
            $this->response->setStatusCode(500, $e->getMessage());

            throw $e;
        }

        return $view;
    }

    /**
     * Build the request URL for the Storage Service API's 'extractFile' endpoint
     * and make the request. Ensure if there is an error that the Storage Service
     * return status is passed back to the browser.
     *
     * @param mixed $request
     */
    protected function extractFile($request)
    {
        if (!arStorageServiceUtils::getAipDownloadEnabled()) {
            throw new QubitApiForbiddenException('AIP Download disabled');
        }

        if (null === $aipUUID = $this->resource->object->aipUUID) {
            throw new QubitApiBadRequestException('Missing parameter: aipuuid');
        }

        if (null === $relativePath = $this->resource->object->relativePathWithinAip) {
            throw new QubitApiBadRequestException('Missing object property: relativePathWithinAip');
        }

        if (null === $baseUrl = QubitSetting::getByName('storage_service_api_url')) {
            throw new QubitApiBadRequestException('Missing setting: storage_service_api_url');
        }

        if (null === $aip = QubitAip::getByUuid($aipUUID)) {
            // Check object properties if QubitAip not found. This will occur for
            // metadata-only dip upload digital objects.
            if (null === $aipFileName = $this->resource->object->aipName) {
                throw new QubitApiBadRequestException('Missing object property: aipName');
            }
        } else {
            $aipFileName = $aip->filename;
        }

        $url = sprintf(
            '%s/%s/%s/extract_file/?relative_path_to_file=%s-%s/data/%s',
            trim($baseUrl, '/'),
            arStorageServiceUtils::STORAGE_SERVICE_PACKAGE_PATH,
            $aipUUID,
            $aipFileName,
            $aipUUID,
            $relativePath
        );

        // Check return status from Storage Service
        if (200 !== $status = arStorageServiceUtils::getFileFromStorageService($url)) {
            sfContext::getInstance()->getLogger()->err(sprintf('Storage Service extract file returned status: %s; %s', $status, $url));
            $ex = arStorageServiceUtils::getStorageServiceException($status);

            throw $ex;
        }

        exit;
    }
}
