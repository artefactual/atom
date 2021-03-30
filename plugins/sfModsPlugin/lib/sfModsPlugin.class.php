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
 * presentation features that are specific to the Metadata Object Description Schema (MODS) standard.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfModsPlugin implements ArrayAccess
{
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function __toString()
    {
        $string = '';

        // Add title if set
        if (0 < strlen($title = $this->resource->__toString())) {
            $string .= $title;
        }

        // Add publication status
        $publicationStatus = $this->resource->getPublicationStatus();
        if (isset($publicationStatus) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatus->statusId) {
            $string .= (!empty($string)) ? ' ' : '';
            $string .= "({$publicationStatus->status->__toString()})";
        }

        return $string;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'identifier':
                return $this->resource->referenceCode;

            case 'baseurl':
                return $this->baseUrl();

            case 'uri':
                return $this->baseUrl().'/index.php/'.$this->resource->slug;

            case 'name':
                $name = [];
                foreach ($this->resource->getActorEvents() as $item) {
                    if (isset($item->actor)) {
                        $name[] = $item;
                    }
                }

                return $name;

            case 'sourceCulture':
                return $this->resource->sourceCulture;

            case 'typeOfResource':
                return $this->resource->getTermRelations(QubitTaxonomy::MODS_RESOURCE_TYPE_ID);

            case 'typeOfResourceForXml':
                $typeOfResources = [];

                // Map to translate RAD GMD terms to MODS resource types
                $map = [
                    'architectural drawing' => 'still image',
                    'cartographic material' => 'cartographic',
                    'graphic material' => 'still image',
                    'moving images' => 'moving image',
                    'multiple media' => 'mixed material',
                    'object' => 'three dimensional object',
                    'philatelic record' => 'still image',
                    'sound recording' => 'sound recording',
                    'technical drawing' => 'still image',
                    'textual record' => 'text',
                ];

                // Real MODS resource types
                foreach ($this->resource->getTermRelations(QubitTaxonomy::MODS_RESOURCE_TYPE_ID) as $relation) {
                    $typeOfResources[] = $relation->term->getName(['culture' => 'en']);
                }

                // Translated RAD material types
                foreach ($this->resource->getTermRelations(QubitTaxonomy::MATERIAL_TYPE_ID) as $relation) {
                    $gmd = trim(strtolower($relation->term->getName(['culture' => 'en'])));

                    if (isset($map[$gmd])) {
                        $typeOfResources[] = $map[$gmd];
                    }
                }

                // Return without duplicates
                return array_unique($typeOfResources);

            case 'genres':
                $genres = [];

                foreach ($this->resource->getTermRelations(QubitTaxonomy::GENRE_ID) as $relation) {
                    array_push($genres, $relation->term->getName(['cultureFallback' => true]));
                }

                return $genres;

            case 'languageNotes':
                return $this->getNoteTexts(QubitTerm::LANGUAGE_NOTE_ID);

            case 'alphanumericNotes':
                return $this->getMatchingRadNotesByName('Alpha-numeric designations');

            case 'generalNotes':
                return $this->getNoteTexts(QubitTerm::GENERAL_NOTE_ID);

            case 'radGeneralNotes':
                return $this->getMatchingRadNotesByName('General note');

            case 'hasRightsAccess':
                return $this->determineIfResourceHasRightsAct('Display');

            case 'hasRightsReplicate':
                return $this->determineIfResourceHasRightsAct('Replicate');
        }
    }

    public function levelOfDescriptionAndIdentifier()
    {
        $string = '';

        if (isset($this->resource->levelOfDescription)) {
            $string .= $this->resource->levelOfDescription->__toString();
        }

        if (isset($this->resource->identifier)) {
            $string .= (!empty($string)) ? ' ' : '';
            $string .= $this->resource->identifier;
        }

        return $string;
    }

    public function offsetExists($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__isset'], $args);
    }

    public function getDateTagNameForEventType($typeId)
    {
        switch ($typeId) {
            case QubitTerm::CREATION_ID:
                return 'dateCreated';

            case QubitTerm::PUBLICATION_ID:
                return 'dateIssued';

            default:
                return 'dateOther';
        }
    }

    public function getMatchingRadNotesByName($noteTypeName)
    {
        foreach (QubitTerm::getRADNotes() as $term) {
            if ($term->getName() == $noteTypeName) {
                return $this->getNoteTexts($term->id);
            }
        }
    }

    public function getNoteTexts($noteTypeId)
    {
        $notes = [];

        $noteData = $this->resource->getNotesByType(['noteTypeId' => $noteTypeId]);
        foreach ($noteData as $note) {
            array_push($notes, $note->getContent(['cultureFallback' => true]));
        }

        return $notes;
    }

    public function getIdForRightsActTerm($termName)
    {
        $criteria = new Criteria();
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::RIGHT_ACT_ID);
        $criteria->add(QubitTerm::SOURCE_CULTURE, 'en');
        $criteria->addJoin(QubitTermI18n::ID, QubitTerm::ID);
        $criteria->add(QubitTermI18n::NAME, $termName);

        if ($term = QubitTerm::getOne($criteria)) {
            return $term->id;
        }

        return false;
    }

    public function determineIfResourceHasRightsAct($actName)
    {
        $criteria = new Criteria();
        $criteria->add(QubitInformationObject::ID, $this->resource->id);
        $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitInformationObject::ID);
        $criteria->addJoin(QubitGrantedRight::RIGHTS_ID, QubitRelation::OBJECT_ID);
        $criteria->add(QubitGrantedRight::ACT_ID, $this->getIdForRightsActTerm($actName));

        return QubitRights::getOne($criteria);
    }

    public function offsetGet($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__get'], $args);
    }

    public function offsetSet($offset, $value)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__set'], $args);
    }

    public function offsetUnset($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__unset'], $args);
    }

    protected function baseUrl()
    {
        $baseUrl = QubitSetting::getByName('siteBaseUrl');

        return (null == $baseUrl) ? 'http://'.gethostname() : $baseUrl;
    }
}
