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

class ApiPhysicalObjectsCreateAction extends QubitApiAction
{
    protected function post($request, $payload)
    {
        // Optionally set culture
        $this->culture = 'en';
        if (!empty($payload->culture)) {
            $this->culture = trim(strtolower($payload->culture));
        }

        // Create empty physical object
        $this->po = new QubitPhysicalObject();
        $this->sourceCulture = $this->culture;

        // Populate physical object fields and save
        foreach ($payload as $field => $value) {
            $this->processField($field, $value);
        }

        $this->po->save();

        // Add physical object to other AtoM objects
        $this->createRelations($payload);

        // Return response
        $this->response->setStatusCode(201);

        return ['slug' => $this->po->slug];
    }

    protected function processField($field, $value)
    {
        $value = trim($value);

        switch ($field) {
            case 'type':
                $typeNormalized = strtolower($value);

                // Load physical object type data
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID);
                $termsByCulture = $taxonomy->getTermNameToIdLookupTable();

                // Check if physical object type term already exists
                if (!empty($termsByCulture['en'][$typeNormalized])) {
                    $this->po->typeId = $termsByCulture[$this->culture][$typeNormalized];
                } else {
                    // Create new physical object type term
                    $term = new QubitTerm();
                    $term->taxonomyId = QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID;
                    $term->setName($value);
                    $term->setSourceCulture($this->culture);
                    $term->save();

                    $this->po->typeId = $term->id;
                }

                break;

            case 'name':
                $this->po->setName($value);

                break;

            case 'location':
                $this->po->setLocation($value);

                break;
        }
    }

    protected function createRelations($payload)
    {
        if (!empty($payload->add_to)) {
            if (!is_array($payload->add_to)) {
                throw new QubitApiBadRequestException('related_to must be an array');
            }

            foreach ($payload->add_to as $slug) {
                // Attempt to load target resource to add the physical object to
                $relatedObject = QubitObject::getBySlug($slug);

                // Return error if the target resource is invalid
                if (null == $relatedObject) {
                    throw new QubitApiBadRequestException('Invalid parent_slug');
                }

                // Make sure the target resource is an appropriate type
                if ('QubitInformationObject' != $relatedObject->className) {
                    throw new QubitApiBadRequestException('Invalid parent resource');
                }

                // Make sure user has permission to modify the target resource
                if (!QubitAcl::check($relatedObject, 'update')) {
                    throw new QubitApiNotAuthorizedException();
                }

                // Add physical object to target resource
                $relatedResource = $relatedObject->className::getById($relatedObject->id);
                $relatedResource->addPhysicalObject($this->po);
                $relatedResource->save();
            }
        }
    }
}
