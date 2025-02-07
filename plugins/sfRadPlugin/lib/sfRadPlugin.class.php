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
 * presentation features that are specific to the Canadian Rules for Archival Description (RAD) standard.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfRadPlugin
{
    protected $resource;
    protected $property;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function __toString()
    {
        $string = '';

        if (0 < strlen($title = $this->resource->__toString())) {
            $string .= $title;
        }

        $publicationStatus = $this->resource->getPublicationStatus();
        if (isset($publicationStatus) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatus->statusId) {
            $string .= (!empty($string)) ? ' ' : '';
            $string .= "({$publicationStatus->status->__toString()})";
        }

        return $string;
    }

    public function getProperty($name, $options = [])
    {
        switch ($name) {
            case 'editionStatementOfResponsibility':
            case 'issuingJurisdictionAndDenomination':
            case 'noteOnPublishersSeries':
            case 'numberingWithinPublishersSeries':
            case 'otherTitleInformation':
            case 'otherTitleInformationOfPublishersSeries':
            case 'parallelTitleOfPublishersSeries':
            case 'standardNumber':
            case 'statementOfCoordinates':
            case 'statementOfProjection':
            case 'statementOfResponsibilityRelatingToPublishersSeries':
            case 'statementOfScaleArchitectural':
            case 'statementOfScaleCartographic':
            case 'titleStatementOfResponsibility':
            case 'titleProperOfPublishersSeries':
                return $this->property($name)->__get('value', $options);

            case 'referenceCode':
                return $this->resource->referenceCode;

            case 'sourceCulture':
                return $this->resource->sourceCulture;

            case 'languageNotes':
                return $this->resource->getNotesByType(['noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID])->offsetGet(0);
        }
    }

    public function setProperty($name, $value)
    {
        switch ($name) {
            case 'editionStatementOfResponsibility':
            case 'issuingJurisdictionAndDenomination':
            case 'noteOnPublishersSeries':
            case 'numberingWithinPublishersSeries':
            case 'otherTitleInformation':
            case 'otherTitleInformationOfPublishersSeries':
            case 'parallelTitleOfPublishersSeries':
            case 'standardNumber':
            case 'statementOfCoordinates':
            case 'statementOfProjection':
            case 'statementOfResponsibilityRelatingToPublishersSeries':
            case 'statementOfScaleArchitectural':
            case 'statementOfScaleCartographic':
            case 'titleProperOfPublishersSeries':
            case 'titleStatementOfResponsibility':
                $this->property($name)->value = $value;

                return $this;

            case 'languageNotes':
                $note = $this->resource->getMemoryNotesByType(['noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID])->offsetGet(0);
                $missingNote = 0 === strlen($note);

                if (0 == strlen($value)) {
                    // Delete note if it's available
                    if (!$missingNote) {
                        $note->delete();
                    }

                    break;
                }

                if ($missingNote) {
                    $note = new QubitNote();
                    $note->typeId = QubitTerm::LANGUAGE_NOTE_ID;
                    $note->userId = sfContext::getInstance()->user->getAttribute('user_id');

                    $this->resource->notes[] = $note;
                }

                $note->content = $value;

                return $this;
        }
    }

    protected function property($name)
    {
        if (!isset($this->property[$name])) {
            $criteria = new Criteria();
            $this->resource->addPropertysCriteria($criteria);
            $criteria->add(QubitProperty::NAME, $name);

            if (1 == count($query = QubitProperty::get($criteria))) {
                $this->property[$name] = $query[0];
            } else {
                $this->property[$name] = new QubitProperty();
                $this->property[$name]->name = $name;

                $this->resource->propertys[] = $this->property[$name];
            }
        }

        return $this->property[$name];
    }
}
