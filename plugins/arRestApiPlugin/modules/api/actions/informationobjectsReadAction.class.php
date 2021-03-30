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
        if (null === $this->resource = QubitInformationObject::getBySlug($this->request->slug)) {
            throw new QubitApi404Exception('Information object not found');
        }

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            throw new QubitApi404Exception('Information object not found');
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'read')) {
            throw new QubitApiNotAuthorizedException();
        }

        $ioData = [];

        if (QubitInformationObject::ROOT_ID != $this->resource->parent->id) {
            $this->addItemToArray($ioData, 'parent', $this->resource->parent->slug);
        }

        $this->addItemToArray($ioData, 'reference_code', $this->resource->referenceCode);
        $this->addItemToArray($ioData, 'title', $this->resource->getTitle(['cultureFallback' => true]));

        if (null !== $status = $this->resource->getPublicationStatus()) {
            $this->addItemToArray($ioData, 'publication_status', $status->status->getName(['cultureFallback' => true]));
        }

        if (isset($this->resource->levelOfDescription)) {
            $this->addItemToArray($ioData, 'level_of_description', $this->resource->levelOfDescription->getName(['cultureFallback' => true]));
        }

        $this->addItemToArray($ioData, 'extent_and_medium', $this->resource->getCleanExtentAndMedium(['cultureFallback' => true]));

        $dates = [];
        foreach ($this->resource->getDates() as $item) {
            $date = [];

            $this->addItemToArray($date, 'date', $item->getDate(['cultureFallback' => true]));
            $this->addItemToArray($date, 'start_date', $item->startDate);
            $this->addItemToArray($date, 'end_date', $item->endDate);

            if (isset($item->type)) {
                $this->addItemToArray($date, 'type', $item->type->getName(['cultureFallback' => true]));
            }

            $dates[] = $date;
        }

        $this->addItemToArray($ioData, 'dates', $dates);

        foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $ancestor) {
            if (0 < count($ancestor->getCreators())) {
                $creators = [];
                foreach ($ancestor->getCreators() as $item) {
                    $creator = [];

                    $this->addItemToArray($creator, 'authotized_form_of_name', $item->getAuthorizedFormOfName(['cultureFallback' => true]));
                    $this->addItemToArray($creator, 'dates_of_existence', $item->getDatesOfExistence(['cultureFallback' => true]));
                    $this->addItemToArray($creator, 'history', $item->getHistory(['cultureFallback' => true]));

                    if (0 == count($this->resource->getCreators())) {
                        $this->addItemToArray($creator, 'inherited_from', $ancestor->getTitle(['cultureFallback' => true]));
                    }

                    $creators[] = $creator;
                }

                $this->addItemToArray($ioData, 'creators', $creators);

                break;
            }
        }

        if (isset($this->resource->repository)) {
            $this->addItemToArray($ioData, 'repository', $this->resource->repository->getAuthorizedFormOfName(['cultureFallback' => true]));
        } else {
            foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $ancestor) {
                if (isset($ancestor->repository)) {
                    $this->addItemToArray($ioData, 'repository', $ancestor->repository->getAuthorizedFormOfName(['cultureFallback' => true]));
                    $this->addItemToArray($ioData, 'repository_inherited_from', $ancestor->getTitle(['cultureFallback' => true]));

                    break;
                }
            }
        }

        if (sfConfig::get('app_element_visibility_isad_archival_history', false)) {
            $this->addItemToArray($ioData, 'archival_history', $this->resource->getArchivalHistory(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_immediate_source', false)) {
            $this->addItemToArray($ioData, 'immediate_source_of_acquisition_or_transfer', $this->resource->getAcquisition(['cultureFallback' => true]));
        }

        $this->addItemToArray($ioData, 'scope_and_content', $this->resource->getScopeAndContent(['cultureFallback' => true]));

        if (sfConfig::get('app_element_visibility_isad_appraisal_destruction', false)) {
            $this->addItemToArray($ioData, 'appraisal_destruction_and_scheduling', $this->resource->getAppraisal(['cultureFallback' => true]));
        }

        $this->addItemToArray($ioData, 'accruals', $this->resource->getAccruals(['cultureFallback' => true]));
        $this->addItemToArray($ioData, 'system_of_arrangement', $this->resource->getArrangement(['cultureFallback' => true]));
        $this->addItemToArray($ioData, 'conditions_governing_access', $this->resource->getAccessConditions(['cultureFallback' => true]));
        $this->addItemToArray($ioData, 'conditions_governing_reproduction', $this->resource->getReproductionConditions(['cultureFallback' => true]));

        $languages = [];
        foreach ($this->resource->language as $language) {
            $languages[] = ucfirst(sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($language));
        }

        $this->addItemToArray($ioData, 'languages_of_material', $languages);

        $scripts = [];
        foreach ($this->resource->script as $script) {
            $scripts[] = format_script($script);
        }

        $this->addItemToArray($ioData, 'scripts_of_material', $scripts);

        if (null !== $note = $this->resource->getNotesByType(['noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID])->offsetGet(0)) {
            $this->addItemToArray($ioData, 'language_and_script_notes', $note->getContent(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_physical_condition', false)) {
            $this->addItemToArray($ioData, 'physical_characteristics_and_technical_requirements', $this->resource->getPhysicalCharacteristics(['cultureFallback' => true]));
        }

        $this->addItemToArray($ioData, 'finding_aids', $this->resource->getFindingAids(['cultureFallback' => true]));
        $this->addItemToArray($ioData, 'existence_and_location_of_originals', $this->resource->getLocationOfOriginals(['cultureFallback' => true]));
        $this->addItemToArray($ioData, 'existence_and_location_of_copies', $this->resource->getLocationOfCopies(['cultureFallback' => true]));
        $this->addItemToArray($ioData, 'related_units_of_description', $this->resource->getRelatedUnitsOfDescription(['cultureFallback' => true]));

        $relatedDescriptions = [];
        foreach ($this->resource->relationsRelatedBysubjectId as $item) {
            if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id) {
                $relatedDescriptions[] = $item->object->getTitle(['cultureFallback' => true]);
            }
        }

        foreach ($this->resource->relationsRelatedByobjectId as $item) {
            if (isset($item->type) && QubitTerm::RELATED_MATERIAL_DESCRIPTIONS_ID == $item->type->id) {
                $relatedDescriptions[] = $item->subject->getTitle(['cultureFallback' => true]);
            }
        }

        $this->addItemToArray($ioData, 'related_descriptions', $relatedDescriptions);

        $publicationNotes = [];
        foreach ($this->resource->getNotesByType(['noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID]) as $item) {
            $publicationNotes[] = $item->getContent(['cultureFallback' => true]);
        }

        $this->addItemToArray($ioData, 'publication_notes', $publicationNotes);

        if (sfConfig::get('app_element_visibility_isad_notes', false)) {
            $notes = [];
            foreach ($this->resource->getNotesByType(['noteTypeId' => QubitTerm::GENERAL_NOTE_ID]) as $item) {
                $notes[] = $item->getContent(['cultureFallback' => true]);
            }

            $this->addItemToArray($ioData, 'notes', $notes);
        }

        $alternativeIdentifiers = [];
        foreach ($this->resource->getProperties(null, 'alternativeIdentifiers') as $item) {
            $alternativeIdentifier = [];

            $this->addItemToArray($alternativeIdentifier, 'label', $item->name);
            $this->addItemToArray($alternativeIdentifier, 'identifier', $item->getValue(['cultureFallback' => true]));

            $alternativeIdentifiers[] = $alternativeIdentifier;
        }

        $this->addItemToArray($ioData, 'alternative_identifiers', $alternativeIdentifiers);

        $subjects = [];
        foreach ($this->resource->getSubjectAccessPoints() as $item) {
            $subjects[] = $item->term->getName(['cultureFallback' => true]);
        }

        $this->addItemToArray($ioData, 'subject_access_points', $subjects);

        $places = [];
        foreach ($this->resource->getPlaceAccessPoints() as $item) {
            $places[] = $item->term->getName(['cultureFallback' => true]);
        }

        $this->addItemToArray($ioData, 'place_access_points', $places);

        $names = [];
        foreach ($this->resource->getNameAccessPoints() as $item) {
            $names[] = $item->object->getAuthorizedFormOfName(['cultureFallback' => true]);
        }

        $this->addItemToArray($ioData, 'name_access_points', $names);

        if (sfConfig::get('app_element_visibility_isad_control_description_identifier', false)) {
            $this->addItemToArray($ioData, 'description_identifier', $this->resource->getDescriptionIdentifier(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_institution_identifier', false)) {
            $this->addItemToArray($ioData, 'institution_identifier', $this->resource->getInstitutionResponsibleIdentifier(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_rules_conventions', false)) {
            $this->addItemToArray($ioData, 'rules_and_or_conventions_used', $this->resource->getRules(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_status', false) && isset($this->resource->descriptionStatus)) {
            $this->addItemToArray($ioData, 'status', $this->resource->descriptionStatus->getName(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_level_of_detail', false) && isset($this->resource->descriptionDetail)) {
            $this->addItemToArray($ioData, 'level_of_detail', $this->resource->descriptionDetail->getName(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_dates', false)) {
            $this->addItemToArray($ioData, 'Dates of creation revision deletion', $this->resource->getRevisionHistory(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_languages', false)) {
            $languages = [];
            foreach ($this->resource->languageOfDescription as $language) {
                $languages[] = ucfirst(sfCultureInfo::getInstance(sfContext::getInstance()->user->getCulture())->getLanguage($language));
            }

            $this->addItemToArray($ioData, 'languages_of_description', $languages);
        }

        if (sfConfig::get('app_element_visibility_isad_control_scripts', false)) {
            $scripts = [];
            foreach ($this->resource->scriptOfDescription as $script) {
                $scripts[] = format_script($script);
            }

            $this->addItemToArray($ioData, 'scripts_of_description', $scripts);
        }

        if (sfConfig::get('app_element_visibility_isad_control_sources', false)) {
            $this->addItemToArray($ioData, 'physical_characteristics_and_technical_requirements', $this->resource->getSources(['cultureFallback' => true]));
        }

        if (sfConfig::get('app_element_visibility_isad_control_archivists_notes', false)) {
            $archivistsNotes = [];
            foreach ($this->resource->getNotesByType(['noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID]) as $item) {
                $archivistsNotes[] = $item->getContent(['cultureFallback' => true]);
            }

            $this->addItemToArray($ioData, 'archivists_notes', $archivistsNotes);
        }

        $rights = [];
        foreach ($this->resource->getRights() as $right) {
            $right = $right->object;
            $rightData = [];

            if (isset($right->basis)) {
                $this->addItemToArray($rightData, 'basis', $right->basis->getName(['cultureFallback' => true]));
            }

            $this->addItemToArray($rightData, 'start_date', $right->startDate);
            $this->addItemToArray($rightData, 'end_date', $right->endDate);
            $this->addItemToArray($rightData, 'documentation_identifier_type', $right->getIdentifierType(['cultureFallback' => true]));
            $this->addItemToArray($rightData, 'documentation_identifier_value', $right->getIdentifierValue(['cultureFallback' => true]));
            $this->addItemToArray($rightData, 'documentation_identifier_role', $right->getIdentifierRole(['cultureFallback' => true]));

            if (isset($right->rightsHolder)) {
                $this->addItemToArray($rightData, 'rights_holder', $right->rightsHolder->getAuthorizedFormOfName(['cultureFallback' => true]));
            }

            $this->addItemToArray($rightData, 'rights_note', $right->getRightsNote(['cultureFallback' => true]));

            if (QubitTerm::RIGHT_BASIS_COPYRIGHT_ID == $right->basisId) {
                if (isset($right->copyrightStatus)) {
                    $this->addItemToArray($rightData, 'copyright_status', $right->copyrightStatus->getName(['cultureFallback' => true]));
                }

                $this->addItemToArray($rightData, 'copyright_status_date', $right->copyrightStatusDate);
                $this->addItemToArray($rightData, 'copyright_jurisdiction', format_country($right->copyrightJurisdiction));
                $this->addItemToArray($rightData, 'copyright_note', $right->getCopyrightNote(['cultureFallback' => true]));
            } elseif (QubitTerm::RIGHT_BASIS_LICENSE_ID == $right->basisId) {
                $this->addItemToArray($rightData, 'license_identifier', $right->getIdentifierValue(['cultureFallback' => true]));
                $this->addItemToArray($rightData, 'license_terms', $right->getLicenseTerms(['cultureFallback' => true]));
                $this->addItemToArray($rightData, 'license_note', $right->getLicenseNote(['cultureFallback' => true]));
            } elseif (QubitTerm::RIGHT_BASIS_STATUTE_ID == $right->basisId) {
                $this->addItemToArray($rightData, 'statute_jurisdiction', $right->getStatuteJurisdiction(['cultureFallback' => true]));

                if (isset($right->statuteCitation)) {
                    $this->addItemToArray($rightData, 'statute_citation', $right->statuteCitation->getName(['cultureFallback' => true]));
                }

                $this->addItemToArray($rightData, 'statute_determination_date', $right->statuteDeterminationDate);
                $this->addItemToArray($rightData, 'statute_note', $right->getStatuteNote(['cultureFallback' => true]));
            }

            $grantedRights = [];
            foreach ($right->grantedRights as $grantedRight) {
                $grantedRightData = [];

                if (isset($grantedRight->act)) {
                    $this->addItemToArray($grantedRightData, 'act', $grantedRight->act->getName(['cultureFallback' => true]));
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

        if (0 < count($this->resource->digitalObjectsRelatedByobjectId)) {
            $digitalObject = $this->resource->digitalObjectsRelatedByobjectId[0];
            $doData = [];

            if (sfConfig::get('app_element_visibility_digital_object_file_name', false)) {
                $this->addItemToArray($doData, 'filename', $digitalObject->name);
            }

            if (sfConfig::get('app_element_visibility_digital_object_media_type', false) && isset($digitalObject->mediaType)) {
                $this->addItemToArray($doData, 'media_type', $digitalObject->mediaType->getName(['cultureFallback' => true]));
            }

            if (sfConfig::get('app_element_visibility_digital_object_mime_type', false)) {
                $this->addItemToArray($doData, 'mime_type', $digitalObject->mimeType);
            }

            if (sfConfig::get('app_element_visibility_digital_object_file_size', false)) {
                $this->addItemToArray($doData, 'byte_size', $digitalObject->byteSize);
            }

            if (sfConfig::get('app_element_visibility_digital_object_uploaded', false)) {
                $this->addItemToArray($doData, 'uploaded_at', format_date($digitalObject->createdAt, 'f'));
            }

            $this->addItemToArray($doData, 'object_uuid', $digitalObject->object->objectUUID);
            $this->addItemToArray($doData, 'aip_uuid', $digitalObject->object->aipUUID);

            if (sfConfig::get('app_element_visibility_digital_object_url', false)) {
                if (QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId) {
                    $this->addItemToArray($doData, 'url', $digitalObject->getFullPath());
                } elseif (QubitTerm::OFFLINE_ID != $digitalObject->usageId) {
                    $this->addItemToArray($doData, 'url', $this->siteBaseUrl.$digitalObject->getFullPath());
                }

                if (QubitTerm::OFFLINE_ID != $digitalObject->usageId) {
                    if (null !== $reference = $digitalObject->getRepresentationByUsage(QubitTerm::REFERENCE_ID)) {
                        $this->addItemToArray($doData, 'reference_url', $this->siteBaseUrl.$reference->getFullPath());
                    }

                    if (null !== $thumbnail = $digitalObject->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID)) {
                        $this->addItemToArray($doData, 'thumbnail_url', $this->siteBaseUrl.$thumbnail->getFullPath());
                    }
                }
            }

            $this->addItemToArray($ioData, 'digital_object', $doData);
        }

        return $ioData;
    }
}
