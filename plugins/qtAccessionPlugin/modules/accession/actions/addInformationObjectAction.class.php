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

class AccessionAddInformationObjectAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    if (!QubitAcl::check(QubitInformationObject::getRoot(), 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Create new information object
    $informationObject = new QubitInformationObject;
    $informationObject->setRoot();

    // Populate fields
    $informationObject->title = $this->resource->title;
    $informationObject->physicalCharacteristics = $this->resource->physicalCharacteristics;
    $informationObject->scopeAndContent = $this->resource->scopeAndContent;
    $informationObject->archivalHistory = $this->resource->archivalHistory;
    $informationObject->appraisal = $this->resource->appraisal;

    // Copy (not link) rights
    foreach (QubitRelation::getRelationsBySubjectId($this->resource->id, array('typeId' => QubitTerm::RIGHT_ID)) as $item)
    {
      $sourceRight = $item->object;

      $right = new QubitRights;
      $right->act = $sourceRight->act;
      $right->startDate = $sourceRight->startDate;
      $right->endDate = $sourceRight->endDate;
      $right->basis = $sourceRight->basis;
      $right->restriction = $sourceRight->restriction;
      $right->copyrightStatus = $sourceRight->copyrightStatus;
      $right->copyrightStatusDate = $sourceRight->copyrightStatusDate;
      $right->copyrightJurisdiction = $sourceRight->copyrightJurisdiction;
      $right->statuteNote = $sourceRight->statuteNote;

      // Right holder
      if (isset($sourceRight->rightsHolder))
      {
        $right->rightsHolder = $sourceRight->rightsHolder;
      }

      // I18n
      $right->rightsNote = $sourceRight->rightsNote;
      $right->copyrightNote = $sourceRight->copyrightNote;
      $right->licenseIdentifier = $sourceRight->licenseIdentifier;
      $right->licenseTerms = $sourceRight->licenseTerms;
      $right->licenseNote = $sourceRight->licenseNote;
      $right->statuteJurisdiction = $sourceRight->statuteJurisdiction;
      $right->statuteCitation = $sourceRight->statuteCitation;
      $right->statuteDeterminationDate = $sourceRight->statuteDeterminationDate;

      foreach ($sourceRight->rightsI18ns as $sourceRightI18n)
      {
        if ($this->context->user->getCulture() == $sourceRightI18n->culture)
        {
          continue;
        }

        $rightI18n = new QubitRightsI18n;
        $rightI18n->rightNote = $sourceRightI18n->rightNote;
        $rightI18n->copyrightNote = $sourceRightI18n->copyrightNote;
        $rightI18n->licenseIdentifier = $sourceRightI18n->licenseIdentifier;
        $rightI18n->licenseTerms = $sourceRightI18n->licenseTerms;
        $rightI18n->licenseNote = $sourceRightI18n->licenseNote;
        $rightI18n->statuteJurisdiction = $sourceRightI18n->statuteJurisdiction;
        $rightI18n->statuteCitation = $sourceRightI18n->statuteCitation;
        $rightI18n->statuteNote = $sourceRightI18n->statuteNote;
        $rightI18n->culture = $sourceRightI18n->culture;

        $right->rightsI18ns[] = $rightI18n;
      }

      $right->save();

      $relation = new QubitRelation;
      $relation->object = $right;
      $relation->typeId = QubitTerm::RIGHT_ID;

      $informationObject->relationsRelatedBysubjectId[] = $relation;
    }

    // Populate creators (from QubitRelation to QubitEvent)
    foreach (QubitRelation::getRelationsByObjectId($this->resource->id, array('typeId' => QubitTerm::CREATION_ID)) as $item)
    {
      $event = new QubitEvent;
      $event->actor = $item->subject;
      $event->typeId = QubitTerm::CREATION_ID;

      $informationObject->events[] = $event;
    }

    // Populate dates
    foreach ($this->resource->getDates() as $accessionEvent)
    {
      $event = new QubitEvent;
      $event->date = $accessionEvent->date;
      $event->startDate = $accessionEvent->startDate;
      $event->endDate = $accessionEvent->endDate;
      $event->typeId = $accessionEvent->typeId;

      $informationObject->events[] = $event;
    }

    // Relationship between the information object and accession record
    $relation = new QubitRelation;
    $relation->object = $this->resource;
    $relation->typeId = QubitTerm::ACCESSION_ID;

    $informationObject->relationsRelatedBysubjectId[] = $relation;

    // Set publication status
    $informationObject->setPublicationStatus(sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID));

    $informationObject->save();

    $this->redirect(array($informationObject, 'module' => 'informationobject'));
  }
}
