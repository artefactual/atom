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

        // Check user authorization
        if (!$this->getUser()->isAuthenticated()) {
            $this->response->setStatusCode(401);

            return $this->renderText(json_encode(['error' => $i18n->__('Sync not allowed: log in required')]));
        }

        // Accept request, but don't perform check/repair if user isn't allowed to perform move
        if (!QubitAcl::check($this->resource, 'update') && !$this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) {
            $responseData = ['checked' => false];

            return $this->renderText(json_encode($responseData));
        }

        // Sync Elasticsearch values with DB if need be
        $syncer = new QubitLftSyncer($this->resource->id);

        $responseData = ['checked' => true];

        // If a repair was attempted during a check, note whether it was successful
        if (is_bool($result = $syncer->sync())) {
            $responseData['repair_successful'] = $result;
        }

        $this->response->setStatusCode(200);

        return $this->renderText(json_encode($responseData));
    }
}
