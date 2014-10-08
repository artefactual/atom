<?php

class sfModsConvertor extends QubitSaxParser {
 
  protected $resource;

  // Name access point-related bookkeeping
  protected $names = array();
  protected $name;
  protected $entityType;
  protected $role;

  // Origin info bookkeeping
  protected $places = array();
  protected $originTypeId;
  protected $originDate;
  protected $originDateStart;
  protected $originDateEnd;

  // Misc. import bookkeeping
  protected $subjects         = array();
  protected $geographic       = array();
  protected $typesOfResources = array();
  protected $languages        = array();
  protected $notes            = array();
  protected $materialTypes    = array();
  protected $digitalObjects   = array();

  // <mods>
  protected function modsTagInit()
  {
    $this->resource = new QubitInformationObject();
    $this->resource->parentId = QubitInformationObject::ROOT_ID;
  }

  // </mods>
  protected function modsTag()
  {
    // Languages
    if (count($this->languages))
    {
      $this->resource->addProperty('language', serialize($this->languages));
    }

    $this->resource->setPublicationStatusByName('Published');
    $this->resource->save();

    // Resource types
    $this->importArrayOfTermNames(QubitTaxonomy::MODS_RESOURCE_TYPE_ID, $this->typesOfResources);

    // Material types
    $this->importArrayOfTermNames(QubitTaxonomy::MATERIAL_TYPE_ID, $this->materialTypes);

    // Subject access points
    $this->importArrayOfTermNames(QubitTaxonomy::SUBJECT_ID, $this->subjects);

    // Place access points
    $this->importArrayOfTermNames(QubitTaxonomy::PLACE_ID, $this->geographic);

    // Name access points
    $this->importNameAccessPoints($this->names);

    // Creation-related events
    $this->importOriginInfo();

    // Create notes
    $this->importNotes($this->notes);

    // Import digital objects
    $this->importDigitalObjects($this->digitalObjects);
  }

  // </title>
  protected function titleTag()
  {
    if ($this->pathIncludes('mods/titleInfo'))
    {
      $this->resource->title = $this->data();
    }
  }

  // </identifier type="local | uri | pid">
  protected function identifierTag()
  {
    switch($this->attr('type'))
    {
      case 'local':
        $this->resource->identifier = $this->data();
        break;

      case 'uri':
        $this->resource->addProperty(
          'Source URI',
          $this->data(),
          array('scope' => 'alternativeIdentifiers')
        );
        break;

      case 'pid':
        $this->resource->addProperty(
          'Source ID',
          $this->data(),
          array('scope' => 'alternativeIdentifiers')
        );
        break;
    }
  }

  // </extent>
  protected function extentTag()
  {
    $this->resource->extentAndMedium = $this->data();
  }

  // </note type="originalLocation | otherFormats | numbering | language | gmd">
  protected function noteTag()
  {
    switch ($this->attr('type'))
    {
      case 'originalLocation':
        $this->resource->locationOfOriginals = $this->data();
        break;

      case 'otherFormats':
        $this->resource->locationOfCopies = $this->data();
        break;

      case 'numbering':
        $this->addRadNote('Alpha-numeric designations', $this->data());
        break;

      case 'language':
        $this->addNote('Language note', $this->data());
        break;

      case 'gmd':
        array_push($this->materialTypes, $this->data());
        break;

      default:
        $this->addNote('General note', $this->data());
    }
  }

  // </abstract>
  protected function abstractTag()
  {
    $this->resource->scopeAndContent = $this->data();
  }

  // </language>
  protected function languageTag()
  {
    array_push($this->languages, $this->data());
  }

  // </accessCondition>
  protected function accessConditionTag()
  {
    $this->resource->accessConditions = $this->data();
  } 

  // </namePart>
  protected function namePartTag()
  {
    $this->name = $this->data();
  }

  // </roleTerm>
  protected function roleTermTag()
  {
    $this->role = $this->data();
  }

  // <name>
  protected function nameTagInit()
  {
    $this->entityType = $this->attr('type');
  }

  // </name>
  protected function nameTag()
  {
    $eventData = array(
      'name' => $this->name,
      'role' => $this->role,
      'type' => $this->entityType
    );

    array_push($this->names, $eventData);
  }

  // </topic>
  protected function topicTag()
  {
    array_push($this->subjects, $this->data());
  }

  // </geographic>
  protected function geographicTag()
  {
    array_push($this->geographic, $this->data());
  }

  // </typeOfResource>
  protected function typeOfResourceTag()
  {
    array_push($this->typesOfResources, $this->data());
  }

  // </placeTerm>
  protected function placeTermTag()
  {
    array_push($this->places, $this->data());
  }

  // </dateCreated>
  protected function dateCreatedTag()
  {
    $this->setOriginDateProperties();
    $this->originTypeId = QubitTerm::CREATION_ID;
  }

  // </dateIssued>
  protected function dateIssuedTag()
  {
    $this->setOriginDateProperties();
    $this->originTypeId = QubitTerm::PUBLICATION_ID;
  }

  // </dateOther>
  protected function dateOtherTag()
  {
    $this->setOriginDateProperties();
  }

  // </recordCreation>
  protected function recordCreationDateTag()
  {
    $this->resource->createdAt = $this->data();
  }

  // </recordChangeDate>
  protected function recordChangeDateTag()
  {
    $this->resource->updatedAt = $this->data();
  }

  // <relatedItem type="host">
  protected function relatedItemTag()
  {
    if ($this->attr('type') == 'host')
    {
      // Lookup parent using identifier
      $criteria = new Criteria;
      $criteria->add(QubitInformationObject::IDENTIFIER, $this->attr('ID'));
      $resource = QubitInformationObject::getOne($criteria);

      if ($resource !== null)
      {
        $this->resource->parentId = $resource->id;
      }
    }
  }

  // <url usage="primary display">
  protected function urlTag()
  {
    if ($this->attr('usage') == 'primary display')
    {
      // Download/copy URL to temp file
      $curlSession = curl_init();
      curl_setopt($curlSession, CURLOPT_URL, $this->data());
      curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
      curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

      $tempFile = tempnam(sys_get_temp_dir(), 'atomFile');
      file_put_contents($tempFile, curl_exec($curlSession));

      curl_close($curlSession);

      // Add temp file to digital object import queue
      $pathParts = pathinfo($this->data());

      if (0 === filesize($tempFile))
      {
        sfContext::getInstance()->getLogger()->info('Digital object file is empty.');
      }
      else
      {
        $this->digitalObjects[] = array(
          'filename' => $pathParts['basename'],
          'tempFile' => $tempFile
        );
      }
    }
  }

  /*
   * Import helpers
   */

  protected function importNotes($notes)
  {
    foreach($notes as $noteSpec)
    {
      $note = new QubitNote;
      $note->objectId = $this->resource->id;
      $note->typeId = $noteSpec['typeId'];
      $note->content = $noteSpec['content'];
      $note->culture = 'en';
      $note->save();
    }
  }

  protected function addNote($typeName, $content, $taxonomyId = QubitTaxonomy::NOTE_TYPE_ID)
  {
    $noteTerm = QubitFlatfileImport::createOrFetchTerm($taxonomyId, $typeName);
    array_push($this->notes, array('typeId' => $noteTerm->id, 'content' => $content));
  }

  protected function addRadNote($typeName, $content)
  {
    $this->addNote($typeName, $content, QubitTaxonomy::RAD_NOTE_ID);
  }

  protected function setOriginDateProperties()
  {
    switch ($this->attr('point'))
    {
      case 'start':
        $this->originDateStart = $this->data();
        break;

      case 'end':
        $this->originDateEnd = $this->data();
        break;

      default:
        $this->originDate = $this->data();
    }
  }

  protected function translateModsTypeOfResourceToRadGmd($typeOfResource)
  {
    $map = array(
      'text'                          => 'Textual record',
      'cartographic'                  => 'Cartographic material',
      'notated music'                 => 'Textual record',
      'sound recording – musical'     => 'Sound recording',
      'sound recording – non-musical' => 'Sound recording',
      'sound recording'               => 'Sound recording',
      'still image'                   => 'Graphic material',
      'moving image'                  => 'Moving images',
      'three dimensional object'      => 'Object',
      'software, multimedia'          => 'Multiple media',
      'mixed material'                => 'Multiple media'
    );

    $normalizedTypeOfResource = trim(strtolower($typeOfResource));
    return (isset($map[$normalizedTypeOfResource])) ? $map[$normalizedTypeOfResource] : $typeOfResource;
  }

  protected function importArrayOfTermNames($taxonomyId, $termNames, $objectId = null)
  {
    $objectId = (is_null($objectId)) ? $this->resource->id : $objectId;

    // Subject access points
    foreach($termNames as $termName)
    {
      // Translate MODS resource types into RAD GMD terms
      if ($taxonomyId == QubitTaxonomy::MATERIAL_TYPE_ID)
      {
        $termName = $this->translateModsTypeOfResourceToRadGmd($termName);
      }
      
      $term = QubitFlatfileImport::createOrFetchTerm($taxonomyId, $termName);
      QubitFlatfileImport::createObjectTermRelation($objectId, $term->id);
    }
  }

  protected function importNameAccessPoints($nameData)
  {
    foreach($nameData as $name)
    {
      $event = new QubitEvent();
      $event->informationObjectId = $this->resource->id;
      $eventTypeTerm = QubitFlatfileImport::createOrFetchTerm(QubitTaxonomy::EVENT_TYPE_ID, $name['role']);
      $event->typeId = $eventTypeTerm->id;

      if (!empty($name['name']))
      {
        // Create actor
        $actor = QubitFlatfileImport::createOrFetchActor($name['name']);
        $event->actorId = $actor->id;

        // Create/fetch entity type term for actor, if any
        if (!empty($name['type']))
        {
          $entityTypeTerm = QubitFlatfileImport::createOrFetchTerm(QubitTaxonomy::ACTOR_ENTITY_TYPE_ID, $name['type']);
          $actor->entityTypeId = $entityTypeTerm->id;
          $actor->save();
        }

        $actor->save();
      }

      $event->save();
    }
  }

  protected function importOriginInfo()
  {
    $event = new QubitEvent();
    $event->informationObjectId = $this->resource->id;
    $event->typeId = (!empty($this->originTypeId)) ? $this->originTypeId : QubitTerm::CREATION_ID;

    // Set date, if set
    if (!empty($this->originDate))
    {
      $event->date = $this->originDate;
    }

    // Set start date, if set (not yet supported by export)
    if (!empty($this->originDateStart))
    {
      $event->startDate = $this->originDateStart;
    }

    // Set end date, if set (not yet supported by export)
    if (!empty($this->originDateEnd))
    {
      $event->endDate = $this->originDateEnd;
    }

    $event->save();

    // Add/associate place terms
    $this->importArrayOfTermNames(QubitTaxonomy::PLACE_ID, $this->places, $event->id);
  }

  function importDigitalObjects($digitalObjects)
  {
    // Create digital objects
    foreach($digitalObjects as $digitalObject)
    {
      // Import digital object
      $do = new QubitDigitalObject;
      $do->informationObjectId = $this->resource->id;
      $do->usageId = QubitTerm::MASTER_ID;
      $do->assets[] = new QubitAsset(
        $digitalObject['filename'],
        file_get_contents($digitalObject['tempFile'])
      );
      $do->save();

      // Delete tempfile
      unlink($digitalObject['tempFile']);
    }
  }
}
