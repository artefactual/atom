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

/**
 * This class is used to provide methods that supplement the core Qubit information object with behaviour or
 * presentation features that are specific to the Metadata Object Description Schema (MODS) standard
 *
 * @package    AccesstoMemory
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfModsPlugin implements ArrayAccess
{
  protected
    $resource;

  public function __construct(QubitInformationObject $resource)
  {
    $this->resource = $resource;
  }

  public function __toString()
  {
    $string = array();

    $levelOfDescriptionAndIdentifier = array();

    if (isset($this->resource->levelOfDescription))
    {
      $levelOfDescriptionAndIdentifier[] = $this->resource->levelOfDescription->__toString();
    }

    if (isset($this->resource->identifier))
    {
      $levelOfDescriptionAndIdentifier[] = $this->resource->identifier;
    }

    if (0 < count($levelOfDescriptionAndIdentifier))
    {
      $string[] = implode($levelOfDescriptionAndIdentifier, ' ');
    }

    $resourceAndPublicationStatus = array();

    if (0 < strlen($title = $this->resource->__toString()))
    {
      $resourceAndPublicationStatus[] = $title;
    }

    $publicationStatus = $this->resource->getPublicationStatus();
    if (isset($publicationStatus) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatus->statusId)
    {
      $resourceAndPublicationStatus[] = "({$publicationStatus->status->__toString()})";
    }

    if (0 < count($resourceAndPublicationStatus))
    {
      $string[] = implode($resourceAndPublicationStatus, ' ');
    }

    return implode(' - ', $string);
  }

  public function offsetExists($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__isset'), $args);
  }

  public function __get($name)
  {
    switch ($name)
    {
      case 'identifier':

        return $this->resource->referenceCode;

      case 'uri':

        $baseUrl = QubitSetting::getByName('siteBaseUrl');
        $baseUrl = ($baseUrl == null) ? 'http://'. gethostname() : $baseUrl;

        return $baseUrl .'/'. $this->resource->slug;

      case 'name':
        $name = array();
        foreach ($this->resource->getActorEvents() as $item)
        {
          if (isset($item->actor))
          {
            $name[] = $item;
          }
        }

        return $name;

      case 'physicalLocation':
        $list = array();

        if (isset($this->resource->repository))
        {
          $list[] = $this->resource->repository->authorizedFormOfName;

          if (isset($this->resource->repository->identifier))
          {
            $list[] = $this->resource->repository->identifier;
          }

          if (null !== $contact = $this->resource->repository->getPrimaryContact())
          {
            $physicalLocation = array();

            if (isset($contact->city))
            {
              $physicalLocation[] = $contact->city;
            }

            if (isset($contact->region))
            {
              $physicalLocation[] = $contact->region;
            }

            if (isset($contact->countryCode))
            {
              $physicalLocation[] = format_country($contact->countryCode);
            }

            $list[] = implode(', ', $physicalLocation);
          }
        }

        return $list;

      case 'sourceCulture':

        return $this->resource->sourceCulture;

      case 'typeOfResource':

        return $this->resource->getTermRelations(QubitTaxonomy::MODS_RESOURCE_TYPE_ID);

      case 'materialTypes':

        $materialTypes = array();

        foreach ($this->resource->getTermRelations(QubitTaxonomy::MATERIAL_TYPE_ID) as $relation)
        {
          array_push($materialTypes, $relation->term->getName(array('cultureFallback' => true)));
        }

        return $materialTypes;

      case 'languageNotes':

        return $this->getNoteTexts(QubitTerm::LANGUAGE_NOTE_ID);

      case 'alphanumericNotes':

        return $this->getMatchingRadNotesByName('Alpha-numeric designations');

      case 'generalNotes':

        return $this->getMatchingRadNotesByName('General note');

      case 'hasRightsAccess':

        return $this->determineIfResourceHasRightsAct('Display');

      case 'hasRightsReplicate':

        return $this->determineIfResourceHasRightsAct('Replicate');
    }
  }

  public function getMatchingRadNotesByName($noteTypeName)
  {
    foreach (QubitTerm::getRADNotes() as $term)
    {
      if ($term->getName() == $noteTypeName)
      {
        return $this->getNoteTexts($term->id);
      }
    }
  }

  public function getNoteTexts($noteTypeId)
  {
    $notes = array();

    $noteData = $this->resource->getNotesByType(array('noteTypeId' => $noteTypeId));
    foreach ($noteData as $note)
    {
      array_push($notes, $note->getContent(array('cultureFallback' => true)));
    }

    return $notes;
  }

  public function getIdForRightsActTerm($termName)
  {
    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::RIGHT_ACT_ID);
    $criteria->add(QubitTerm::SOURCE_CULTURE, 'en');
    $criteria->addJoin(QubitTermI18n::ID, QubitTerm::ID);
    $criteria->add(QubitTermI18n::NAME, $termName);

    if ($term = QubitTerm::getOne($criteria))
    {
      return $term->id;
    }

    return false;
  }

  public function determineIfResourceHasRightsAct($actName)
  {
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::ID, $this->resource->id);
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitInformationObject::ID);
    $criteria->addJoin(QubitRights::ID, QubitRelation::OBJECT_ID);
    $criteria->add(QubitRights::ACT_ID, $this->getIdForRightsActTerm($actName));

    return QubitRights::getOne($criteria);
  }

  public function offsetGet($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__get'), $args);
  }

  public function offsetSet($offset, $value)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__set'), $args);
  }

  public function offsetUnset($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__unset'), $args);
  }
}
