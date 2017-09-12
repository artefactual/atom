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
      ),
      new sfCommandOption(
        'index',
        null,
        sfCommandOption::PARAMETER_NONE,
        "Index for search during import."
      ),
      new sfCommandOption(
        'update',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Attempt to update if description has already been imported. Valid option values are "match-and-update" & "delete-and-replace".'
      ),
      new sfCommandOption(
        'skip-matched',
        null,
        sfCommandOption::PARAMETER_NONE,
        'When importing records without --update, use this option to skip creating new records when an existing one matches.'
      ),
      new sfCommandOption(
        'skip-unmatched',
        null,
        sfCommandOption::PARAMETER_NONE,
        "When importing records with --update, skip creating new records if no existing records match."
      ),
      new sfCommandOption(
        'skip-derivatives',
        null,
        sfCommandOption::PARAMETER_NONE,
        "Skip creation of digital object derivatives."
      ),
      new sfCommandOption(
        'limit',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Limit --update matching to under a specified top level description or repository via slug.'
      ),
      new sfCommandOption(
        'keep-digital-objects',
        null,
        sfCommandOption::PARAMETER_NONE,
        'Skip the deletion of existing digital objects and their derivatives when using --update with "match-and-update".'
      )
    ));
  }

  /**
   * Echo and log a message
   *
   * @see sfTask::log()
   * @see sfLoggger::log()
   *
   * @param string $message N.B. sfTask::log() accepts an array or string, but
   *                        sfLogger::log() expects a string only
   * @param string $priority See sfLogger for priority levels
   */
  public function log($message, $priority = sfLogger::INFO)
  {
    // Echo message
    parent::log($message);

    // Log message
    sfContext::getInstance()->getLogger()->log($message, $priority);
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $this->validateOptions($options);

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    // Source name can be specified so, if importing from multiple
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

    // Set default publication status
    $results = $conn->query('SELECT i18n.value
      FROM setting INNER JOIN setting_i18n i18n ON setting.id = i18n.id
      WHERE setting.name=\'defaultPubStatus\'');

    if ($results)
    {
      $defaultStatusId = $results->fetchColumn();
    }
    else
    {
      $defaultStatusId = QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID;
    }

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
      $defaultParentId = QubitFlatfileImport::getIdCorrespondingToSlug($options['default-parent-slug']);

      if (!$options['quiet'])
      {
        print 'Parent ID of slug "'. $options['default-parent-slug'] .'" is '. $defaultParentId;
      }
    }
    else if ($options['default-legacy-parent-id'])
    {
      // Attempt to fetch keymap entry
      $keyMapEntry = QubitFlatfileImport::fetchKeymapEntryBySourceAndTargetName(
        $options['default-legacy-parent-id'],
        $sourceName,
        'information_object'
      );

      if ($keyMapEntry)
      {
        $defaultParentId = $keyMapEntry->target_id;
      }
      else
      {
        throw new sfException('Could not find Qubit ID corresponding to legacy ID.');
      }

      print 'Using default parent ID '. $defaultParentId .' (legacy parent ID '. $options['default-legacy-parent-id'] .")\n";
    }
    else
    {
      $defaultParentId = QubitInformationObject::ROOT_ID;
    }

    // Define import
    $import = new QubitFlatfileImport(array(
      // Pass context
      'context' => sfContext::createInstance($this->configuration),

      // What type of object are we importing?
      'className' => 'QubitInformationObject',

      // Allow silencing of progress info
      'displayProgress' => ($options['quiet']) ? false : true,

      // How many rows should import until we display an import status update?
      'rowsUntilProgressDisplay' => $options['rows-until-update'],

      // Where to log errors to
      'errorLog' => $options['error-log'],

      // The status array is a place to put data that should be accessible
      // from closure logic using the getStatus method
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
        'physicalObjectTypes'    => $termData['physicalObjectTypes'],
      ),

      // Import columns that map directory to QubitInformationObject properties
      'standardColumns' => array(
        'updatedAt',
        'createdAt',
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

      // Import columns that should be redirected to QubitInformationObject
      // properties (and optionally transformed). Example:
      // 'columnMap' => array(
      //   'Archival History' => 'archivalHistory',
      //   'Revision history' => array(
      //     'column' => 'revision',
      //     'transformationLogic' => function(&$self, $text)
      //     {
      //       return $self->appendWithLineBreakIfNeeded(
      //         $self->object->revision,
      //         $text
      //       );
      //     }
      //   )
      // ),

      'columnMap' => array(
        'radEdition' => 'edition',
        'institutionIdentifier' => 'institutionResponsibleIdentifier'
      ),

      // Import columns that can be added using the
      // QubitInformationObject::addProperty method
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

      // Import columns that can be added as QubitNote objects
      'noteMap' => array(
        'languageNote' => array(
          'typeId' => array_search('Language note', $termData['noteTypes']['en'])
        ),
        'publicationNote' => array(
          'typeId' => array_search('Publication note', $termData['noteTypes']['en'])
        ),
        'generalNote' => array(
          'typeId' => array_search('General note', $termData['noteTypes']['en'])
        ),
        'archivistNote' => array(
          'typeId' => array_search("Archivist's note", $termData['noteTypes']['en'])
        ),
        'radNoteCast' => array(
          'typeId' => array_search('Cast note', $termData['radNoteTypes']['en'])
        ),
        'radNoteCredits' => array(
          'typeId' => array_search('Credits note', $termData['radNoteTypes']['en'])
        ),
        'radNoteSignaturesInscriptions' => array(
          'typeId' => array_search('Signatures note', $termData['radNoteTypes']['en'])
        ),
        'radNoteConservation' => array(
          'typeId' => array_search('Conservation', $termData['radNoteTypes']['en'])
        ),
        'radNoteGeneral' => array(
          'typeId' => array_search('General note', $termData['noteTypes']['en'])
        ),
        'radNotePhysicalDescription' => array(
          'typeId' => array_search('Physical description', $termData['radNoteTypes']['en'])
        ),
        'radNotePublishersSeries' => array(
          'typeId' => array_search("Publisher's series", $termData['radNoteTypes']['en'])
        ),
        'radNoteRights' => array(
          'typeId' => array_search('Rights', $termData['radNoteTypes']['en'])
        ),
        'radNoteAccompanyingMaterial' => array(
          'typeId' => array_search('Accompanying material', $termData['radNoteTypes']['en'])
        ),
        'radNoteAlphaNumericDesignation' => array(
          'typeId' => array_search('Alpha-numeric designations', $termData['radNoteTypes']['en'])
        ),
        'radNoteEdition' => array(
          'typeId' => array_search('Edition', $termData['radNoteTypes']['en'])
        ),
        'radTitleStatementOfResponsibilityNote' => array(
          'typeId' => array_search('Statements of responsibility', $termData['titleNoteTypes']['en'])
        ),
        'radTitleParallelTitles' => array(
          'typeId' => array_search('Parallel titles and other title information', $termData['titleNoteTypes']['en'])
        ),
        'radTitleSourceOfTitleProper' => array(
          'typeId' => array_search('Source of title proper', $termData['titleNoteTypes']['en'])
        ),
        'radTitleVariationsInTitle' => array(
          'typeId' => array_search('Variations in title', $termData['titleNoteTypes']['en'])
        ),
        'radTitleAttributionsAndConjectures' => array(
          'typeId' => array_search('Attributions and conjectures', $termData['titleNoteTypes']['en'])
        ),
        'radTitleContinues' => array(
          'typeId' => array_search('Continuation of title', $termData['titleNoteTypes']['en'])
        ),
        'radTitleNoteContinuationOfTitle' => array(
          'typeId' => array_search('Continuation of title', $termData['titleNoteTypes']['en'])
        )
      ),

      // Import columns with values that should be serialized/added as a language property
      'languageMap' => array(
        'language'              => 'language',
        'languageOfDescription' => 'languageOfDescription'
      ),

      // Import columns with values that should be serialized/added as a script property
      'scriptMap' => array(
        'script'              => 'script',
        'scriptOfDescription' => 'scriptOfDescription'
      ),

      // These values get stored to the rowStatusVars array
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
        'physicalObjectName',
        'physicalObjectLocation',
        'physicalObjectType',
        'physicalStorageLocation',
        'digitalObjectPath',
        'digitalObjectURI',
        'digitalObjectChecksum'
      ),

      // These values get exploded and stored to the rowStatusVars array
      'arrayColumns' => array(
        'accessionNumber'              => '|',
        'alternativeIdentifiers'       => '|',
        'alternativeIdentifierLabels'  => '|',

        'nameAccessPoints'          => '|',
        'nameAccessPointHistories'  => '|',
        'placeAccessPoints'         => '|',
        'placeAccessPointHistories' => '|', // Not yet implemented
        'subjectAccessPoints'       => '|',
        'subjectAccessPointScopes'  => '|',  // Not yet implemented
        'genreAccessPoints'         => '|',

        'eventActors'          => '|',
        'eventActorHistories'  => '|',
        'eventTypes'           => '|',
        'eventPlaces'          => '|',
        'eventDates'           => '|',
        'eventStartDates'      => '|',
        'eventEndDates'        => '|',
        'eventDescriptions'    => '|',

        // These columns are for backwards compatibility
        'creators'           => '|',
        'creatorHistories'   => '|',
        'creatorDates'       => '|',
        'creatorDatesStart'  => '|',
        'creatorDatesEnd'    => '|',
        'creatorDateNotes'   => '|',
        'creationDates'      => '|',
        'creationDatesStart' => '|',
        'creationDatesEnd'   => '|',
        'creationDateNotes'  => '|'
      ),

      'updatePreparationLogic' => function(&$self)
      {
        // If keep-digital-objects is set and --update="match-and-update" is set,
        // skip this logic to delete digital objects.
        if (((isset($self->rowStatusVars['digitalObjectPath']) && $self->rowStatusVars['digitalObjectPath'])
          || (isset($self->rowStatusVars['digitalObjectURI']) && $self->rowStatusVars['digitalObjectURI']))
          && !$self->keepDigitalObjects)
        {
          // Retrieve any digital objects that exist for this information object
          $do = $self->object->getDigitalObject();

          if (null !== $do)
          {
            $deleteDigitalObject = true;

            if ($self->isUpdating())
            {
              // if   - there is a checksum in the import file
              //      - the checksum is non-blank
              //      - the checksum in the csv file matches what is in the database
              // then - do not re-load the digital object from the import file on UPDATE (leave existing recs as is)
              // else - reload the digital object in the import file (i.e. delete existing record below)
              if (isset($self->rowStatusVars['digitalObjectChecksum'])
                && $self->rowStatusVars['digitalObjectChecksum']
                && 0 === strcmp($self->rowStatusVars['digitalObjectChecksum'], $do->getChecksum()))
              {
                // if the checksum matches what is stored with digital object, do not import this digital object.
                $deleteDigitalObject = false;
              }
            }

            if ($deleteDigitalObject)
            {
              $do->delete();
            }
          }
        }
      },

      // Import logic to execute before saving information object
      'preSaveLogic' => function(&$self)
      {
        // Set repository
        if (isset($self->rowStatusVars['repository']) && $self->rowStatusVars['repository'])
        {
          $repository = $self->createOrFetchRepository($self->rowStatusVars['repository']);
          $self->object->repositoryId = $repository->id;
        }

        // Set level of detail
        if (isset($self->rowStatusVars['levelOfDetail']) && 0 < strlen($self->rowStatusVars['levelOfDetail']))
        {
          $levelOfDetail = trim($self->rowStatusVars['levelOfDetail']);

          $levelOfDetailTermId = array_search_case_insensitive($levelOfDetail, $self->status['levelOfDetailTypes'][$self->columnValue('culture')]);
          if ($levelOfDetailTermId === false)
          {
            print "\nTerm $levelOfDetail not found in description details level taxonomy, creating it...\n";

            $newTerm = QubitFlatfileImport::createTerm(
              QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID,
              $levelOfDetail,
              $self->columnValue('culture')
            );

            $levelOfDetailTermId = $newTerm->id;
            $self->status['levelOfDetailTypes'] = refreshTaxonomyTerms(QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID);
          }

          $self->object->descriptionDetailId = $levelOfDetailTermId;
        }

        // Add alternative identifiers
        if (array_key_exists('alternativeIdentifiers', $self->rowStatusVars) &&
            array_key_exists('alternativeIdentifierLabels', $self->rowStatusVars))
        {
          setAlternativeIdentifiers(
            $self->object,
            $self->rowStatusVars['alternativeIdentifiers'],
            $self->rowStatusVars['alternativeIdentifierLabels']
          );
        }

        // Set description status
        if (isset($self->rowStatusVars['descriptionStatus']) && 0 < strlen($self->rowStatusVars['descriptionStatus']))
        {
          $descStatus = trim($self->rowStatusVars['descriptionStatus']);
          $statusTermId = array_search_case_insensitive($descStatus, $self->status['descriptionStatusTypes'][$self->columnValue('culture')]);

          if (false !== $statusTermId)
          {
            $self->object->descriptionStatusId = $statusTermId;
          }
          else
          {
            print "\nTerm $descStatus not found in description status taxonomy, creating it...\n";

            $newTerm = QubitFlatfileImport::createTerm(QubitTaxonomy::DESCRIPTION_STATUS_ID, $descStatus, $self->columnValue('culture'));
            $self->status['descriptionStatusTypes'] = refreshTaxonomyTerms(QubitTaxonomy::DESCRIPTION_STATUS_ID);

            $self->object->descriptionStatusId = $newTerm->id;
          }
        }

        // Set publication status
        if (isset($self->rowStatusVars['publicationStatus']) && 0 < strlen($self->rowStatusVars['publicationStatus']))
        {
          $pubStatusTermId = array_search_case_insensitive(
            $self->rowStatusVars['publicationStatus'],
            $self->status['pubStatusTypes'][$self->columnValue('culture')]
          );

          if (!$pubStatusTermId)
          {
            print "\nPublication status: '". $self->rowStatusVars['publicationStatus'] ."' is invalid. Using default.\n";
            $pubStatusTermId = $self->status['defaultStatusId'];
          }
        }
        else
        {
          $pubStatusTermId = $self->status['defaultStatusId'];
        }

        $self->object->setPublicationStatus($pubStatusTermId);

        if (isset($self->rowStatusVars['qubitParentSlug']) && $self->rowStatusVars['qubitParentSlug'])
        {
          $parentId = $self->getIdCorrespondingToSlug($self->rowStatusVars['qubitParentSlug']);
        }
        else
        {
          if (!isset($self->rowStatusVars['parentId']) || !$self->rowStatusVars['parentId'])
          {
            // Don't overwrite valid parentId when adding an i18n row
            if (!isset($self->object->parentId))
            {
              $parentId = $self->status['defaultParentId'];
            }
          }
          else
          {
            if ($mapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
              $self->rowStatusVars['parentId'],
              $self->getStatus('sourceName'),
              'information_object'))
            {
              $parentId = $mapEntry->target_id;
            }
            else if (null !== QubitInformationObject::getById($self->rowStatusVars['parentId']))
            {
              $parentId = $self->rowStatusVars['parentId'];
            }
            else
            {
              $error = sprintf('legacyId %s: could not find parentId %s in key_map table or existing data. Setting parent to root...',
                               $self->rowStatusVars['legacyId'], $self->rowStatusVars['parentId']);

              print $self->logError($error);
              $self->object->parentId = QubitInformationObject::ROOT_ID;
            }
          }
        }

        if (isset($parentId))
        {
          $self->object->parentId = $parentId;
        }
      },

      // Import logic to execute after saving information object
      'postSaveLogic' => function(&$self)
      {
        if (!$self->object->id)
        {
          throw new sfException('Information object save failed');
        }

        // Add keymap entry
        $self->createKeymapEntry($self->getStatus('sourceName'), $self->rowStatusVars['legacyId']);

        // Inherit repository instead of duplicating the association to it if applicable
        if ($self->object->canInheritRepository($self->object->repositoryId))
        {
          // Use raw SQL since we don't want an entire save() here.
          $sql = 'UPDATE information_object SET repository_id = NULL WHERE id = ?';
          QubitPdo::prepareAndExecute($sql, array($self->object->id));

          $self->object->repositoryId = null;
        }

        // Add physical objects
        if (isset($self->rowStatusVars['physicalObjectName']) &&
            $self->rowStatusVars['physicalObjectName'])
        {
          $names = explode('|', $self->rowStatusVars['physicalObjectName']);
          $locations = explode('|', $self->rowStatusVars['physicalObjectLocation']);
          $types = (isset($self->rowStatusVars['physicalObjectType']))
            ? explode('|', $self->rowStatusVars['physicalObjectType'])
            : array();

          foreach ($names as $index => $name)
          {
            // If location column populated
            if ($self->rowStatusVars['physicalObjectLocation'])
            {
              // If current index applicable
              if (isset($locations[$index]))
              {
                $location = $locations[$index];
              }
              else
              {
                $location = $locations[0];
              }
            }
            else
            {
              $location = '';
            }

            // If object type column populated
            if ($self->rowStatusVars['physicalObjectType'])
            {
              // If current index applicable
              if (isset($types[$index]))
              {
                $type = $types[$index];
              }
              else
              {
                $type = $types[0];
              }
            }
            else
            {
              $type = 'Box';
            }

            $physicalObjectTypeId = array_search_case_insensitive($type, $self->status['physicalObjectTypes'][$self->columnValue('culture')]);

            // Create new physical object type if not found
            if ($physicalObjectTypeId === false)
            {
              print "\nTerm $type not found in physical object type taxonomy, creating it...\n";

              $newTerm = QubitFlatfileImport::createTerm(QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID, $type, $self->columnValue('culture'));
              $self->status['physicalObjectTypes'] = refreshTaxonomyTerms(QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID);

              $physicalObjectTypeId = $newTerm->id;
            }

            $container = $self->createOrFetchPhysicalObject($name, $location, $physicalObjectTypeId);

            // Associate container with information object
            $self->createRelation($container->id, $self->object->id, QubitTerm::HAS_PHYSICAL_OBJECT_ID);
          }
        }

        // Add subject access points
        $accessPointColumns = array(
          'subjectAccessPoints' => QubitTaxonomy::SUBJECT_ID,
          'placeAccessPoints'   => QubitTaxonomy::PLACE_ID,
          'genreAccessPoints'   => QubitTaxonomy::GENRE_ID
        );

        foreach ($accessPointColumns as $columnName => $taxonomyId)
        {
          if (isset($self->rowStatusVars[$columnName]))
          {
            $index = 0;
            foreach ($self->rowStatusVars[$columnName] as $subject)
            {
              if ($subject)
              {
                $scope = false;
                if (isset($self->rowStatusVars['subjectAccessPointScopes'][$index]))
                {
                  $scope = $self->rowStatusVars['subjectAccessPointScopes'][$index];
                }

                $self->createOrFetchTermAndAddRelation($taxonomyId, $subject);

                if ($scope)
                {
                  // Get term ID
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

                    // Check if a scope note already exists for this term
                    $query = "SELECT n.id FROM note n INNER JOIN note_i18n i ON n.id=i.id WHERE n.object_id=? AND n.type_id=?";

                    $statement = QubitFlatfileImport::sqlQuery(
                      $query,
                      array($termId, QubitTerm::SCOPE_NOTE_ID)
                    );

                    $result = $statement->fetch(PDO::FETCH_OBJ);

                    if (!$result)
                    {
                      // Add scope note if it doesn't exist
                      $note = new QubitNote;
                      $note->objectId = $termId;
                      $note->typeId = QubitTerm::SCOPE_NOTE_ID;
                      $note->content = $self->content($scope);
                      $note->scope = 'QubitTerm'; // Not sure if this is needed
                      $note->save();
                    }
                  }
                  else
                  {
                    throw new sfException('Could not find term "'. $subject .'"');
                  }
                }
              }
              $index++;
            }
          }
        }

        // Add name access points
        if (isset($self->rowStatusVars['nameAccessPoints']))
        {
          // Add name access points
          $index = 0;
          foreach ($self->rowStatusVars['nameAccessPoints'] as $name)
          {
            // Skip blank names
            if ($name)
            {
              $actorOptions = array();
              if (isset($self->rowStatusVars['nameAccessPointHistories'][$index]))
              {
                $actorOptions['history'] = $self->rowStatusVars['nameAccessPointHistories'][$index];
              }

              if (null !== $repo = $self->object->getRepository(array('inherit' => true)))
              {
                $actorOptions['repositoryId'] = $repo->id;
              }

              $actor = $self->createOrFetchAndUpdateActorForIo($name, $actorOptions);
              $self->createRelation($self->object->id, $actor->id, QubitTerm::NAME_ACCESS_POINT_ID);
            }

            $index++;
          }
        }

        // Add accessions
        if (isset($self->rowStatusVars['accessionNumber']) &&
            count($self->rowStatusVars['accessionNumber']))
        {
          foreach ($self->rowStatusVars['accessionNumber'] as $accessionNumber)
          {
            // Attempt to fetch keymap entry
            $accessionMapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
              $accessionNumber,
              $self->getStatus('sourceName'),
              'accession'
            );

            // If no entry found, create accession and entry
            if (!$accessionMapEntry)
            {
              $criteria = new Criteria;
              $criteria->add(QubitAccession::IDENTIFIER, $accessionNumber);

              if (null === $accession = QubitAccession::getone($criteria))
              {
                print "\nCreating accession # ". $accessionNumber ."\n";

                // Create new accession
                $accession = new QubitAccession;
                $accession->identifier = $accessionNumber;
                $accession->save();

                // Create keymap entry for accession
                $self->createKeymapEntry($self->getStatus('sourceName'), $accessionNumber, $accession);
              }

              $accessionId = $accession->id;
            }
            else
            {
              $accessionId = $accessionMapEntry->target_id;
            }

            print "\nAssociating accession # ". $accessionNumber ." with ". $self->object->title ."\n";

            // Add relationship between information object and accession
            $self->createRelation($self->object->id, $accessionId, QubitTerm::ACCESSION_ID);
          }
        }

        // Add material-related term relation
        if (isset($self->rowStatusVars['radGeneralMaterialDesignation']))
        {
          foreach ($self->rowStatusVars['radGeneralMaterialDesignation'] as $material)
          {
            $self->createObjectTermRelation($self->object->id, $material);
          }
        }

        // Add copyright info
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

              foreach ($rightsHolderNames as $index => $rightsHolderName)
              {
                $rightsHolderName = ($rightsHolderName) ? $rightsHolderName : 'Unknown';
                $rightsHolder = $self->createOrFetchRightsHolder($rightsHolderName);
                $rightsHolderId = $rightsHolder->id;

                $rightsHolderName = trim(strtolower($rightsHolderName));
                if ($rightsHolderName == 'city of vancouver' || strpos($rightsHolderName, 'city of vancouver') === 0)
                {
                  $restriction = 1;
                }
                else
                {
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
                  // If rightsholder/expiry dates and paired, use
                  // corresponding date, otherwise just use the
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

                if ($rightsHolderId)
                {
                  $rightAndRelation['rightsHolderId'] = $rightsHolderId;
                }

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

        // Add events
        csvImportBaseTask::importEvents($self);

        // This will import only a single digital object;
        // if both a URI and path are provided, the former is preferred.

        // note: Digital Objects should only be created here if they do not
        // exist already.  If getDigitalObject() is null, we know they do not
        // exist and therefore should be created.

        // When this CSV importer is run and the --update parameter is set,
        // the function 'updatePreparationLogic' (above) will be run.
        // 'updatePreparationLogic' has been enhanced to compare the DO
        // checksum in the DB against the checksum in the import CSV and will
        // only remove the DO if the checksums differ.  If they are removed
        // because the checksums are different, the following code will
        // recreate them from the CSV file.
        if (($uri = $self->rowStatusVars['digitalObjectURI'])
          && null === $self->object->getDigitalObject())
        {
          $do = new QubitDigitalObject;
          $do->informationObject = $self->object;

          if ($self->status['options']['skip-derivatives'])
          {
            // Don't download remote resource or create derivatives
            $do->createDerivatives = false;
          }
          else
          {
            // Try downloading external object up to three times (2 retries)
            $options = array('downloadRetries' => 2);
          }

          // Catch digital object import errors to avoid killing whole import
          try
          {
            $do->importFromURI($uri, $options);
            $do->save($conn);
          }
          catch (Exception $e)
          {
            // Log error
            $this->log($e->getMessage(), sfLogger::ERR);
          }
        }
        else if (($path = $self->rowStatusVars['digitalObjectPath'])
          && null === $self->object->getDigitalObject())
        {
          $do = new QubitDigitalObject;
          $do->usageId = QubitTerm::MASTER_ID;
          $do->informationObject = $self->object;

          // Don't create derivatives (reference, thumb)
          if ($self->status['options']['skip-derivatives'])
          {
            $do->createDerivatives = false;
          }

          $do->assets[] = new QubitAsset($path);

          try
          {
            $do->save($conn);
          }
          catch (Exception $e)
          {
            // Log error
            $this->log($e->getMessage(), sfLogger::ERR);
          }
        }
      }
    ));

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = ($options['index']) ? false : true;

    // Set update, limit and skip options
    $import->setUpdateOptions($options);

    // Convert content with | characters to a bulleted list
    $import->contentFilterLogic = function($text)
    {
      return (substr_count($text, '|')) ? '* '. str_replace("|", "\n* ", $text) : $text;
    };

    $import->addColumnHandler('levelOfDescription', function($self, $data)
    {
      $self->object->setLevelOfDescriptionByName($data);
    });

    // Map value to taxonomy term name and take note of taxonomy term's ID
    $import->addColumnHandler('radGeneralMaterialDesignation', function($self, $data)
    {
      if ($data)
      {
        $data = explode('|', $data);

        foreach ($data as $value)
        {
          $value = trim($value);
          $materialTypeId = array_search_case_insensitive($value, $self->status['materialTypes'][$self->columnValue('culture')]);

          if ($materialTypeId !== false)
          {
            $self->rowStatusVars['radGeneralMaterialDesignation'][] = $materialTypeId;
          }
          else
          {
            print "\nTerm $value not found in material type taxonomy, creating it...\n";

            $newTerm = QubitFlatfileImport::createTerm(QubitTaxonomy::MATERIAL_TYPE_ID, $value, $self->columnValue('culture'));
            $self->status['materialTypes'] = refreshTaxonomyTerms(QubitTaxonomy::MATERIAL_TYPE_ID);

            $self->rowStatusVars['radGeneralMaterialDesignation'][] = $newTerm->id;
          }
        }
      }
    });

    $import->csv($fh, $skipRows);

    // Build nested set if desired
    if (!$options['skip-nested-set-build'])
    {
      $buildNestedSet = new propelBuildNestedSetTask($this->dispatcher, $this->formatter);
      $buildNestedSet->setCommandApplication($this->commandApplication);
      $buildNestedSet->setConfiguration($this->configuration);
      $ret = $buildNestedSet->run();
    }
  }
}

function array_search_case_insensitive($search, $array)
{
  return array_search(strtolower($search), array_map('strtolower', $array));
}

function setAlternativeIdentifiers($io, $altIds, $altIdLabels)
{
  if (count($altIdLabels) !== count($altIds))
  {
    throw new sfException('Number of alternative ids does not match number of alt id labels');
  }

  for ($i = 0; $i < count($altIds); $i++)
  {
    $io->addProperty($altIdLabels[$i], $altIds[$i], array('scope' => 'alternativeIdentifiers'));
  }
}

/**
 * Reload a taxonomy's terms from the database. We'll need to do this
 * whenever we create new terms on the fly when importing the file,
 * so subsequent rows can use the newly created terms.
 */
function refreshTaxonomyTerms($taxonomyId)
{
  $result = QubitFlatfileImport::loadTermsFromTaxonomies(array($taxonomyId => 'terms'));

  return $result['terms'];
}
