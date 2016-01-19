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
    $this->do = new QubitDigitalObject;
    $this->do->parentId = NULL;

    foreach ($payload as $field => $value)
    {
      $this->processField($field, $value);
    }

    if (empty($payload->mime_type) && !empty($payload->path))
    {
      // Attempt to determine MIME type if unspecified
      $mimeType = QubitDigitalObject::deriveMimeType($payload->path);
      $this->do->mimeType = $mimeType;
    }

    if (empty($payload->media_type) && !empty($this->do->mimeType))
    {
      $this->do->setDefaultMediaType();
    }

    // Associate properties with information object
    if (!empty($this->do->informationObjectId))
    {

      // Create file UUID property
      if (!empty($payload->file_uuid))
      {
        $property = new QubitProperty;
        $property->objectId = $this->do->informationObjectId;
        $property->name = "objectUUID";
        $property->value = $payload->file_uuid;
        $property->save();
      }

      // Create AIP UUID property
      if (!empty($payload->aip_uuid))
      {
        $property = new QubitProperty;
        $property->objectId = $this->do->informationObjectId;
        $property->name = "aipUUID";
        $property->value = $payload->aip_uuid;
        $property->save();
      }
    }

    $this->do->save();

    $this->response->setStatusCode(201);

    return array('id' => (int)$this->do->id, 'slug' => $this->do->slug);
  }

  protected function processField($field, $value)
  {
    switch ($field)
    {
      case 'name':
      case 'path':
      case 'byte_size':
      case 'parent_id':
        $field = lcfirst(sfInflector::camelize($field));
        $this->do->$field = $value;

        break;

      case 'information_object_slug':
        // Get parent slug so we can determine its ID
        $criteria = new Criteria;
        $criteria->add(QubitSlug::SLUG, $value);

        $slug = QubitSlug::getOne($criteria);

        if (null !== $slug)
        {
          $this->do->informationObjectId = $slug->objectId;
        }
        break;

      case 'uri':
        $this->do->importFromURI($value);
        break;

      case 'information_object_id':
        $io = new QubitInformationObject;
        $io->parentId = $value;
        $io->setLevelOfDescriptionByName('item');
        $io->save();

        $this->do->informationObjectId = $io->id;
        break;

      case 'media_type':
        $criteria = new Criteria;
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);
        $criteria->add(QubitTermI18n::NAME, $value);
        $typeTerm = QubitTerm::getOne($criteria);
        $this->do->mediaType = $typeTerm;
        break;
    }
  }
}
