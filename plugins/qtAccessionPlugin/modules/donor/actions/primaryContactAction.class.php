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

class DonorPrimaryContactAction extends sfAction
{
    public function execute($request)
    {
        $resource = $this->getRoute()->resource;

        // Check user authorization
        if (!QubitAcl::check($resource, 'read')) {
            QubitAcl::forwardToSecureAction();
        }

        // Return 404 if the primary contact doesn't exist
        if (null === $primaryContactInformation = $resource->getPrimaryContact()) {
            $this->forward404();
        }

        $data = [];

        foreach (
            [
                'city',
                'contactPerson',
                'countryCode',
                'email',
                'postalCode',
                'region',
                'streetAddress',
                'telephone',
                'contactType',
                'website',
                'fax',
                'latitude',
                'longitude',
                'note',
            ] as $field
        ) {
            if (isset($primaryContactInformation->{$field})) {
                $data[$field] = $primaryContactInformation->{$field};
            } else {
                $data[$field] = '';
            }
        }

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode($data));
    }
}
