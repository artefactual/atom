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

class InformationObjectFullWidthTreeViewSyncAction extends sfAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;
        $i18n = sfContext::getInstance()->i18n;

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            $this->response->setStatusCode(404);

            return $this->renderText(json_encode(['error' => $i18n->__('Sync failed: resource not found')]));
        }

# ???
        if (!$this->getUser()->isAuthenticated()) {
            $this->response->setStatusCode(401);

            return $this->renderText(json_encode(['error' => $i18n->__('Sync not allowed: log in required')]));
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'update')) {
            $this->response->setStatusCode(403);

            return $this->renderText(json_encode(['error' => $i18n->__('Sync not allowed: not enough permissions')]));
        }

        // Sync Elasticsearch values with DB if need be
        $syncer = new QubitLftSyncer($this->resource->id);

        $responseData = ["success" => true];

	if ($syncer->sync()) {
            $responseData["repaired"] = true;
	}

        $this->response->setStatusCode(201);

        return $this->renderText(json_encode($responseData));
    }
}
