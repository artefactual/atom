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

class ApiInformationObjectsUpdateAction extends QubitApiAction
{
    private $noteToDelete; // To specify a note to delete after updating
    private $relationsToDelete = []; // To specify relations to delete after updating
    private $propertiesToDelete = []; // To specify properties to delete after updating

    protected function put($request, $payload)
    {
        if (null === $this->io = QubitObject::getBySlug($request->slug)) {
            throw new QubitApi404Exception('Information object not found');
        }

        if (QubitInformationObject::ROOT_ID === (int) $this->io->id) {
            throw new QubitApiForbiddenException();
        }

        if (!QubitAcl::check($this->io, 'update')) {
            throw new QubitApiNotAuthorizedException();
        }

        foreach ($payload as $field => $value) {
            $this->processField($field, $value);
        }

        $this->io->save();

        if (isset($this->noteToDelete)) {
            $this->noteToDelete->delete();
        }

        foreach ($this->relationsToDelete as $relation) {
            $relation->delete();
        }

        foreach ($this->propertiesToDelete as $property) {
            $property->delete();
        }

        return [
            'id' => (int) $this->io->id,
            'parent_id' => (int) $this->io->parentId,
        ];
    }

    protected function processField($field, $value)
    {
        switch ($field) {
            case 'identifier':
            case 'level_of_description_id':
            case 'title':
            case 'extentAndMedium':
            case 'locationOfOriginals':
            case 'locationOfCopies':
            case 'scopeAndContent':
            case 'accessConditions':
            case 'archivalHistory':
            case 'acquisition':
            case 'revisionHistory':
            case 'arrangement':
            case 'accruals':
            case 'sources':
            case 'appraisal':
            case 'reproductionConditions':
            case 'descriptionIdentifier':
            case 'institutionResponsibleIdentifier':
            case 'relatedUnitsOfDescription':
            case 'rules':
            case 'physicalCharacteristics':
            case 'revisionHistory':
                $field = lcfirst(sfInflector::camelize($field));
                $this->io->{$field} = $value;

                break;

            case 'language':
            case 'languageOfDescription':
            case 'script':
            case 'scriptOfDescription':
                // Delete existing properties of this type from the database
                $criteria = new Criteria();
                $criteria->add(QubitProperty::OBJECT_ID, $this->io->id);
                $criteria->add(QubitProperty::NAME, $field);
                foreach (QubitProperty::get($criteria) as $property) {
                    $property->delete();
                }

                $values = explode('|', $value);
                if ('script' === $field || 'scriptOfDescription' === $field) {
                    $fields = array_keys(sfCultureInfo::getInstance()->getScripts());
                } else {
                    $fields = array_keys(sfCultureInfo::getInstance()->getLanguages());
                }

                foreach ($values as $valueIndex => $val) {
                    // Fail on invalid value (normalizing by case when checking value validity)
                    if (false === $vocabularyIndex = array_search(strtolower($val), array_map('strtolower', $fields))) {
                        throw new QubitApiBadRequestException(sprintf('Invalid %s: %s', $field, $val));
                    }

                    // Normalize case of value
                    $values[$valueIndex] = $fields[$vocabularyIndex];
                }

                // Create property manually rather than using addProperty model methods
                // as they are implemented inconsistently
                $property = new QubitProperty();
                $property->name = $field;
                $property->setValue(serialize(array_unique($values)), ['sourceCulture' => true]);

                $this->io->propertys[] = $property;

                break;

            case 'descriptionStatusId':
            case 'descriptionDetailId':
                $taxonomyId = [
                    'descriptionDetailId' => QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID,
                    'descriptionStatusId' => QubitTaxonomy::DESCRIPTION_STATUS_ID,
                ];
                $descriptionMap = [];
                foreach (QubitTaxonomy::getTermsById($taxonomyId[$field]) as $item) {
                    $descriptionMap[$item->name] = $item->id;
                }

                if (!empty($value) && array_key_exists(ucwords($value), $descriptionMap)) {
                    $this->io->{$field} = $descriptionMap[ucwords($value)];
                }

                break;

            case 'nameAccessPoints':
                // Multi-value supported
                if (!is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $item) {
                    if (empty($item)) {
                        continue;
                    }

                    $actor = null;

                    // Find or create actor
                    if (isset($item->name) && !isset($item->actor_id)) {
                        $name = trim(preg_replace('/\s+/', ' ', $item->name));
                        $actor = QubitActor::getByAuthorizedFormOfName($name, ['includeHistory' => true]);
                        if (!$actor) {
                            $actor = new QubitActor();
                            $actor->authorizedFormOfName = $name;
                            $actor->save();
                        }
                    } elseif (isset($item->actor_id)) {
                        $actor = QubitActor::getById($item->actor_id);
                        if (!$actor) {
                            throw new QubitApiBadRequestException('Invalid actor_id for nameAccessPoints');
                        }
                    } else {
                        throw new QubitApiBadRequestException('nameAccessPoints requires either name or actor_id');
                    }

                    if ($actor) {
                        // Create relation with information object as subject and actor as object
                        $relation = new QubitRelation();
                        $relation->subjectId = $this->io->id;
                        $relation->objectId = $actor->id;
                        $relation->typeId = QubitTerm::NAME_ACCESS_POINT_ID;

                        // Add to the information object's relations array
                        $this->io->relationsRelatedBysubjectId[] = $relation;
                    }
                }

                break;

            case 'placeAccessPoints':
            case 'genreAccessPoints':
            case 'subjectAccessPoints':
                $taxonomy = [
                    'placeAccessPoints' => QubitTaxonomy::PLACE_ID,
                    'genreAccessPoints' => QubitTaxonomy::GENRE_ID,
                    'subjectAccessPoints' => QubitTaxonomy::SUBJECT_ID,
                ];
                // Multi-value supported
                if (!is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $item) {
                    if (empty($item)) {
                        continue;
                    }

                    $term = null;

                    // Try to find existing term by ID or name
                    if (!empty($item->id)) {
                        $term = QubitTerm::getById($item->id);

                        // Verify term belongs to Subject taxonomy
                        if ($term && $term->taxonomyId !== $taxonomy[$field]) {
                            $term = null;
                        }
                    }

                    if (!$term && !empty($item->name)) {
                        // Search for term in Subject taxonomy
                        $criteria = new Criteria();
                        $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomy[$field]);
                        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                        $criteria->add(QubitTermI18n::NAME, $item->name);

                        $term = QubitTerm::getOne($criteria);
                    }

                    // Create new term if not found
                    if (!$term && !empty($item->name)) {
                        $term = new QubitTerm();
                        $term->taxonomyId = $taxonomy[$field];
                        $term->name = $item->name;
                        $term->save();
                    }

                    if ($term) {
                        // Create relation
                        $relation = new QubitObjectTermRelation();
                        $relation->termId = $term->id;

                        // Add to information object's term relations array
                        $this->io->objectTermRelationsRelatedByobjectId[] = $relation;
                    }
                }

                break;

            case 'dates':
                // Multi-value supported
                if (!is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $item) {
                    if (empty($item)) {
                        break;
                    }

                    $event = new QubitEvent();
                    $event->startDate = $item->start_date ?? null;
                    $event->endDate = $item->end_date ?? null;
                    $event->date = $item->date ?? null;
                    if (isset($item->type) && 'accumulation' == strtolower($item->type)) {
                            $event->typeId = QubitTerm::ACCUMULATION_ID;
                    } else {
                        $event->typeId = QubitTerm::CREATION_ID;
                    }

                    $this->io->eventsRelatedByobjectId[] = $event;
                }

                break;

            case 'types':
                // Multi-value not supported yet!
                if (is_array($value)) {
                    $value = array_pop($value);
                }
                if (empty($value)) {
                    break;
                }
                $relation = false;
                foreach ($this->io->getTermRelations(QubitTaxonomy::DC_TYPE_ID) as $item) {
                    $relation = $item;

                    break;
                }
                if (false !== $relation) {
                    $relation->termId = $value->id;
                    $relation->save();
                } else {
                    $relation = new QubitObjectTermRelation();
                    $relation->termId = $value->id;

                    $this->io->objectTermRelationsRelatedByobjectId[] = $relation;
                }

                break;

            case 'level_of_description':
                $criteria = new Criteria();
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
                $criteria->add(QubitTermI18n::NAME, $value, Criteria::LIKE);
                if (null !== $term = QubitTerm::getOne($criteria)) {
                    $this->io->levelOfDescriptionId = $term->id;
                }

                break;

            case 'actor':
                if (is_array($value) || empty($value)) {
                    break;
                }

                $actor = null;

                // Normalize incoming name to avoid mismatches due to extra
                // whitespace or stray control characters.
                if (!empty($value->authorized_form_of_name)) {
                    $name = trim(preg_replace('/\s+/', ' ', (string) $value->authorized_form_of_name));
                } else {
                    $name = null;
                }

                if (!empty($value->id) && !empty($value->history)) {
                    $actor = QubitActor::getById($value->id, ['history' => $value->history]);
                } elseif (!empty($value->id)) {
                    $actor = QubitActor::getById($value->id);
                } elseif (!empty($name) && !empty($value->history)) {
                    // First try matching including history
                    $actor = QubitActor::getByAuthorizedFormOfName($name, ['history' => $value->history]);

                    // If not found, fall back to matching without history
                    if (null === $actor) {
                        $actor = QubitActor::getByAuthorizedFormOfName($name);
                    }
                } elseif (!empty($name)) {
                    $actor = QubitActor::getByAuthorizedFormOfName($name);
                }

                if (null === $actor) {
                    $actor = new QubitActor();
                    if (null !== $name) {
                        $actor->authorizedFormOfName = $name;
                    }
                    if (!empty($value->history)) {
                        $actor->history = $value->history;
                    }
                    $actor->save();
                }

                // Create a relation between the information object and the actor
                $event = new QubitEvent();
                $event->typeId = !empty($value->event_type_id)
                    ? $value->event_type_id
                    : QubitTerm::CREATION_ID; // Default to creation event
                $event->actorId = $actor->id;

                $this->io->relationsRelatedByobjectId[] = $event;

                break;

            case 'repository':
                // Only 1 repo can be linked to a description
                if (is_array($value) || empty($value)) {
                    break;
                }

                if (!empty($value->slug)) {
                    $repository = QubitRepository::getBySlug($value->slug);
                } elseif (!empty($value->id)) {
                    $repository = QubitRepository::getById($value->id);
                } elseif (!empty($value->name)) {
                    $repository = new QubitRepository();
                    $repository->setAuthorizedFormOfName($value->name);
                    $repository->save();
                }
                $this->io->setRepositoryId($repository->id);

                break;

            case 'accessionNumber':
                if (!is_array($value)) {
                    $value = [$value];
                }

                // Get existing accession relations for deletion
                if (!empty($this->io->relationsRelatedBysubjectId)) {
                    foreach ($this->io->relationsRelatedBysubjectId as $relation) {
                        if (QubitTerm::ACCESSION_ID === $relation->typeId) {
                            $this->relationsToDelete[] = $relation;
                        }
                    }
                }

                foreach ($value as $item) {
                    $criteria = new Criteria();
                    $criteria->add(QubitAccession::IDENTIFIER, $item);

                    if (null === $accession = QubitAccession::getone($criteria)) {
                        // Create new accession
                        $accession = new QubitAccession();
                        $accession->identifier = $item;
                        $accession->save();
                    }

                    // Create relation object
                    $relation = new QubitRelation();
                    $relation->objectId = $accession->id;
                    $relation->typeId = QubitTerm::ACCESSION_ID;

                    // Add to the information object's relations array
                    // When $this->io->save() is called, Symfony will automatically
                    // save this relation WITH the correct subject ID
                    $this->io->relationsRelatedBysubjectId[] = $relation;
                }

                break;

            case 'digitalObjectURI':
                empty($this->io->digitalObjectsRelatedByobjectId) ? $do = new QubitDigitalObject() : $do = $this->io->digitalObjectsRelatedByobjectId[0];

                try {
                    $do->usageId = QubitTerm::EXTERNAL_URI_ID;
                    $do->path = $value;
                    $do->name = basename(parse_url($value, PHP_URL_PATH));
                    $do->createDerivatives = false;
                    $do->save();

                    // Link to information object
                    $this->io->digitalObjectsRelatedByobjectId[] = $do;
                } catch (Exception $e) {
                    $this->log($e->getMessage(), sfLogger::ERR);
                }

                break;

            case 'digitalObjectPath':
                empty($this->io->digitalObjectsRelatedByobjectId) ? $digitalObject = new QubitDigitalObject() : $digitalObject = $this->io->digitalObjectsRelatedByobjectId[0];

                try {
                    // Verify file exists and is readable
                    if (!is_file($value) || !is_readable($value)) {
                        throw new QubitApiBadRequestException("File not found or not readable: {$value}");
                    }

                    // Create digital object
                    $digitalObject->usageId = QubitTerm::MASTER_ID;

                    // Import the file as an asset
                    $digitalObject->assets[] = new QubitAsset($value);

                    // Set object properties
                    $digitalObject->name = basename($value);

                    // Add to information object's digital objects array
                    // This will be saved automatically when $this->io->save() is called
                    $this->io->digitalObjectsRelatedByobjectId[] = $digitalObject;
                } catch (Exception $e) {
                    $this->log($e->getMessage(), sfLogger::ERR);
                }

                break;

            case 'languageNote':
            case 'archivistNote':
            case 'publicationNote':
            case 'generalNote':
                $noteId = [
                    'archivistNote' => QubitTerm::ARCHIVIST_NOTE_ID,
                    'publicationNote' => QubitTerm::PUBLICATION_NOTE_ID,
                    'languageNote' => QubitTerm::LANGUAGE_NOTE_ID,
                    'generalNote' => QubitTerm::GENERAL_NOTE_ID,
                ];

                $note = false;

                // Multi-value not supported yet!
                if (is_array($value)) {
                    $value = array_pop($value);
                }

                if (empty($value)) {
                    break;
                }

                $notes = $this->io->getNotes();
                foreach ($notes as $item) {
                    if ($item->typeId === $noteId[$field]) {
                        $item->delete();
                    }
                }

                // Create new note
                $note = new QubitNote();
                $note->typeId = $noteId[$field];
                $note->content = $value;

                $this->io->notes[] = $note;

                break;

            case 'publicationStatus':
                if (empty($value) && ('draft' !== strtolower($value) || 'published' !== strtolower($value))) {
                    break;
                }

                $publicationStatusId = ('published' === strtolower($value))
                    ? QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID
                    : QubitTerm::PUBLICATION_STATUS_DRAFT_ID;

                $this->io->setPublicationStatus($publicationStatusId);

                break;

            case 'alternativeIds':
                if (empty($value)) {
                    break;
                }

                foreach ($value as $altId => $altIdLabel) {
                    $altIds[] = $altId;
                    $altIdLabels[] = $altIdLabel;
                }

                if (count($altIdLabels) !== count($altIds)) {
                    throw new QubitApiBadRequestException('Number of alternative ids does not match number of alt id labels');
                }

                for ($i = 0; $i < count($altIds); ++$i) {
                    $this->io->addProperty($altIdLabels[$i], $altIds[$i], ['scope' => 'alternativeIdentifiers']);
                }

                $this->io->save();

                break;
        }
    }

    protected function getNoteTypeData()
    {
        return $this->simplifyTermData(QubitTerm::getNoteTypes());
    }

    protected function getRadNoteTypeData()
    {
        return $this->simplifyTermData(QubitTerm::getRADNotes());
    }

    protected function simplifyTermData($terms)
    {
        $termData = [];

        foreach ($terms as $term) {
            $termData[$term->id] = $term->name;
        }

        return $termData;
    }
}
