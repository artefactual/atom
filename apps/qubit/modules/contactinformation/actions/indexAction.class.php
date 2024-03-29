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

class ContactInformationIndexAction extends sfAction
{
    public function execute($request)
    {
        $hasEditAccess = QubitAcl::check($this->resource, 'update')
            || $this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID);
        // Check user authorization
        if (!($this->getUser()->isAuthenticated() && $hasEditAccess)) {
            QubitAcl::forwardUnauthorized();
        }

        $this->resource = QubitContactInformation::getById($request->id);

        if (!isset($this->resource)) {
            $this->forward404();
        }

        $value = [];

        $value['primaryContact'] = (bool) $this->resource->primaryContact;

        if (isset($this->resource->contactPerson)) {
            $value['contactPerson'] = $this->resource->contactPerson;
        }

        if (isset($this->resource->streetAddress)) {
            $value['streetAddress'] = $this->resource->streetAddress;
        }

        if (isset($this->resource->website)) {
            $value['website'] = $this->resource->website;
        }

        if (isset($this->resource->email)) {
            $value['email'] = $this->resource->email;
        }

        if (isset($this->resource->telephone)) {
            $value['telephone'] = $this->resource->telephone;
        }

        if (isset($this->resource->fax)) {
            $value['fax'] = $this->resource->fax;
        }

        if (isset($this->resource->postalCode)) {
            $value['postalCode'] = $this->resource->postalCode;
        }

        if (isset($this->resource->countryCode)) {
            $value['countryCode'] = $this->resource->countryCode;
        }

        if (isset($this->resource->latitude)) {
            $value['latitude'] = $this->resource->latitude;
        }

        if (isset($this->resource->longitude)) {
            $value['longitude'] = $this->resource->longitude;
        }

        if (isset($this->resource->city)) {
            $value['city'] = $this->resource->city;
        }

        if (isset($this->resource->region)) {
            $value['region'] = $this->resource->region;
        }

        if (isset($this->resource->note)) {
            $value['note'] = $this->resource->note;
        }

        if (isset($this->resource->contactType)) {
            $value['contactType'] = $this->resource->contactType;
        }

        // Add source culture information if necessary
        $culture = sfContext::getInstance()->user->getCulture();

        if ($culture != $this->resource->sourceCulture) {
            $sourceCultureData = ['fields' => []];

            // Add source culture text direction, if different from user's culture,
            // so source culture value can be displayed appropriately (some languages
            // display text from right to left: Arabic and Chinese for example)
            $sourceCultureDirection = sfCultureInfo::getInstance($this->resource->sourceCulture)->direction;
            if (sfCultureInfo::getInstance($culture)->direction != $sourceCultureDirection) {
                $sourceCultureData['direction'] = $sourceCultureDirection;
            }

            // Add source culture field values
            foreach (['contactType', 'city', 'region', 'note'] as $propertyName) {
                $propertyGetMethod = 'get'.ucwords($propertyName);
                $sourceCultureData['fields'][$propertyName] = $this->resource->{$propertyGetMethod}(['sourceCulture' => true]);
            }

            $value['_sourceCulture'] = $sourceCultureData;
        }

        return $this->renderText(json_encode($value));
    }
}
