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
 * Export flatfile information object data
 *
 * @package    symfony
 * @subpackage library
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvInformationObjectExport extends QubitFlatfileExport
{
  // Maps of note type IDs to names
  protected $commonNoteMap;
  protected $radNoteMap;
  protected $titleNoteMap;

  // Taxonomy cache properties
  protected $commonNoteTypeIds       = array();
  protected $radNoteTypeIds          = array();
  protected $titleNoteTypeIds        = array();
  protected $levelOfDescriptionTerms = array();
  protected $levelOfDetailTerms      = array();
  protected $descriptionStatusTerms  = array();
  protected $eventTypeTerms          = array();
  protected $physicalObjectTypes     = array();

  protected $options = array();

  /*
   * Information object-specific property setting based on configuration data
   *
   * @return void
   */
  protected function config(&$config)
  {
    // Store note mappings
    $this->commonNoteMap = $config['note']['common'];
    $this->radNoteMap    = $config['note']['rad'];
    $this->titleNoteMap  = $config['note']['title'];
  }

  /*
   * Store export parameters for use.
   *
   * @return void
   */
  public function setOptions($options = array())
  {
    $this->options = $options;
  }

  /*
   * Information object-specific column setting before CSV row write
   *
   * @return void
   */
  protected function modifyRowBeforeExport()
  {
    // Keep parent columns blank if description has no parent
    if ($this->resource->parentId != QubitInformationObject::ROOT_ID)
    {
      $this->setColumn('parentId', $this->resource->parentId);
    }

    // Set repository column and resposible institution identifier
    $repository = $this->resource->getRepository(array('inherit' => true));
    $this->setColumn('repository', $repository->authorizedFormOfName);

    // Set other groups of columns
    $this->setAccessPointColumns();
    $this->setNoteColumns();
    $this->setRadGeneralMaterialDesignationColumn();
    $this->setAlternativeIdentifierColumns();
    $this->setAccessionNumberColumn();
    $this->setEventColumns();

    // Set physical object columns if CLI being used or user has permission
    if (check_field_visibility('app_element_visibility_physical_storage', $this->options))
    {
      $this->setPhysicalObjectColumns();
    }

    // Set level of description
    $this->setColumn(
      'levelOfDescription',
      $this->levelOfDescriptionTerms[$this->resource->levelOfDescriptionId]
    );

    // Set level of detail
    $this->setColumn(
      'levelOfDetail',
      $this->levelOfDetailTerms[$this->resource->descriptionDetailId]
    );

    // Set description status
    $this->setColumn(
      'descriptionStatus',
      $this->descriptionStatusTerms[$this->resource->descriptionStatusId]
    );

    // Set digital object public URL
    $this->setColumn('digitalObjectURI', $this->resource->getDigitalObjectPublicUrl());

    // Grab checksum for this digital object
    $this->setColumn('digitalObjectChecksum', $this->resource->getDigitalObjectChecksum());

    // Set publication status
    $this->setColumn('publicationStatus', $this->resource->getPublicationStatus());
  }

  /*
   * Set access point-related columns
   *
   * @return void
   */
  protected function setAccessPointColumns()
  {
    // Set name access point columns
    $nameAccessPointData = $this->getNameAccessPointData();
    $this->setColumn('nameAccessPoints', $nameAccessPointData['names']);

    // Set place access point columns
    $placeAccessPointData = $this->getPlaceAccessPoints();
    $this->setColumn('placeAccessPoints', $placeAccessPointData['names']);

    // Set subject access point columns
    $subjectAccessPointData = $this->getSubjectAccessPoints();
    $this->setColumn('subjectAccessPoints', $subjectAccessPointData['names']);

    // Set genre access point columns
    $genreAccessPointData = $this->getGenreAccessPointData();
    $this->setColumn('genreAccessPoints', $genreAccessPointData['names']);
  }

  /*
   * Set note-related columns
   *
   * @return void
   */
  protected function setNoteColumns()
  {
    $this->setNoteColumnsUsingMap($this->commonNoteMap, $this->commonNoteTypeIds);
    $this->setNoteColumnsUsingMap($this->radNoteMap, $this->radNoteTypeIds);
    $this->setNoteColumnsUsingMap($this->titleNoteMap, $this->titleNoteTypeIds);
  }

  /*
   * Set RAD standard's general material designation column
   *
   * @return void
   */
  protected function setRadGeneralMaterialDesignationColumn()
  {
    $relations = $this->resource->getTermRelations(QubitTaxonomy::MATERIAL_TYPE_ID);

    if (count($relations))
    {
      $materials = array();

      foreach ($relations as $relation)
      {
        $materials[] = $relation->term->name;
      }

      $this->setColumn('radGeneralMaterialDesignation', $materials);
    }
  }

  /*
   * Set alternative identifier-related columns
   *
   * @return void
   */
  protected function setAlternativeIdentifierColumns()
  {
    $properties = $this->resource->getProperties(null, 'alternativeIdentifiers');

    $alternativeIdentifiers      = array();
    $alternativeIdentifierLabels = array();

    foreach ($properties as $property)
    {
      $alternativeIdentifierLabels[] = $property->name;
      $alternativeIdentifiers[] = $property->value;
    }

    $this->setColumn('alternativeIdentifierLabels', $alternativeIdentifierLabels);
    $this->setColumn('alternativeIdentifiers', $alternativeIdentifiers);
  }

  /*
   * Set physical object-related columns
   *
   * @return void
   */
  protected function setPhysicalObjectColumns()
  {
    $physicalObjects = $this->resource->getPhysicalObjects();

    $physicalObjectNames     = array();
    $physicalObjectLocations = array();
    $physicalObjectTypes     = array();

    foreach ($physicalObjects as $physicalObject)
    {
      $physicalObjectNames[]     = $physicalObject->name;
      $physicalObjectLocations[] = $physicalObject->location;
      $physicalObjectTypes[]     = $this->physicalObjectTypes[$physicalObject->typeId];
    }

    $this->setColumn('physicalObjectName', $physicalObjectNames);
    $this->setColumn('physicalObjectLocation', $physicalObjectLocations);
    $this->setColumn('physicalObjectType', $physicalObjectTypes);
  }

  /*
   * Set accession number column
   *
   * @return void
   */
  protected function setAccessionNumberColumn()
  {
    $relations = QubitRelation::getRelationsBySubjectId($this->resource->id, array('typeId' => QubitTerm::ACCESSION_ID));

    $accessionNumbers = array();

    foreach ($relations as $item)
    {
      $accessionNumbers[] = $item->object->identifier;
    }

    $this->setColumn('accessionNumber', $accessionNumbers);
  }

  /*
   * Set event-related columns
   *
   * @return void
   */
  protected function setEventColumns()
  {
    $types          = array();
    $dates          = array();
    $startDates     = array();
    $endDates       = array();
    $descriptions   = array();
    $actors         = array();
    $actorHistories = array();
    $places         = array();

    $events = $this->resource->getEventsRelatedByobjectId();

    foreach ($events as $event)
    {
      $types[]          = $this->eventTypeTerms[$event->typeId] ? $this->eventTypeTerms[$event->typeId] : 'NULL';
      $dates[]          = $event->date ? $event->date : 'NULL';
      $startDates[]     = $event->startDate ? $event->startDate : 'NULL';
      $endDates[]       = $event->endDate ? $event->endDate : 'NULL';
      $descriptions[]   = $event->description ? $event->description : 'NULL';
      $actors[]         = $event->actor->authorizedFormOfName ? $event->actor->authorizedFormOfName : 'NULL';
      $actorHistories[] = $event->actor->history ? $event->actor->history : 'NULL';
      $places[]         = $event->getPlace()->name ? $event->getPlace()->name : 'NULL';
    }

    $this->setColumn('eventTypes', $types);
    $this->setColumn('eventDates', $dates);
    $this->setColumn('eventStartDates', $startDates);
    $this->setColumn('eventEndDates', $endDates);
    $this->setColumn('eventDescriptions', $descriptions);
    $this->setColumn('eventActors', $actors);
    $this->setColumn('eventActorHistories', $actorHistories);
    $this->setColumn('eventPlaces', $places);
  }

  /*
   * Set note-related columns
   *
   * @return void
   */
  protected function setNoteColumnsUsingMap($map, $typeIds)
  {
    foreach ($map as $column => $typeName)
    {
      $typeId = array_search($typeName, $typeIds);

      // If note type doesn't exist, skip
      if ($typeId === false)
      {
        continue;
      }

      $noteContent = array();
      foreach ($this->resource->getNotesByType(array('noteTypeId' => $typeId)) as $note)
      {
        $noteContent[] = $note->content;
      }

      if (count($noteContent))
      {
        $this->setColumn($column, $noteContent);
      }
    }
  }

  /*
   * Get name access point data
   *
   * @return void
   */
  protected function getNameAccessPointData()
  {
    $accessPoints = $this->resource->getNameAccessPoints();

    $data          = array();
    $data['names'] = array();

    foreach ($accessPoints as $accessPoint)
    {
      if ($accessPoint->object->authorizedFormOfName)
      {
        $data['names'][] = $accessPoint->object->authorizedFormOfName;
      }
    }

    return $data;
  }

  /*
   * Get place access point data
   *
   * @return void
   */
  protected function getPlaceAccessPoints()
  {

    $accessPoints = $this->resource->getPlaceAccessPoints();

    $data          = array();
    $data['names'] = array();

    foreach ($accessPoints as $accessPoint)
    {
      if ($accessPoint->term->name)
      {
        $data['names'][] = $accessPoint->term->name;
      }
    }

    return $data;
  }

  /*
   * Get subject access point data
   *
   * @return void
   */
  protected function getSubjectAccessPoints()
  {

    $accessPoints = $this->resource->getSubjectAccessPoints();

    $data = array();
    $data['names'] = array();

    foreach ($accessPoints as $accessPoint)
    {
      if ($accessPoint->term->name)
      {
        $data['names'][] = $accessPoint->term->name;
      }
    }

    return $data;
  }

  /*
   * Get genre access point data
   *
   * @return void
   */
  protected function getGenreAccessPointData()
  {
    $accessPoints = $this->resource->getGenreAccessPoints();

    $data          = array();
    $data['names'] = array();

    foreach ($accessPoints as $accessPoint)
    {
      if ($accessPoint->term->name)
      {
        $data['names'][] = $accessPoint->term->name;
      }
    }

    return $data;
  }
}
