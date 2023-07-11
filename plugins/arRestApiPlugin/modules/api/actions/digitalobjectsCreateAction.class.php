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

class ApiDigitalObjectsCreateAction extends QubitApiAction
{
    protected function post($request, $payload)
    {
        $this->do = new QubitDigitalObject();

        if (isset($payload->information_object_slug)) {
            // If information_object_slug is set, attach this digital object to
            // the identified information object
            $this->setObjectBySlug($payload->information_object_slug);
        } elseif (isset($payload->information_object_id)) {
            // If information_object_id is set, create a new *child* (item) of
            // the identified IO and attach the new digital object to the child
            $this->setObjectById($payload->information_object_id);
        } elseif (isset($payload->parent_id)) {
            // If parent_id is set then make this digital object a child of the
            // identified digital object
            $this->setParent($payload->parent_id);
        } else {
            // If none of the above is set, throw an error
            throw new QubitApiBadRequestException(
                'Request must include one of: information_object_slug,'.
                ' information_object_id, or parent_id'
            );
        }

        foreach ($payload as $field => $value) {
            $this->processField($field, $value);
        }

        if (empty($payload->mime_type) && !empty($payload->path)) {
            // Attempt to determine MIME type if unspecified
            $mimeType = QubitDigitalObject::deriveMimeType($payload->path);
            $this->do->mimeType = $mimeType;
        }

        if (empty($payload->media_type)) {
            $this->do->mediaTypeId = QubitTerm::OTHER_ID;
        } elseif (!empty($this->do->mimeType) && 'unknown' != $this->do->mimeType) {
            $this->do->setDefaultMediaType();
        }

        // Associate properties with information object
        if (!empty($this->do->objectId)) {
            $props = [
                'file_uuid' => 'objectUUID',
                'aip_uuid' => 'aipUUID',
                'format_name' => 'formatName',
                'format_version' => 'formatVersion',
                'format_registry_key' => 'formatRegistryKey',
                'format_registry_name' => 'formatRegistryName',
                'relative_path_within_aip' => 'relativePathWithinAip',
                'aip_name' => 'aipName',
            ];

            foreach ($props as $pkey => $pval) {
                if (empty($payload->{$pkey})) {
                    continue;
                }

                $property = new QubitProperty();
                $property->objectId = $this->do->objectId;
                $property->name = $pval;
                $property->value = $payload->{$pkey};
                $property->save();
            }
        }

        $this->do->save();

        $this->response->setStatusCode(201);

        return ['id' => (int) $this->do->id, 'slug' => $this->do->slug];
    }

    protected function setParent($parentId)
    {
        $parent = QubitDigitalObject::getById($parentId);

        if (empty($parent)) {
            throw new QubitApiBadRequestException('Invalid parent_id');
        }

        // Check that user has permission to update the parent digital object's
        // "object" (information object or actor)
        if (!QubitAcl::check($parent->object, 'update')) {
            throw new QubitApiNotAuthorizedException();
        }

        $this->do->parent = $parent;
    }

    protected function setObjectBySlug($slug)
    {
        $io = QubitInformationObject::getBySlug($slug);

        if (empty($io)) {
            throw new QubitApiBadRequestException(
                'Invalid information_object_slug'
            );
        }

        if (!QubitAcl::check($io, 'update')) {
            throw new QubitApiNotAuthorizedException();
        }

        if (!empty($io->getDigitalObject())) {
            throw new QubitApiForbiddenException(
                'Already has a digital object'
            );
        }

        $this->do->object = $io;
    }

    protected function setObjectById($id)
    {
        $parent = QubitInformationObject::getById($id);

        if (empty($parent)) {
            throw new QubitApiBadRequestException(
                'Invalid information_object_id'
            );
        }

        if (!QubitAcl::check($parent, 'create')) {
            throw new QubitApiNotAuthorizedException();
        }

        // Create a child info object, and link digital object to it
        $this->do->object = $this->createChildItem($parent);
    }

    protected function createChildItem($parent)
    {
        $io = new QubitInformationObject();
        $io->parentId = $parent->id;
        $io->setLevelOfDescriptionByName('item');
        $io->save();

        return $io;
    }

    protected function processField($field, $value)
    {
        switch ($field) {
            case 'name':
            case 'path':
            case 'byte_size':
                $field = lcfirst(sfInflector::camelize($field));
                $this->do->{$field} = $value;

                break;

            case 'uri':
                $this->do->importFromURI($value);

                break;

            case 'media_type':
                if (!empty($value)) {
                    $criteria = new Criteria();
                    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);
                    $criteria->add(QubitTermI18n::NAME, $value);
                    if (null !== $typeTerm = QubitTerm::getOne($criteria)) {
                        $this->do->mediaType = $typeTerm;
                    }
                }

                break;

            case 'usage':
                $criteria = new Criteria();
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID);
                $criteria->add(QubitTermI18n::NAME, $value);
                $typeTerm = QubitTerm::getOne($criteria);
                $this->do->usage = $typeTerm;

                break;
        }
    }
}
