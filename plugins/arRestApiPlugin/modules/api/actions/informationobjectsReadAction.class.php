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

class ApiInformationObjectsReadAction extends QubitApiAction
{
  protected function get($request)
  {
    if (null === $this->resource = QubitInformationObject::getBySlug($this->request->slug))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'read'))
    {
      throw new QubitApiNotAuthorizedException;
    }

    $ioData = array();

    if ($this->resource->parent->id != QubitInformationObject::ROOT_ID)
    {
      $this->addItemToArray($ioData, 'parent', $this->resource->parent->slug);
    }

    $this->addItemToArray($ioData, 'reference_code', $this->resource->referenceCode);
    $this->addItemToArray($ioData, 'title', $this->resource->getTitle(array('cultureFallback' => true)));

    if (null !== $status = $this->resource->getPublicationStatus())
    {
      $this->addItemToArray($ioData, 'publication_status', $status->status->getName(array('cultureFallback' => true)));
    }

    if (isset($this->resource->levelOfDescription))
    {
      $this->addItemToArray($ioData, 'level_of_description', $this->resource->levelOfDescription->getName(array('cultureFallback' => true)));
    }

    $this->addItemToArray($ioData, 'extent_and_medium', $this->resource->getCleanExtentAndMedium(array('cultureFallback' => true)));

    $dates = array();
    foreach ($this->resource->getDates() as $item)
    {
      $date = array();

      $this->addItemToArray($date, 'date', $item->getDate(array('cultureFallback' => true)));
      $this->addItemToArray($date, 'start_date', $item->startDate);
      $this->addItemToArray($date, 'end_date', $item->endDate);

      if (isset($item->type))
      {
        $this->addItemToArray($date, 'type', $item->type->getName(array('cultureFallback' => true)));
      }

      $dates[] = $date;
    }

    $this->addItemToArray($ioData, 'dates', $dates);

    foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $ancestor)
    {
      if (0 < count($ancestor->getCreators()))
      {
        $creators = array();
        foreach ($ancestor->getCreators() as $item)
        {
          $creator = array();

          $this->addItemToArray($creator, 'authotized_form_of_name', $item->getAuthorizedFormOfName(array('cultureFallback' => true)));
          $this->addItemToArray($creator, 'dates_of_existence', $item->getDatesOfExistence(array('cultureFallback' => true)));
          $this->addItemToArray($creator, 'history', $item->getHistory(array('cultureFallback' => true)));

          if (0 == count($this->resource->getCreators()))
          {
            $this->addItemToArray($creator, 'inherited_from', $ancestor->getTitle(array('cultureFallback' => true)));
          }

          $creators[] = $creator;
        }

        $this->addItemToArray($ioData, 'creators', $creators);

        break;
      }
    }

    if (isset($this->resource->repository))
    {
      $this->addItemToArray($ioData, 'repository', $this->resource->repository->getAuthorizedFormOfName(array('cultureFallback' => true)));
    }
    else
    {
      foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $ancestor)
      {
        if (isset($ancestor->repository))
        {
          $this->addItemToArray($ioData, 'repository', $ancestor->repository->getAuthorizedFormOfName(array('cultureFallback' => true)));
          $this->addItemToArray($ioData, 'repository_inherited_from', $ancestor->getTitle(array('cultureFallback' => true)));
        
          break;
        }
      }
    }

    if (sfConfig::get('app_element_visibility_isad_archival_history', false))
    {
      $this->addItemToArray($ioData, 'archival_history', $this->resource->getArchivalHistory(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_immediate_source', false))
    {
      $this->addItemToArray($ioData, 'immediate_source_of_acquisition_or_transfer', $this->resource->getAcquisition(array('cultureFallback' => true)));
    }

    $this->addItemToArray($ioData, 'scope_and_content', $this->resource->getScopeAndContent(array('cultureFallback' => true)));

    if (sfConfig::get('app_element_visibility_isad_appraisal_destruction', false))
    {
      $this->addItemToArray($ioData, 'appraisal_destruction_and_scheduling', $this->resource->getAppraisal(array('cultureFallback' => true)));
    }

    $this->addItemToArray($ioData, 'accruals', $this->resource->getAccruals(array('cultureFallback' => true)));
    $this->addItemToArray($ioData, 'system_of_arrangement', $this->resource->getArrangement(array('cultureFallback' => true)));
    $this->addItemToArray($ioData, 'conditions_governing_access', $this->resource->getAccessConditions(array('cultureFallback' => true)));
    $this->addItemToArray($ioData, 'conditions_governing_reproduction', $this->resource->getReproductionConditions(array('cultureFallback' => true)));

    $languages = array();
    foreach ($this->resource->language as $language)
    {
      $languages[] = ucfirst(sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($language));
    }

    $this->addItemToArray($ioData, 'languages_of_material', $languages);

    $scripts = array();
    foreach ($this->resource->script as $script)
    {
      $scripts[] = format_script($script);
    }

    $this->addItemToArray($ioData, 'scripts_of_material', $scripts);

    if (null !== $note = $this->resource->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID))->offsetGet(0))
    {
      $this->addItemToArray($ioData, 'language_and_script_notes', $note->getContent(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_physical_condition', false))
    {
      $this->addItemToArray($ioData, 'physical_characteristics_and_technical_requirements', $this->resource->getPhysicalCharacteristics(array('cultureFallback' => true)));
    }

    $this->addItemToArray($ioData, 'finding_aids', $this->resource->getFindingAids(array('cultureFallback' => true)));
    $this->addItemToArray($ioData, 'existence_and_location_of_originals', $this->resource->getLocationOfOriginals(array('cultureFallback' => true)));
    $this->addItemToArray($ioData, 'existence_and_location_of_copies', $this->resource->getLocationOfCopies(array('cultureFallback' => true)));
    $this->addItemToArray($ioData, 'related_units_of_description', $this->resource->getRelatedUnitsOfDescription(array('cultureFallback' => true)));

    $relatedDescriptions = array();
    foreach ($this->resource->relationsRelatedBysubjectId as $item)
    {
      if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id)
      {
        $relatedDescriptions[] = $item->object->getTitle(array('cultureFallback' => true));
      }
    }

    foreach ($this->resource->relationsRelatedByobjectId as $item)
    {
      if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id)
      {
        $relatedDescriptions[] = $item->subject->getTitle(array('cultureFallback' => true));
      }
    }
    
    $this->addItemToArray($ioData, 'related_descriptions', $relatedDescriptions);

    $publicationNotes = array();
    foreach ($this->resource->getNotesByType(array('noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID)) as $item)
    {
      $publicationNotes[] = $item->getContent(array('cultureFallback' => true));
    }
    
    $this->addItemToArray($ioData, 'publication_notes', $publicationNotes);

    if (sfConfig::get('app_element_visibility_isad_notes', false))
    {
      $notes = array();
      foreach ($this->resource->getNotesByType(array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID)) as $item)
      {
        $notes[] = $item->getContent(array('cultureFallback' => true));
      }
      
      $this->addItemToArray($ioData, 'notes', $notes);
    }

    $alternativeIdentifiers = array();
    foreach ($this->resource->getProperties(null, 'alternativeIdentifiers') as $item)
    {
      $alternativeIdentifier = array();

      $this->addItemToArray($alternativeIdentifier, 'label', $item->name);
      $this->addItemToArray($alternativeIdentifier, 'identifier', $item->getValue(array('cultureFallback' => true)));

      $alternativeIdentifiers[] = $alternativeIdentifier;
    }

    $this->addItemToArray($ioData, 'alternative_identifiers', $alternativeIdentifiers);

    $subjects = array();
    foreach ($this->resource->getSubjectAccessPoints() as $item)
    {
      $subjects[] = $item->term->getName(array('cultureFallback' => true));
    }

    $this->addItemToArray($ioData, 'subject_access_points', $subjects);

    $places = array();
    foreach ($this->resource->getPlaceAccessPoints() as $item)
    {
      $places[] = $item->term->getName(array('cultureFallback' => true));
    }

    $this->addItemToArray($ioData, 'place_access_points', $places);

    $names = array();
    foreach ($this->resource->getNameAccessPoints() as $item)
    {
      $names[] = $item->object->getAuthorizedFormOfName(array('cultureFallback' => true));
    }

    $this->addItemToArray($ioData, 'name_access_points', $names);

    if (sfConfig::get('app_element_visibility_isad_control_description_identifier', false))
    {
      $this->addItemToArray($ioData, 'description_identifier', $this->resource->getDescriptionIdentifier(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_institution_identifier', false))
    {
      $this->addItemToArray($ioData, 'institution_identifier', $this->resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_rules_conventions', false))
    {
      $this->addItemToArray($ioData, 'rules_and_or_conventions_used', $this->resource->getRules(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_status', false) && isset($this->resource->descriptionStatus))
    {
      $this->addItemToArray($ioData, 'status', $this->resource->descriptionStatus->getName(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_level_of_detail', false) && isset($this->resource->descriptionDetail))
    {
      $this->addItemToArray($ioData, 'level_of_detail', $this->resource->descriptionDetail->getName(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_dates', false))
    {
      $this->addItemToArray($ioData, 'Dates of creation revision deletion', $this->resource->getRevisionHistory(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_languages', false))
    {
      $languages = array();
      foreach ($this->resource->languageOfDescription as $language)
      {
        $languages[] = ucfirst(sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($language));
      }

      $this->addItemToArray($ioData, 'languages_of_description', $languages);
    }

    if (sfConfig::get('app_element_visibility_isad_control_scripts', false))
    {
      $scripts = array();
      foreach ($this->resource->scriptOfDescription as $script)
      {
        $scripts[] = format_script($script);
      }

      $this->addItemToArray($ioData, 'scripts_of_description', $scripts);
    }

    if (sfConfig::get('app_element_visibility_isad_control_sources', false))
    {
      $this->addItemToArray($ioData, 'physical_characteristics_and_technical_requirements', $this->resource->getSources(array('cultureFallback' => true)));
    }

    if (sfConfig::get('app_element_visibility_isad_control_archivists_notes', false))
    {
      $archivistsNotes = array();
      foreach ($this->resource->getNotesByType(array('noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID)) as $item)
      {
        $archivistsNotes[] = $item->getContent(array('cultureFallback' => true));
      }
      
      $this->addItemToArray($ioData, 'archivists_notes', $archivistsNotes);      
    }

    $rights = array();
    foreach ($this->resource->getRights() as $right)
    {
      $right = $right->object;
      $rightData = array();

      if (isset($right->basis))
      {
        $this->addItemToArray($rightData, 'basis', $right->basis->getName(array('cultureFallback' => true)));      
      }

      $this->addItemToArray($rightData, 'start_date', $right->startDate);      
      $this->addItemToArray($rightData, 'end_date', $right->endDate);
      $this->addItemToArray($rightData, 'documentation_identifier_type', $right->getIdentifierType(array('cultureFallback' => true)));
      $this->addItemToArray($rightData, 'documentation_identifier_value', $right->getIdentifierValue(array('cultureFallback' => true)));
      $this->addItemToArray($rightData, 'documentation_identifier_role', $right->getIdentifierRole(array('cultureFallback' => true)));

      if (isset($right->rightsHolder))
      {
        $this->addItemToArray($rightData, 'rights_holder', $right->rightsHolder->getAuthorizedFormOfName(array('cultureFallback' => true)));      
      }

      $this->addItemToArray($rightData, 'rights_note', $right->getRightsNote(array('cultureFallback' => true)));


      if (QubitTerm::RIGHT_BASIS_COPYRIGHT_ID == $right->basisId)
      {
        if (isset($right->copyrightStatus))
        {
          $this->addItemToArray($rightData, 'copyright_status', $right->copyrightStatus->getName(array('cultureFallback' => true)));      
        }

        $this->addItemToArray($rightData, 'copyright_status_date', $right->copyrightStatusDate);
        $this->addItemToArray($rightData, 'copyright_jurisdiction', format_country($right->copyrightJurisdiction));
        $this->addItemToArray($rightData, 'copyright_note', $right->getCopyrightNote(array('cultureFallback' => true)));
      }
      else if (QubitTerm::RIGHT_BASIS_LICENSE_ID == $right->basisId)
      {
        $this->addItemToArray($rightData, 'license_identifier', $right->getIdentifierValue(array('cultureFallback' => true)));
        $this->addItemToArray($rightData, 'license_terms', $right->getLicenseTerms(array('cultureFallback' => true)));
        $this->addItemToArray($rightData, 'license_note', $right->getLicenseNote(array('cultureFallback' => true)));
      }
      else if (QubitTerm::RIGHT_BASIS_STATUTE_ID == $right->basisId)
      {
        $this->addItemToArray($rightData, 'statute_jurisdiction', $right->getStatuteJurisdiction(array('cultureFallback' => true)));

        if (isset($right->statuteCitation))
        {
          $this->addItemToArray($rightData, 'statute_citation', $right->statuteCitation->getName(array('cultureFallback' => true)));      
        }

        $this->addItemToArray($rightData, 'statute_determination_date', $right->statuteDeterminationDate);
        $this->addItemToArray($rightData, 'statute_note', $right->getStatuteNote(array('cultureFallback' => true)));
      }

      $grantedRights = array();
      foreach ($right->grantedRights as $grantedRight)
      {
        $grantedRightData = array();

        if (isset($grantedRight->act))
        {
          $this->addItemToArray($grantedRightData, 'act', $grantedRight->act->getName(array('cultureFallback' => true)));      
        }

        $this->addItemToArray($grantedRightData, 'restriction', QubitGrantedRight::getRestrictionString($grantedRight->restriction));      
        $this->addItemToArray($grantedRightData, 'start_date', $grantedRight->startDate);
        $this->addItemToArray($grantedRightData, 'end_date', $grantedRight->endDate);
        $this->addItemToArray($grantedRightData, 'notes', $grantedRight->notes);

        $grantedRights[] = $grantedRightData;
      }

      $this->addItemToArray($rightData, 'granted_rights', $grantedRights);

      $rights[] = $rightData;
    }

    $this->addItemToArray($ioData, 'rights', $rights);      

    if (0 < count($this->resource->digitalObjects))
    {
      $digitalObject = $this->resource->digitalObjects[0];
      $doData = array();

      if (sfConfig::get('app_element_visibility_digital_object_file_name', false))
      {
        $this->addItemToArray($doData, 'filename', $digitalObject->name);
      }

      if (sfConfig::get('app_element_visibility_digital_object_media_type', false) && isset($digitalObject->mediaType))
      {
        $this->addItemToArray($doData, 'media_type', $digitalObject->mediaType->getName(array('cultureFallback' => true)));
      }

      if (sfConfig::get('app_element_visibility_digital_object_mime_type', false))
      {
        $this->addItemToArray($doData, 'mime_type', $digitalObject->mimeType);
      }

      if (sfConfig::get('app_element_visibility_digital_object_file_size', false))
      {
        $this->addItemToArray($doData, 'byte_size', $digitalObject->byteSize);
      }

      if (sfConfig::get('app_element_visibility_digital_object_uploaded', false))
      {
        $this->addItemToArray($doData, 'uploaded_at', format_date($digitalObject->createdAt, 'f'));
      }

      $this->addItemToArray($doData, 'object_uuid', $digitalObject->informationObject->objectUUID);
      $this->addItemToArray($doData, 'aip_uuid', $digitalObject->informationObject->aipUUID);

      if (sfConfig::get('app_element_visibility_digital_object_url', false))
      {
        if (QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId)
        {
          $this->addItemToArray($doData, 'url', $digitalObject->getFullPath());
        }
        else if (QubitTerm::OFFLINE_ID != $digitalObject->usageId)
        {
          $this->addItemToArray($doData, 'url', $this->siteBaseUrl . $digitalObject->getFullPath());
        }

        if (QubitTerm::OFFLINE_ID != $digitalObject->usageId)
        {
          if (null !== $reference = $digitalObject->getRepresentationByUsage(QubitTerm::REFERENCE_ID))
          {
            $this->addItemToArray($doData, 'reference_url', $this->siteBaseUrl . $reference->getFullPath());
          }

          if (null !== $thumbnail = $digitalObject->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID))
          {
            $this->addItemToArray($doData, 'thumbnail_url', $this->siteBaseUrl . $thumbnail->getFullPath());
          }
        }
      }

      $this->addItemToArray($ioData, 'digital_object', $doData);
    }

    return $ioData;
  }
}
