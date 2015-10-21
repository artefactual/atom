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
      $sourceRights = $item->object;

      $newRights = $sourceRights->copy();

      $relation = new QubitRelation;
      $relation->object = $newRights;
      $relation->typeId = QubitTerm::RIGHT_ID;

      $informationObject->relationsRelatedBysubjectId[] = $relation;
    }

    // Populate creators (from QubitRelation to QubitEvent)
    foreach (QubitRelation::getRelationsByObjectId($this->resource->id, array('typeId' => QubitTerm::CREATION_ID)) as $item)
    {
      $event = new QubitEvent;
      $event->actor = $item->subject;
      $event->typeId = QubitTerm::CREATION_ID;

      $informationObject->eventsRelatedByobjectId[] = $event;
    }

    // Populate dates
    foreach ($this->resource->getDates() as $accessionEvent)
    {
      $event = new QubitEvent;
      $event->date = $accessionEvent->date;
      $event->startDate = $accessionEvent->startDate;
      $event->endDate = $accessionEvent->endDate;
      $event->typeId = $accessionEvent->typeId;

      $informationObject->eventsRelatedByobjectId[] = $event;
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
