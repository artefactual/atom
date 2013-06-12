<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Import csv data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvImportTask extends csvImportBaseTask
{
    protected $namespace        = 'csv';
    protected $name             = 'import';
    protected $briefDescription = 'Import csv information object data';

    protected $detailedDescription = <<<EOF
Import CSV data
EOF;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption(
        'source-name',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        'Source name to use when inserting keymap entries.'
      ),
      new sfCommandOption(
        'default-parent-slug',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        'Parent slug under which imported items, with no parent specified, will be added.'
      ),
      new sfCommandOption(
        'default-legacy-parent-id',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        'Legacy parent ID under which imported items, with no parent specified, will be added.'
      ),
      new sfCommandOption(
        'skip-nested-set-build',
        null,
        sfCommandOption::PARAMETER_NONE,
        "Don't build the nested set upon import completion."
      )
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->validateOptions($options);

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    // source name can be specified so, if importing from multiple
    // sources, you can accommodate legacy ID collisions in files
    // you import from different places
    $sourceName = ($options['source-name'])
      ? $options['source-name']
      : basename($arguments['filename']);

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // set default publishing status
    $defaultStatusId = sfConfig::get(
      'app_defaultPubStatus',
      QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID
    );

    // TODO: this may be unnecessary as it may now be part of Qubit trunk
    // create note term if it doesn't yet exist
    QubitFlatfileImport::createOrFetchTerm(
      QubitTaxonomy::NOTE_TYPE_ID,
      'Language note'
    );

    // Load taxonomies into variables to avoid use of magic numbers
    $termData = QubitFlatfileImport::loadTermsFromTaxonomies(array(
      QubitTaxonomy::DESCRIPTION_STATUS_ID       => 'descriptionStatusTypes',
      QubitTaxonomy::PUBLICATION_STATUS_ID       => 'pubStatusTypes',
      QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID => 'levelOfDetailTypes',
      QubitTaxonomy::NOTE_TYPE_ID                => 'noteTypes',
      QubitTaxonomy::RAD_NOTE_ID                 => 'radNoteTypes',
      QubitTaxonomy::RAD_TITLE_NOTE_ID           => 'titleNoteTypes',
      QubitTaxonomy::MATERIAL_TYPE_ID            => 'materialTypes',
      QubitTaxonomy::RIGHT_ACT_ID                => 'copyrightActTypes',
      QubitTaxonomy::COPYRIGHT_STATUS_ID         => 'copyrightStatusTypes',
      QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID     => 'physicalObjectTypes'
    ));

    // Allow default parent ID to be overridden by CLI options
    if ($options['default-parent-slug'])
    {
      $defaultParentId = getIdCorrespondingToSlug($options['default-parent-slug']);

      if (!$options['quiet'])
      {
        print 'Parent ID of slug "'. $options['default-parent-slug'] .'" is '. $defaultParentId;
      }
    } else if($options['default-legacy-parent-id']) {
      // attempt to fetch keymap entry
      $keyMapEntry = QubitFlatfileImport::fetchKeymapEntryBySourceAndTargetName(
        $options['default-legacy-parent-id'],
        $sourceName,
        'information_object'
      );

      if ($keyMapEntry)
      {
        $defaultParentId = $keyMapEntry->target_id;
      } else {
        throw new sfException('Could not find Qubit ID corresponding to legacy ID.');
      }
      print 'Using default parent ID '. $defaultParentId .' (legacy parent ID '. $options['default-legacy-parent-id'] .")\n";
    } else {
      $defaultParentId = QubitInformationObject::ROOT_ID;
    }

    // Define import
    $import = new QubitFlatfileImport(array(
      /* Pass context */
      'context' => sfContext::createInstance($this->configuration),

      /* What type of object are we importing? */
      'className' => 'QubitInformationObject',

      /* Allow silencing of progress info */
      'displayProgress' => ($options['quiet']) ? false : true,

      /* How many rows should import until we display an import status update? */
      'rowsUntilProgressDisplay' => $options['rows-until-update'],

      /* Where to log errors to */
      'errorLog' => $options['error-log'],

      /* the status array is a place to put data that should be accessible
         from closure logic using the getStatus method */
      'status' => array(
        'options'                => $options,
        'sourceName'             => $sourceName,
        'defaultParentId'        => $defaultParentId,
        'copyrightStatusTypes'   => $termData['copyrightStatusTypes'],
        'copyrightActTypes'      => $termData['copyrightActTypes'],
        'defaultStatusId'        => $defaultStatusId,
        'descriptionStatusTypes' => $termData['descriptionStatusTypes'],
        'pubStatusTypes'         => $termData['pubStatusTypes'],
        'levelOfDetailTypes'     => $termData['levelOfDetailTypes'],
        'materialTypes'          => $termData['materialTypes'],
        'physicalObjectTypes'    => $termData['physicalObjectTypes']
      ),

      /* import columns that map directory to QubitInformationObject properties */
      'standardColumns' => array(
        'accessConditions',
        'accruals',
        'acquisition',
        'alternateTitle',
        'appraisal',
        'archivalHistory',
        'arrangement',
        'culture',
        'descriptionIdentifier',
        'extentAndMedium',
        'findingAids',
        'identifier',
        'locationOfCopies',
        'locationOfOriginals',
        'physicalCharacteristics',
        'relatedUnitsOfDescription',
        'reproductionConditions',
        'revisionHistory',
        'rules',
        'scopeAndContent',
        'sources',
        'title'
      ),

      /* import columns that should be redirected to QubitInformationObject
         properties (and optionally transformed)

         Example:
         'columnMap' => array(
           'Archival History' => 'archivalHistory',
           'Revision history' => array(
             'column' => 'revision',
             'transformationLogic' => function(&$self, $text)
             {
               return $self->appendWithLineBreakIfNeeded(
                 $self->object->revision,
                 $text
               );
             }
           )
         ),
      */
      'columnMap' => array(
        'radEdition' => 'edition',
        'institutionIdentifier' => 'institutionResponsibleIdentifier'
      ),

      /* import columns that can be added using the
         QubitInformationObject::addProperty method */
      'propertyMap' => array(
        'radOtherTitleInformation'            => 'otherTitleInformation',
        'radTitleStatementOfResponsibility'   => 'titleStatementOfResponsibility',
        'radStatementOfProjection'            => 'statementOfProjection',
        'radStatementOfCoordinates'           => 'statementOfCoordinates',
        'radStatementOfScaleArchitectural'    => 'statementOfScaleArchitectural',
        'radStatementOfScaleCartographic'     => 'statementOfScaleCartographic',
        'radPublishersSeriesNote'             => 'noteOnPublishersSeries',
        'radIssuingJurisdiction'              => 'issuingJurisdictionAndDenomination',
        'radEditionStatementOfResponsibility' => 'editionStatementOfResponsibility',
        'radTitleProperOfPublishersSeries'    => 'titleProperOfPublishersSeries',
        'radParallelTitlesOfPublishersSeries' => 'parallelTitleOfPublishersSeries',
        'radOtherTitleInformationOfPublishersSeries' => 'otherTitleInformationOfPublishersSeries',
        'radStatementOfResponsibilityRelatingToPublishersSeries' => 'statementOfResponsibilityRelatingToPublishersSeries',
        'radNumberingWithinPublishersSeries'  => 'numberingWithinPublishersSeries',
        'radStandardNumber'                   => 'standardNumber'
      ),

      /* import columns that can be added as QubitNote objects */
      'noteMap' => array(
        'languageNote' => array(
          'typeId' => array_search('Language note', $termData['noteTypes'])
        ),
        'publicationNote' => array(
          'typeId' => array_search('Publication note', $termData['noteTypes'])
        ),
        'generalNote' => array(
          'typeId' => array_search('General note', $termData['noteTypes'])
        ),
        'archivistNote' => array(
          'typeId' => array_search("Archivist's note", $termData['noteTypes'])
        ),
        'radNoteConservation' => array(
          'typeId' => array_search('Conservation', $termData['radNoteTypes'])
        ),
        'radNoteGeneral' => array(
          'typeId' => array_search('General note', $termData['radNoteTypes'])
        ),
        'radNotePhysicalDescription' => array(
          'typeId' => array_search('Physical description', $termData['radNoteTypes'])
        ),
        'radNotePublishersSeries' => array(
          'typeId' => array_search("Publisher's series", $termData['radNoteTypes'])
        ),
        'radNoteRights' => array(
          'typeId' => array_search('Rights', $termData['radNoteTypes'])
        ),
        'radNoteAccompanyingMaterial' => array(
          'typeId' => array_search('Accompanying material', $termData['radNoteTypes'])
        ),
        'radNoteAlphaNumericDesignation' => array(
          'typeId' => array_search('Alpha-numeric designations', $termData['radNoteTypes'])
        ),
        'radNoteEdition' => array(
          'typeId' => array_search('Edition', $termData['radNoteTypes'])
        ),
        'radTitleStatementOfResponsibilityNote' => array(
          'typeId' => array_search('Statements of responsibility', $termData['titleNoteTypes'])
        ),
        'radTitleParallelTitles' => array(
          'typeId' => array_search('Parallel titles and other title information', $termData['titleNoteTypes'])
        ),
        'radTitleSourceOfTitleProper' => array(
          'typeId' => array_search('Source of title proper', $termData['titleNoteTypes'])
        ),
        'radTitleVariationsInTitle' => array(
          'typeId' => array_search('Variations in title', $termData['titleNoteTypes'])
        ),
        'radTitleAttributionsAndConjectures' => array(
          'typeId' => array_search('Attributions and conjectures', $termData['titleNoteTypes'])
        ),
        'radTitleContinues' => array(
          'typeId' => array_search('Continuation of title', $termData['titleNoteTypes'])
        ),
        'radTitleNoteContinuationOfTitle' => array(
          'typeId' => array_search('Continuation of title', $termData['titleNoteTypes'])
        )
      ),

      /* these values get stored to the rowStatusVars array */
      'variableColumns' => array(
        'legacyId',
        'parentId',
        'copyrightStatus',
        'copyrightExpires',
        'copyrightHolder',
        'qubitParentSlug',
        'descriptionStatus',
        'publicationStatus',
        'levelOfDetail',
        'repository',
        'language',
        'script',
        'languageOfDescription',
        'scriptOfDescription',
        'physicalObjectName',
        'physicalObjectLocation',
        'physicalObjectType',
        'physicalStorageLocation'
      ),

      /* these values get exploded and stored to the rowStatusVars array */
      'arrayColumns' => array(
        'accessionNumber'      => '|',
        'creators'             => '|',
        'creatorHistories'       => '|',
        // TODO: the creatorDates* columns should be depricated in favor of
        // a separate event import
        'creatorDates'      => '|',
        'creatorDateNotes'  => '|',
        'creatorDatesStart' => '|',
        'creatorDatesEnd'   => '|',

        'nameAccessPoints'     => '|',
        'nameAccessPointHistories' => '|',
        'placeAccessPoints'    => '|',
        'placeAccessPointHistories' => '|', // not yet implemented
        'subjectAccessPoints'  => '|',
        'subjectAccessPointScopes' => '|', // not yet implemented

        'eventActors'          => '|',
        'eventTypes'           => '|',
        'eventPlaces'          => '|',
        'eventDates'           => '|',
        'eventStartDates'      => '|',
        'eventEndDates'        => '|',
        'eventDescriptions'    => '|'
      ),

      /* import logic to execute before saving information object */
      'preSaveLogic' => function(&$self)
      {
        // set repository
        if (
          isset($self->rowStatusVars['repository'])
          && $self->rowStatusVars['repository']
        )
        {
          $repository = $self->createOrFetchRepository($self->rowStatusVars['repository']);
          $self->object->repositoryId = $repository->id;
        }

        // set level of detail
        if (isset($self->rowStatusVars['levelOfDetail']) && 0 < strlen($self->rowStatusVars['levelOfDetail']))
        {
          $levelOfDetailTermId = array_search(
            (trim($self->rowStatusVars['levelOfDetail'])) ? $self->rowStatusVars['levelOfDetail'] : 'Full',
            $self->status['levelOfDetailTypes']
          );

          $self->object->descriptionDetailId = $levelOfDetailTermId;
        }

        // storage language-related properties as serialized data
        $languageProperties = array(
          'language',
          'script',
          'languageOfDescription',
          'scriptOfDescription'
        );

        foreach($languageProperties as $serializeProperty)
        {
          if (isset($self->rowStatusVars[$serializeProperty])
            && 0 < strlen($self->rowStatusVars[$serializeProperty]))
          {
            $data = explode('|', $self->rowStatusVars[$serializeProperty]);

            $self->object->addProperty(
              $serializeProperty,
              serialize($data)
            );
          }
        }

        // set description status
        if (isset($self->rowStatusVars['descriptionStatus'])
          && 0 < strlen($self->rowStatusVars['descriptionStatus']))
        {
          $statusTermId = array_search(
            (trim($self->rowStatusVars['descriptionStatus'])) ? $self->rowStatusVars['descriptionStatus'] : 'Final',
            $self->status['descriptionStatusTypes']
          );

          $self->object->descriptionStatusId = $statusTermId;
        }

        // set publication status
        if (isset($self->rowStatusVars['publicationStatus'])
          && 0 < strlen($self->rowStatusVars['publicationStatus']))
        {
          $pubStatusTermId = array_search(
            ucwords($self->rowStatusVars['publicationStatus']),
            $self->status['pubStatusTypes']
          );
          if (!$pubStatusTermId)
          {
            print "Publication status: '". $self->rowStatusVars['publicationStatus'] ."' is invalid. Using default.\n";
            $pubStatusTermId = $self->status['defaultStatusId'];
          }
        } else {
          $pubStatusTermId = $self->status['defaultStatusId'];
        }

        $self->object->setPublicationStatus($pubStatusTermId);

        if (
          isset($self->rowStatusVars['qubitParentSlug'])
          && $self->rowStatusVars['qubitParentSlug']
          && isset($self->rowStatusVars['parentId'])
          && $self->rowStatusVars['parentId']
        )
        {
          throw new sfException('Both parentId and qubitParentSlug are set: only one should be set.');
        }

        if (
          isset($self->rowStatusVars['qubitParentSlug'])
          && $self->rowStatusVars['qubitParentSlug']
        )
        {
          $parentId = getIdCorrespondingToSlug($self->rowStatusVars['qubitParentSlug']);
        } else {
          if (!isset($self->rowStatusVars['parentId']) || !$self->rowStatusVars['parentId'])
          {
            // Don't overwrite valid parentId when adding an i18n row
            if (!isset($self->object->parentId))
            {
              $parentId = $self->status['defaultParentId'];
            }
          } else {
            if ($mapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
              $self->rowStatusVars['parentId'],
              $self->getStatus('sourceName'),
              'information_object'
            ))
            {
              $parentId = $mapEntry->target_id;
            } else {
              $error = 'For legacyId '
                . $self->rowStatusVars['legacyId']
                .' Could not find parentId '
                . $self->rowStatusVars['parentId']
                .' in key_map table';
              print $self->logError($error);
            }
          }
        }

        if (isset($parentId))
        {
          $self->object->parentId = $parentId;
        }
      },

      /* import logic to execute after saving information object */
      'postSaveLogic' => function(&$self)
      {
        if (!$self->object->id)
        {
          throw new sfException('Information object save failed');
        } else {
          // add keymap entry
          $keymap = new QubitKeymap;
          $keymap->sourceId   = $self->rowStatusVars['legacyId'];
          $keymap->sourceName = $self->getStatus('sourceName');
          $keymap->targetId   = $self->object->id;
          $keymap->targetName = 'information_object';
          $keymap->save();

          // add physical objects
          if (
            (
              isset($self->rowStatusVars['physicalObjectName'])
              && $self->rowStatusVars['physicalObjectName']
            )
            || (
              isset($self->rowStatusVars['physicalObjectLocation'])
              && $self->rowStatusVars['physicalObjectLocation']
            )
          )
          {
            if (
              $self->rowStatusVars['physicalObjectName']
              && $self->rowStatusVars['physicalObjectLocation']
            )
            {
              $names = explode('|', $self->rowStatusVars['physicalObjectName']);
              $locations = explode('|', $self->rowStatusVars['physicalObjectLocation']);
              $types = (isset($self->rowStatusVars['physicalObjectType']))
                ? explode('|', $self->rowStatusVars['physicalObjectType'])
                : array();

              foreach($names as $index => $name)
              {
                // if location column populated
                if ($self->rowStatusVars['physicalObjectLocation'])
                {
                  // if current index applicable
                  if (isset($locations[$index]))
                  {
                    $location = $locations[$index];
                  } else {
                    $location = $locations[0];
                  }
                } else {
                  $location = $name;
                }

                // if location column populated
                if ($self->rowStatusVars['physicalObjectType'])
                {
                  // if current index applicable
                  if (isset($types[$index]))
                  {
                    $type = $types[$index];
                  } else {
                    $type = $types[0];
                  }
                } else {
                  $type = 'Box';
                }

                $container = $self->createOrFetchPhysicalObject(
                  $name,
                  $location,
                  array_search(
                    $type,
                    $self->getStatus('physicalObjectTypes')
                  )
                );

                // associate container with information object
                $self->createRelation(
                  $container->id,
                  $self->object->id,
                  QubitTerm::HAS_PHYSICAL_OBJECT_ID
                );
              }
            } else {
              throw new sfException('Both physicalObjectName and physicalObjectLocation '
                     . 'required to create a physical object. (physicalObjectName: "'. $self->rowStatusVars['physicalObjectName']
                     . '", physicalObjectLocation: "'. $self->rowStatusVars['physicalObjectLocation'] .'")');
            }
          }

          // add subject access points
          $accessPointColumns = array(
            'subjectAccessPoints' => QubitTaxonomy::SUBJECT_ID,
            'placeAccessPoints'   => QubitTaxonomy::PLACE_ID,
          );

          foreach($accessPointColumns as $columnName => $taxonomyId)
          {
            if (isset($self->rowStatusVars[$columnName]))
            {
              $index = 0;
              foreach($self->rowStatusVars[$columnName] as $subject)
              {
                if ($subject)
                {
                  $scope = false;
                  if (isset($self->rowStatusVars['subjectAccessPointScopes'][$index]))
                  {
                    $scope = $self->rowStatusVars['subjectAccessPointScopes'][$index];
                  }

                  $self->createAccessPoint($taxonomyId, $subject);

                  if ($scope)
                  {
                    // get term ID
                    $query = "SELECT t.id FROM term t \r
                      INNER JOIN term_i18n i ON t.id=i.id \r
                      WHERE i.name=? AND t.taxonomy_id=? AND culture='en'";

                    $statement = QubitFlatfileImport::sqlQuery(
                      $query,
                      array($subject, $taxonomyId)
                    );

                    $result = $statement->fetch(PDO::FETCH_OBJ);

                    if ($result)
                    {
                      $termId = $result->id;

                      // check if a scope note already exists for this term
                      $query = "SELECT n.id FROM note n INNER JOIN note_i18n i ON n.id=i.id WHERE n.object_id=? AND n.type_id=?";

                      $statement = QubitFlatfileImport::sqlQuery(
                        $query,
                        array($termId, QubitTerm::SCOPE_NOTE_ID)
                      );

                      $result = $statement->fetch(PDO::FETCH_OBJ);

                      if (!$result)
                      {
                        // add scope note if it doesn't exist
                        $note = new QubitNote;
                        $note->objectId = $termId;
                        $note->typeId = QubitTerm::SCOPE_NOTE_ID;
                        $note->content = $self->content($scope);
                        $note->scope = 'QubitTerm'; # not sure if this is needed
                        $note->save();
                      }
                    } else {
                      throw new sfException('Could not find term "'. $subject .'"');
                    }
                  }
                }
                $index++;
              }
            }
          }

          // add name access points
          if (isset($self->rowStatusVars['nameAccessPoints']))
          {
            // add name access points
            $index = 0;
            foreach($self->rowStatusVars['nameAccessPoints'] as $name)
            {
              // skip blank names
              if ($name)
              {
                $actorOptions = array();
                if (isset($self->rowStatusVars['nameAccessPointHistories'][$index]))
                {
                  $actorOptions['history'] = $self->rowStatusVars['nameAccessPointHistories'][$index];
                }
                $actor = $self->createOrFetchActor($name, $actorOptions);
                $self->createRelation($self->object->id, $actor->id, QubitTerm::NAME_ACCESS_POINT_ID);
              }
              $index++;
            }
          }

          // add accessions
          if (
            isset($self->rowStatusVars['accessionNumber'])
            && count($self->rowStatusVars['accessionNumber'])
          )
          {
            foreach($self->rowStatusVars['accessionNumber'] as $accessionNumber)
            {
              // attempt to fetch keymap entry
              $accessionMapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
                $accessionNumber,
                $self->getStatus('sourceName'),
                'accession'
              );

              // if no entry found, create accession and entry
              if (!$accessionMapEntry)
              {
                print "\nCreating accession # ". $accessionNumber ."\n";

                // create new accession
                $accession = new QubitAccession;
                $accession->identifier = $accessionNumber;
                $accession->save();

                // create keymap entry for accession
                $keymap = new QubitKeymap;
                $keymap->sourceId   = $accessionNumber;
                $keymap->sourceName = $self->getStatus('sourceName');
                $keymap->targetId   = $accession->id;
                $keymap->targetName = 'accession';
                $keymap->save();

                $accessionId = $accession->id;
              } else {
                $accessionId = $accessionMapEntry->target_id;
              }

              print "\nAssociating accession # ". $accessionNumber ." with ". $self->object->title ."\n";

              // add relationship between information object and accession
              $self->createRelation($self->object->id, $accessionId, QubitTerm::ACCESSION_ID);
            }
          }

          // add material-related term relation
          if (isset($self->rowStatusVars['radGeneralMaterialDesignation']))
          {
            $self->createObjectTermRelation(
              $self->object->id,
              $self->rowStatusVars['radGeneralMaterialDesignation']
            );
          }

          // add copyright info
          // TODO: handle this via a separate import
          if (isset($self->rowStatusVars['copyrightStatus']) && $self->rowStatusVars['copyrightStatus'])
          {
            switch (strtolower($self->rowStatusVars['copyrightStatus']))
            {
              case 'under copyright':
                print "Adding rights for ". $self->object->title ."...\n";
                $rightsHolderId = false;
                $rightsHolderNames = explode('|', $self->rowStatusVars['copyrightHolder']);

                if ($self->rowStatusVars['copyrightExpires'])
                {
                  $endDates = explode('|', $self->rowStatusVars['copyrightExpires']);
                }

                foreach($rightsHolderNames as $index => $rightsHolderName)
                {
                  $rightsHolderName = ($rightsHolderName) ? $rightsHolderName : 'Unknown';
                  $rightsHolder = $self->createOrFetchRightsHolder($rightsHolderName);
                  $rightsHolderId = $rightsHolder->id;

                  $rightsHolderName = trim(strtolower($rightsHolderName));
                  if (
                    $rightsHolderName == 'city of vancouver'
                    || strpos($rightsHolderName, 'city of vancouver') === 0
                  )
                  {
                    $restriction = 1;
                  } else {
                    $restriction = 0;
                  }

                  $rightAndRelation = array(
                    'restriction'       => $restriction,
                    'basisId'           => QubitTerm::RIGHT_BASIS_COPYRIGHT_ID,
                    'actId'             => array_search(
                      'Replicate',
                      $self->getStatus('copyrightActTypes')
                    ),
                    'copyrightStatusId' => array_search(
                      'Under copyright',
                      $self->getStatus('copyrightStatusTypes')
                    )
                  );

                  if (isset($endDates))
                  {
                    // if rightsholder/expiry dates and paired, use
                    // corresponding date ...otherwise just use the
                    // first expiry date
                    $rightAndRelation['endDate']
                      = (count($endDates) == count($rightsHolderNames))
                        ? $endDates[$index]
                        : $endDates[0];

                    if (!is_numeric($rightAndRelation['endDate']))
                    {
                      throw new sfException('Copyright expiry '. $rightAndRelation['endDate']
                        .' is invalid.');
                    }
                  }

                  if ($rightsHolderId) $rightAndRelation['rightsHolderId'] = $rightsHolderId;
                  $self->createRightAndRelation($rightAndRelation);
                }
                break;

              case 'unknown':
                $rightsHolder   = $self->createOrFetchRightsHolder('Unknown');
                $rightsHolderId = $rightsHolder->id;

                $rightAndRelation = array(
                  'rightsHolderId'    => $rightsHolderId,
                  'restriction'       => 0,
                  'basisId'           => QubitTerm::RIGHT_BASIS_COPYRIGHT_ID,
                  'actId'             => array_search(
                    'Replicate',
                    $self->getStatus('copyrightActTypes')
                  ),
                  'copyrightStatusId' => array_search(
                    'Unknown',
                    $self->getStatus('copyrightStatusTypes')
                  )
                );

                if ($self->rowStatusVars['copyrightExpires'])
                {
                  $rightAndRelation['endDate'] = $self->rowStatusVars['copyrightExpires'];
                }

                $self->createRightAndRelation($rightAndRelation);
                break;

              case 'public domain':

                $rightAndRelation = array(
                  'restriction'       => 1,
                  'basisId'           => QubitTerm::RIGHT_BASIS_COPYRIGHT_ID,
                  'actId'             => array_search(
                    'Replicate',
                    $self->getStatus('copyrightActTypes')
                  ),
                  'copyrightStatusId' => array_search(
                    'Public domain',
                    $self->getStatus('copyrightStatusTypes')
                  )
                );

                if ($self->rowStatusVars['copyrightExpires'])
                {
                  $rightAndRelation['endDate'] = $self->rowStatusVars['copyrightExpires'];
                }

                $self->createRightAndRelation($rightAndRelation);
                break;

              default:
                throw new sfException('Copyright status "'
                  . $self->rowStatusVars['copyrightStatus']
                  .'" not handled: adjust script or import data');
                break;
            }
          }

          // add ad-hoc events
          if (isset($self->rowStatusVars['eventActors']))
          {
            foreach($self->rowStatusVars['eventActors'] as $index => $actor)
            {
              // initialize data that'll be used to create the event
              $eventData = array(
                'actorName' => $actor
              );

              // define whether each event-related column's values go directly
              // into an event property or put into a varibale for further
              // processing
              $eventColumns = array(
                'eventTypes' => array(
                  'variable'      => 'eventType',
                  'requiredError' => 'You have populated the eventActors column but not the eventTypes column.'
                ),
                'eventPlaces'        => array('variable' => 'place'),
                'eventDates'         => array('property' => 'date'),
                'eventStartDates'    => array('property' => 'startDate'),
                'eventEndDates'      => array('property' => 'endDate'),
                'eventDescriptions'  => array('property' => 'description')
              );

              // handle each of the event-related columns
              $eventType = false;
              $place     = false;
              foreach($eventColumns as $column => $definition)
              {
                if (isset($self->rowStatusVars[$column]))
                {
                  $value
                    = (count($self->rowStatusVars['eventActors']) == count($self->rowStatusVars[$column]))
                      ? $self->rowStatusVars[$column][$index]
                      : $self->rowStatusVars[$column][0];

                  // allow column value(s) to set event property
                  if (isset($definition['property']))
                  {
                    $eventData[($definition['property'])] = $value;
                  }

                  // allow column values(s) to set variable
                  if (isset($definition['variable']))
                  {
                    $$definition['variable'] = $value;
                  }
                } else if (isset($definition['requiredError'])) {
                  throw new sfException('You have populated the eventActors column but not the eventTypes column.');
                }
              }

              // if an event type has been specified, attempt to create the event
              if ($eventType)
              {
                // do lookup of type ID
                $typeTerm = $self->createOrFetchTerm(QubitTaxonomy::EVENT_TYPE_ID, $eventType);
                $eventTypeId = $typeTerm->id;

                // create event
                $event = $self->createOrUpdateEvent($eventTypeId, $eventData);

                // create a place term if specified
                if ($place)
                {
                  // create place
                  $placeTerm = $self->createTerm(QubitTaxonomy::PLACE_ID, $place);
                  $self->createObjectTermRelation($event->id, $placeTerm->id);
                }
              } else {
                throw new sfException('eventTypes column need to be populated.');
              }
            }
          }

          // add creators and create events
          $createEvents = array();
          if (isset($self->rowStatusVars['creators'])
            && count($self->rowStatusVars['creators']))
          {
            foreach($self->rowStatusVars['creators'] as $index => $creator)
            {
              // Init eventData array and add creator name
              $eventData = array('actorName' => $creator);

              setupEventDateData($self, $eventData, $index);

              // Add creator history if specified
              if(isset($self->rowStatusVars['creatorHistories'][$index]))
              {
                $eventData['actorHistory'] = $self->rowStatusVars['creatorHistories'][$index];
              }

              array_push($createEvents, $eventData);
            }
          }
          else if(
            isset($self->rowStatusVars['creatorDatesStart'])
            || isset($self->rowStatusVars['creatorDatesEnd'])
          ) {
            foreach($self->rowStatusVars['creatorDatesStart'] as $index => $date)
            {
              $eventData = array();

              setupEventDateData($self, $eventData, $index);

              array_push($createEvents, $eventData);
            }
          }
          else if(
            isset($self->rowStatusVars['creatorDates'])
            || isset($self->rowStatusVars['creatorDates'])
          ) {
            foreach($self->rowStatusVars['creatorDates'] as $index => $date)
            {
              $eventData = array();

              setupEventDateData($self, $eventData, $index);

              array_push($createEvents, $eventData);
            }
          }

          // create events, if any
          if (count($createEvents))
          {
            if ($self->rowStatusVars['culture'] != $self->object->sourceCulture)
            {
              // Add i18n data to existing event
              $sql = "SELECT id FROM event WHERE information_object_id = ? and type_id = ?;";
              $stmt = QubitFlatfileImport::sqlQuery($sql, array(
                $self->object->id,
                QubitTerm::CREATION_ID));

              $i = 0;
              while ($eventId = $stmt->fetchColumn())
              {
                $createEvents[$i++]['eventId'] = $eventId;
              }
            }

            foreach($createEvents as $eventData)
            {
               $event = $self->createOrUpdateEvent(
                 QubitTerm::CREATION_ID,
                 $eventData
               );
            }
          }
        }
      }
    ));

    // convert content with | characters to a bulleted list
    $import->contentFilterLogic = function($text)
    {
      return (substr_count($text, '|'))
        ? '* '. str_replace("|", "\n* ", $text)
        : $text;
    };

    $import->addColumnHandler('levelOfDescription', function(&$self, $data)
    {
      $self->object->setLevelOfDescriptionByName($data);
    });

    // map value to taxonomy term name and take note of taxonomy term's ID
    $import->addColumnHandler('radGeneralMaterialDesignation', function(&$self, $data)
    {
      if ($data)
      {
        $materialIndex = array_search(
          $data,
          $self->getStatus('materialTypes')
        );

        if (is_numeric($materialIndex))
        {
          $self->rowStatusVars['radGeneralMaterialDesignation'] = array_search(
            $data,
            $self->getStatus('materialTypes')
          );
        } else {
          throw new sfException('Invalid material type:'. $self->rowStatusVars['radGeneralMaterialDesignation']);
        }
      }
    });

    $import->csv($fh, $skipRows);

    // build nested set if desired
    if (!$options['skip-nested-set-build'])
    {
      $buildNestedSet = new propelBuildNestedSetTask($this->dispatcher, $this->formatter);
      $buildNestedSet->setCommandApplication($this->commandApplication);
      $buildNestedSet->setConfiguration($this->configuration);
      $ret = $buildNestedSet->run();
    }
  }

}

function setupEventDateData(&$self, &$eventData, $index)
{

  // add dates if specified
  if (
    isset($self->rowStatusVars['creatorDatesStart'][$index])
    || (
      isset($self->rowStatusVars['creatorDatesStart'][$index])
      && isset($self->rowStatusVars['creatorDatesEnd'][$index])
    )
  )
  {
    // Start and end date
    foreach(array(
        'creatorDatesEnd' => 'endDate',
        'creatorDatesStart' => 'startDate'
      )
      as $statusVar => $eventProperty
    )
    {
      if (isset($self->rowStatusVars[$statusVar][$index])) {
        $eventData[$eventProperty] = $self->rowStatusVars[$statusVar][$index] .'-00-00';
      }
    }

    // Other date info
    foreach(array(
        'creatorDateNotes' => 'description',
        'creatorDates'      => 'date'
      )
      as $statusVar => $eventProperty
    )
    {
      if (isset($self->rowStatusVars[$statusVar][$index]))
      {
        $eventData[$eventProperty] = $self->rowStatusVars[$statusVar][$index];
      }
    }
  }
}

function getIdCorrespondingToSlug($slug)
{
  $query = "SELECT object_id FROM slug WHERE slug=?";

  $statement = QubitFlatfileImport::sqlQuery(
    $query,
    array($slug)
  );

  $result = $statement->fetch(PDO::FETCH_OBJ);

  if ($result)
  {
    return $result->object_id;
  } else {
    throw new sfException('Could not find information object matching slug "'. $slug .'"');
  }
}
