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

class DefaultTranslationLinksComponent extends sfComponent
{
    public function execute($request)
    {
        $currentCulture = $this->getUser()->getCulture();

        switch (get_class($this->resource)) {
            case 'QubitInformationObject':
                $this->module = 'informationobject';
                $i18ns = $this->resource->informationObjectI18ns;
                $propertyName = 'title';
                $sourceCultureProperty = $this->resource->getTitle(['sourceCulture' => true]);

                break;

            case 'QubitActor':
                $this->module = 'actor';
                $i18ns = $this->resource->actorI18ns;
                $propertyName = 'authorizedFormOfName';
                $sourceCultureProperty = $this->resource->getAuthorizedFormOfName(['sourceCulture' => true]);

                break;

            case 'QubitRepository':
                $this->module = 'repository';
                $i18ns = $this->resource->actorI18ns;
                $propertyName = 'authorizedFormOfName';
                $sourceCultureProperty = $this->resource->getAuthorizedFormOfName(['sourceCulture' => true]);

                break;

            case 'QubitAccession':
                $this->module = 'accession';
                $i18ns = $this->resource->accessionI18ns;
                $sourceCultureProperty = $this->resource->identifier;

                break;

            case 'QubitDeaccession':
                $this->module = 'deaccession';
                $i18ns = $this->resource->deaccessionI18ns;
                $sourceCultureProperty = $this->resource->identifier;

                break;

            case 'QubitDonor':
                $this->module = 'donor';
                $i18ns = $this->resource->actorI18ns;
                $propertyName = 'authorizedFormOfName';
                $sourceCultureProperty = $this->resource->getAuthorizedFormOfName(['sourceCulture' => true]);

                break;

            case 'QubitFunctionObject':
                $this->module = 'function';
                $i18ns = $this->resource->functionObjectI18ns;
                $propertyName = 'authorizedFormOfName';
                $sourceCultureProperty = $this->resource->getAuthorizedFormOfName(['sourceCulture' => true]);

                break;

            case 'QubitPhysicalObject':
                $this->module = 'physicalobject';
                $i18ns = $this->resource->physicalObjectI18ns;
                $propertyName = 'name';
                $sourceCultureProperty = $this->resource->getName(['sourceCulture' => true]);

                break;

            case 'QubitRightsHolder':
                $this->module = 'rightsholder';
                $i18ns = $this->resource->actorI18ns;
                $propertyName = 'authorizedFormOfName';
                $sourceCultureProperty = $this->resource->getAuthorizedFormOfName(['sourceCulture' => true]);

                break;

            case 'QubitTerm':
                $this->module = 'term';
                $i18ns = $this->resource->termI18ns;
                $propertyName = 'name';
                $sourceCultureProperty = $this->resource->getName(['sourceCulture' => true]);

                break;
        }

        // Return nothing if the resource only has the current culture
        if (1 == count($i18ns) && $i18ns[0]->culture == $currentCulture) {
            return sfView::NONE;
        }

        // Get other cultures available
        $this->translations = [];
        foreach ($i18ns as $i18n) {
            if ($i18n->culture == $currentCulture) {
                continue;
            }

            $name = isset($propertyName) && isset($i18n->{$propertyName}) ? $i18n->{$propertyName} : $sourceCultureProperty;
            $langCode = $i18n->culture;
            $langName = format_language($langCode);

            $this->translations[$langCode] = [
                'name' => $name,
                'language' => ucfirst($langName),
            ];
        }
    }
}
