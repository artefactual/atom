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
 * Import csv data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvImportTask extends csvImportBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'import';
    protected $briefDescription = 'Import csv information object data';

    protected $detailedDescription = <<<'EOF'
Import CSV data
EOF;

    /**
     * Echo and log a message.
     *
     * @see sfTask::log()
     * @see sfLoggger::log()
     *
     * @param string $message  N.B. sfTask::log() accepts an array or string, but
     *                         sfLogger::log() expects a string only
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
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $this->validateOptions($options);

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        if (!empty($options['user-id']) && (null !== $user = QubitUser::getById($options['user-id']))) {
            $this->context->getUser()->signIn($user);
        }

        $skipRows = $options['skip-rows'] ?: 0;
        $sourceName = $options['source-name'] ?: basename($arguments['filename']);
        $defaultStatusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);

        // Load taxonomies into variables to avoid use of magic numbers
        $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
            QubitTaxonomy::DESCRIPTION_STATUS_ID => 'descriptionStatusTypes',
            QubitTaxonomy::PUBLICATION_STATUS_ID => 'pubStatusTypes',
            QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID => 'levelOfDetailTypes',
            QubitTaxonomy::NOTE_TYPE_ID => 'noteTypes',
            QubitTaxonomy::RAD_NOTE_ID => 'radNoteTypes',
            QubitTaxonomy::RAD_TITLE_NOTE_ID => 'titleNoteTypes',
            QubitTaxonomy::MATERIAL_TYPE_ID => 'materialTypes',
            QubitTaxonomy::RIGHT_ACT_ID => 'copyrightActTypes',
            QubitTaxonomy::COPYRIGHT_STATUS_ID => 'copyrightStatusTypes',
            QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID => 'physicalObjectTypes',
        ]);

        if (
            $options['roundtrip']
            && !$options['no-confirmation']
            && !$this->askConfirmation(
                [
                    'WARNING: In round trip mode legacy IDs will be treated as internal IDs.',
                    'Please back-up your database manually before you proceed.',
                    '',
                    'Have you done a manual backup and wish to proceed? (y/N)',
                ],
                'QUESTION_LARGE',
                false
            )
        ) {
            $this->log('Task aborted.');

            return 1;
        }

        // Define import
        $import = new QubitFlatfileImport([
            // Pass context
            'context' => $this->context,

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
            'status' => [
                'options' => $options,
                'sourceName' => $sourceName,
                'defaultParentId' => $this->getDefaultParentId($sourceName, $options),
                'copyrightStatusTypes' => $termData['copyrightStatusTypes'],
                'copyrightActTypes' => $termData['copyrightActTypes'],
                'defaultStatusId' => $defaultStatusId,
                'descriptionStatusTypes' => $termData['descriptionStatusTypes'],
                'pubStatusTypes' => $termData['pubStatusTypes'],
                'levelOfDetailTypes' => $termData['levelOfDetailTypes'],
                'materialTypes' => $termData['materialTypes'],
                'physicalObjectTypes' => $termData['physicalObjectTypes'],
            ],

            // Import columns that map directory to QubitInformationObject properties
            'standardColumns' => [
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
                'title',
            ],

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

            'columnMap' => [
                'radEdition' => 'edition',
                'institutionIdentifier' => 'institutionResponsibleIdentifier',
            ],

            // Import columns that can be added using the
            // QubitInformationObject::addProperty method
            'propertyMap' => [
                'radOtherTitleInformation' => 'otherTitleInformation',
                'radTitleStatementOfResponsibility' => 'titleStatementOfResponsibility',
                'radStatementOfProjection' => 'statementOfProjection',
                'radStatementOfCoordinates' => 'statementOfCoordinates',
                'radStatementOfScaleArchitectural' => 'statementOfScaleArchitectural',
                'radStatementOfScaleCartographic' => 'statementOfScaleCartographic',
                'radPublishersSeriesNote' => 'noteOnPublishersSeries',
                'radIssuingJurisdiction' => 'issuingJurisdictionAndDenomination',
                'radEditionStatementOfResponsibility' => 'editionStatementOfResponsibility',
                'radTitleProperOfPublishersSeries' => 'titleProperOfPublishersSeries',
                'radParallelTitlesOfPublishersSeries' => 'parallelTitleOfPublishersSeries',
                'radOtherTitleInformationOfPublishersSeries' => 'otherTitleInformationOfPublishersSeries',
                'radStatementOfResponsibilityRelatingToPublishersSeries' => 'statementOfResponsibilityRelatingToPublishersSeries',
                'radNumberingWithinPublishersSeries' => 'numberingWithinPublishersSeries',
                'radStandardNumber' => 'standardNumber',
            ],

            // Import columns that can be added as QubitNote objects
            'noteMap' => [
                'languageNote' => [
                    'typeId' => array_search('Language note', $termData['noteTypes']['en']),
                ],
                'publicationNote' => [
                    'typeId' => array_search('Publication note', $termData['noteTypes']['en']),
                ],
                'generalNote' => [
                    'typeId' => array_search('General note', $termData['noteTypes']['en']),
                ],
                'archivistNote' => [
                    'typeId' => array_search("Archivist's note", $termData['noteTypes']['en']),
                ],
                'radNoteCast' => [
                    'typeId' => array_search('Cast note', $termData['radNoteTypes']['en']),
                ],
                'radNoteCredits' => [
                    'typeId' => array_search('Credits note', $termData['radNoteTypes']['en']),
                ],
                'radNoteSignaturesInscriptions' => [
                    'typeId' => array_search('Signatures note', $termData['radNoteTypes']['en']),
                ],
                'radNoteConservation' => [
                    'typeId' => array_search('Conservation', $termData['radNoteTypes']['en']),
                ],
                'radNoteGeneral' => [
                    'typeId' => array_search('General note', $termData['noteTypes']['en']),
                ],
                'radNotePhysicalDescription' => [
                    'typeId' => array_search('Physical description', $termData['radNoteTypes']['en']),
                ],
                'radNotePublishersSeries' => [
                    'typeId' => array_search("Publisher's series", $termData['radNoteTypes']['en']),
                ],
                'radNoteRights' => [
                    'typeId' => array_search('Rights', $termData['radNoteTypes']['en']),
                ],
                'radNoteAccompanyingMaterial' => [
                    'typeId' => array_search('Accompanying material', $termData['radNoteTypes']['en']),
                ],
                'radNoteAlphaNumericDesignation' => [
                    'typeId' => array_search('Alpha-numeric designations', $termData['radNoteTypes']['en']),
                ],
                'radNoteEdition' => [
                    'typeId' => array_search('Edition', $termData['radNoteTypes']['en']),
                ],
                'radTitleStatementOfResponsibilityNote' => [
                    'typeId' => array_search('Statements of responsibility', $termData['titleNoteTypes']['en']),
                ],
                'radTitleParallelTitles' => [
                    'typeId' => array_search('Parallel titles and other title information', $termData['titleNoteTypes']['en']),
                ],
                'radTitleSourceOfTitleProper' => [
                    'typeId' => array_search('Source of title proper', $termData['titleNoteTypes']['en']),
                ],
                'radTitleVariationsInTitle' => [
                    'typeId' => array_search('Variations in title', $termData['titleNoteTypes']['en']),
                ],
                'radTitleAttributionsAndConjectures' => [
                    'typeId' => array_search('Attributions and conjectures', $termData['titleNoteTypes']['en']),
                ],
                'radTitleContinues' => [
                    'typeId' => array_search('Continuation of title', $termData['titleNoteTypes']['en']),
                ],
                'radTitleNoteContinuationOfTitle' => [
                    'typeId' => array_search('Continuation of title', $termData['titleNoteTypes']['en']),
                ],
            ],

            // Import columns with values that should be serialized/added as a language property
            'languageMap' => [
                'language' => 'language',
                'languageOfDescription' => 'languageOfDescription',
            ],

            // Import columns with values that should be serialized/added as a script property
            'scriptMap' => [
                'script' => 'script',
                'scriptOfDescription' => 'scriptOfDescription',
            ],

            // These values get stored to the rowStatusVars array
            'variableColumns' => [
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
                'digitalObjectChecksum',
            ],

            // These values get exploded and stored to the rowStatusVars array
            'arrayColumns' => [
                'accessionNumber' => '|',
                'alternativeIdentifiers' => '|',
                'alternativeIdentifierLabels' => '|',

                'nameAccessPoints' => '|',
                'nameAccessPointHistories' => '|',
                'placeAccessPoints' => '|',
                'placeAccessPointHistories' => '|', // Not yet implemented
                'subjectAccessPoints' => '|',
                'subjectAccessPointScopes' => '|',  // Not yet implemented
                'genreAccessPoints' => '|',

                'eventActors' => '|',
                'eventActorHistories' => '|',
                'eventTypes' => '|',
                'eventPlaces' => '|',
                'eventDates' => '|',
                'eventStartDates' => '|',
                'eventEndDates' => '|',
                'eventDescriptions' => '|',

                // These columns are for backwards compatibility
                'creators' => '|',
                'creatorHistories' => '|',
                'creatorDates' => '|',
                'creatorDatesStart' => '|',
                'creatorDatesEnd' => '|',
                'creatorDateNotes' => '|',
                'creationDates' => '|',
                'creationDatesStart' => '|',
                'creationDatesEnd' => '|',
                'creationDateNotes' => '|',
            ],

            'updatePreparationLogic' => function (&$self) {
                $this->deleteDigitalObjectIfUpdatingAndNotKeeping($self);
            },

            // Import logic to execute before saving information object
            'preSaveLogic' => function (&$self) {
                $notImportingTranslation = $self->object instanceof QubitInformationObject;

                // If importing a translation, warn of values in inappropriate columns and don't import related data
                if (!$notImportingTranslation) {
                    // Determine which possible columns are allowable
                    $translationObjectProperties = [];
                    $dbMap = Propel::getDatabaseMap(QubitInformationObjectI18n::DATABASE_NAME);
                    $translationTable = $dbMap->getTable(QubitInformationObjectI18n::TABLE_NAME);
                    $columns = $translationTable->getColumns();

                    foreach ($columns as $column) {
                        array_push($translationObjectProperties, $column->getPhpName());
                    }

                    // Determine which columns being used should be ignored
                    $allowedColumns = ['legacyId'] + $translationObjectProperties;
                    $ignoredColumns = [];

                    foreach ($self->rowStatusVars as $columnName => $value) {
                        if (!empty($value) && false === array_search($columnName, $allowedColumns)) {
                            array_push($ignoredColumns, $columnName);
                        }
                    }

                    // Show warning about ignored columns
                    if (count($ignoredColumns)) {
                        $errorMessage = 'Ignoring values in column(s) incompatible with translation rows: ';
                        $errorMessage .= implode(' ', $ignoredColumns);
                        echo $self->logError($errorMessage);
                    }

                    return;
                }

                // Set repository if not importing an QubitInformationObjectI18n translation row
                if ($notImportingTranslation && isset($self->rowStatusVars['repository']) && $self->rowStatusVars['repository']) {
                    $repository = $self->createOrFetchRepository($self->rowStatusVars['repository']);
                    $self->object->repositoryId = $repository->id;
                }

                // Set level of detail
                if (isset($self->rowStatusVars['levelOfDetail']) && 0 < strlen($self->rowStatusVars['levelOfDetail'])) {
                    $levelOfDetail = trim($self->rowStatusVars['levelOfDetail']);

                    $levelOfDetailTermId = self::arraySearchCaseInsensitive($levelOfDetail, $self->status['levelOfDetailTypes'][$self->columnValue('culture')]);
                    if (false === $levelOfDetailTermId) {
                        echo "\nTerm {$levelOfDetail} not found in description details level taxonomy, creating it...\n";

                        $newTerm = QubitFlatfileImport::createTerm(
                            QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID,
                            $levelOfDetail,
                            $self->columnValue('culture')
                        );

                        $levelOfDetailTermId = $newTerm->id;
                        $self->status['levelOfDetailTypes'] = self::refreshTaxonomyTerms(QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID);
                    }

                    $self->object->descriptionDetailId = $levelOfDetailTermId;
                }

                // Add alternative identifiers
                if (
                    array_key_exists('alternativeIdentifiers', $self->rowStatusVars)
                    && array_key_exists('alternativeIdentifierLabels', $self->rowStatusVars)
                ) {
                    self::setAlternativeIdentifiers(
                        $self->object,
                        $self->rowStatusVars['alternativeIdentifiers'],
                        $self->rowStatusVars['alternativeIdentifierLabels']
                    );
                }

                // Set description status
                if (isset($self->rowStatusVars['descriptionStatus']) && 0 < strlen($self->rowStatusVars['descriptionStatus'])) {
                    $descStatus = trim($self->rowStatusVars['descriptionStatus']);
                    $statusTermId = self::arraySearchCaseInsensitive($descStatus, $self->status['descriptionStatusTypes'][$self->columnValue('culture')]);

                    if (false !== $statusTermId) {
                        $self->object->descriptionStatusId = $statusTermId;
                    } else {
                        echo "\nTerm {$descStatus} not found in description status taxonomy, creating it...\n";

                        $newTerm = QubitFlatfileImport::createTerm(QubitTaxonomy::DESCRIPTION_STATUS_ID, $descStatus, $self->columnValue('culture'));
                        $self->status['descriptionStatusTypes'] = self::refreshTaxonomyTerms(QubitTaxonomy::DESCRIPTION_STATUS_ID);

                        $self->object->descriptionStatusId = $newTerm->id;
                    }
                }

                // Set publication status
                if (isset($self->rowStatusVars['publicationStatus']) && 0 < strlen($self->rowStatusVars['publicationStatus'])) {
                    $pubStatusTermId = self::arraySearchCaseInsensitive(
                        $self->rowStatusVars['publicationStatus'],
                        $self->status['pubStatusTypes'][trim($self->columnValue('culture'))]
                    );

                    if (!$pubStatusTermId) {
                        echo "\nPublication status: '".$self->rowStatusVars['publicationStatus']."' is invalid. Using default.\n";
                        $pubStatusTermId = $self->status['defaultStatusId'];
                    }
                } else {
                    $pubStatusTermId = $self->status['defaultStatusId'];
                }

                $self->object->setPublicationStatus($pubStatusTermId);

                if (isset($self->rowStatusVars['qubitParentSlug']) && $self->rowStatusVars['qubitParentSlug']) {
                    $parentId = $self->getIdCorrespondingToSlug($self->rowStatusVars['qubitParentSlug']);
                } else {
                    if (!isset($self->rowStatusVars['parentId']) || !$self->rowStatusVars['parentId']) {
                        // Don't overwrite valid parentId when importing an QubitInformationObjectI18n translation row
                        if ($notImportingTranslation && !isset($self->object->parentId)) {
                            $parentId = $self->status['defaultParentId'];
                        }
                    } else {
                        if (
                            $mapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
                                $self->rowStatusVars['parentId'],
                                $self->getStatus('sourceName'),
                                'information_object'
                            )
                        ) {
                            $parentId = $mapEntry->target_id;
                        } elseif (null !== QubitInformationObject::getById($self->rowStatusVars['parentId'])) {
                            $parentId = $self->rowStatusVars['parentId'];
                        } else {
                            $error = sprintf(
                                'legacyId %s: could not find parentId %s in key_map table or existing data. Setting parent to root...',
                                $self->rowStatusVars['legacyId'],
                                $self->rowStatusVars['parentId']
                            );

                            echo $self->logError($error);

                            // Set parent if not importing an QubitInformationObjectI18n translation row
                            if ($notImportingTranslation) {
                                $self->object->parentId = QubitInformationObject::ROOT_ID;
                            }
                        }
                    }
                }

                if (isset($parentId) && $notImportingTranslation) {
                    $self->object->parentId = $parentId;
                }

                $self->object->indexOnSave = false;
            },

            // Import logic to execute after saving information object
            'postSaveLogic' => function (&$self) {
                if (!$self->object->id) {
                    throw new sfException('Information object save failed');
                }

                // If importing a translation row, don't deal with related data
                if ($self->object instanceof QubitInformationObjectI18n) {
                    return;
                }

                // Add keymap entry if not in round trip mode
                if (!$self->roundtrip) {
                    $self->createKeymapEntry($self->getStatus('sourceName'), $self->rowStatusVars['legacyId']);
                }

                // Inherit repository, instead of duplicating the association to it, if applicable
                if ($self->object instanceof QubitInformationObject && $self->object->canInheritRepository($self->object->repositoryId)) {
                    // Use raw SQL since we don't want an entire save() here.
                    $sql = 'UPDATE information_object SET repository_id = NULL WHERE id = ?';
                    QubitPdo::prepareAndExecute($sql, [$self->object->id]);

                    $self->object->repositoryId = null;
                }

                // Add physical objects
                csvImportBaseTask::importPhysicalObjects($self);

                // Add subject access points
                $accessPointColumns = [
                    'subjectAccessPoints' => QubitTaxonomy::SUBJECT_ID,
                    'placeAccessPoints' => QubitTaxonomy::PLACE_ID,
                    'genreAccessPoints' => QubitTaxonomy::GENRE_ID,
                ];

                foreach ($accessPointColumns as $columnName => $taxonomyId) {
                    if (isset($self->rowStatusVars[$columnName])) {
                        // Create/relate terms from array of term names.
                        $self->createOrFetchTermAndAddRelation($taxonomyId, $self->rowStatusVars[$columnName]);

                        $index = 0;
                        foreach ($self->rowStatusVars[$columnName] as $subject) {
                            if ($subject) {
                                $scope = false;
                                if (isset($self->rowStatusVars['subjectAccessPointScopes'][$index])) {
                                    $scope = $self->rowStatusVars['subjectAccessPointScopes'][$index];
                                }

                                if ($scope) {
                                    // Get term ID
                                    $query = "SELECT t.id FROM term t \r
                                        INNER JOIN term_i18n i ON t.id=i.id \r
                                        WHERE i.name=? AND t.taxonomy_id=? AND culture='en'";

                                    $statement = QubitFlatfileImport::sqlQuery(
                                        $query,
                                        [$subject, $taxonomyId]
                                    );

                                    $result = $statement->fetch(PDO::FETCH_OBJ);

                                    if ($result) {
                                        $termId = $result->id;

                                        // Check if a scope note already exists for this term
                                        $query = 'SELECT n.id FROM note n INNER JOIN note_i18n i ON n.id=i.id WHERE n.object_id=? AND n.type_id=?';

                                        $statement = QubitFlatfileImport::sqlQuery(
                                            $query,
                                            [$termId, QubitTerm::SCOPE_NOTE_ID]
                                        );

                                        $result = $statement->fetch(PDO::FETCH_OBJ);

                                        if (!$result) {
                                            // Add scope note if it doesn't exist
                                            $note = new QubitNote();
                                            $note->objectId = $termId;
                                            $note->typeId = QubitTerm::SCOPE_NOTE_ID;
                                            $note->content = $self->content($scope);
                                            $note->scope = 'QubitTerm'; // Not sure if this is needed
                                            $note->save();
                                        }
                                    } else {
                                        throw new sfException('Could not find term "'.$subject.'"');
                                    }
                                }
                            }
                            ++$index;
                        }
                    }
                }

                // Add name access points
                if (isset($self->rowStatusVars['nameAccessPoints'])) {
                    // Add name access points
                    $index = 0;
                    foreach ($self->rowStatusVars['nameAccessPoints'] as $name) {
                        // Skip blank names
                        if ($name) {
                            $actorOptions = [];
                            if (isset($self->rowStatusVars['nameAccessPointHistories'][$index])) {
                                $actorOptions['history'] = $self->rowStatusVars['nameAccessPointHistories'][$index];
                            }

                            if (null !== $repo = $self->object->getRepository(['inherit' => true])) {
                                $actorOptions['repositoryId'] = $repo->id;
                            }

                            $actor = $self->createOrFetchAndUpdateActorForIo($name, $actorOptions);
                            $self->createRelation($self->object->id, $actor->id, QubitTerm::NAME_ACCESS_POINT_ID);
                        }

                        ++$index;
                    }
                }

                // Add accessions
                if (
                    isset($self->rowStatusVars['accessionNumber'])
                    && count($self->rowStatusVars['accessionNumber'])
                ) {
                    foreach ($self->rowStatusVars['accessionNumber'] as $accessionNumber) {
                        // Attempt to fetch keymap entry
                        $accessionMapEntry = $self->fetchKeymapEntryBySourceAndTargetName(
                            $accessionNumber,
                            $self->getStatus('sourceName'),
                            'accession'
                        );

                        // If no entry found, create accession and entry
                        if (!$accessionMapEntry) {
                            $criteria = new Criteria();
                            $criteria->add(QubitAccession::IDENTIFIER, $accessionNumber);

                            if (null === $accession = QubitAccession::getone($criteria)) {
                                echo "\nCreating accession # ".$accessionNumber."\n";

                                // Create new accession
                                $accession = new QubitAccession();
                                $accession->identifier = $accessionNumber;
                                $accession->save();

                                // Create keymap entry for accession
                                $self->createKeymapEntry($self->getStatus('sourceName'), $accessionNumber, $accession);
                            }

                            $accessionId = $accession->id;
                        } else {
                            $accessionId = $accessionMapEntry->target_id;
                        }

                        echo "\nAssociating accession # ".$accessionNumber.' with '.$self->object->title."\n";

                        // Add relationship between information object and accession
                        $self->createRelation($self->object->id, $accessionId, QubitTerm::ACCESSION_ID);
                    }
                }

                // Add material-related term relation
                if (isset($self->rowStatusVars['radGeneralMaterialDesignation'])) {
                    foreach ($self->rowStatusVars['radGeneralMaterialDesignation'] as $material) {
                        $self->createObjectTermRelation($self->object->id, $material);
                    }
                }

                // Add copyright info
                // TODO: handle this via a separate import
                if (isset($self->rowStatusVars['copyrightStatus']) && $self->rowStatusVars['copyrightStatus']) {
                    switch (strtolower($self->rowStatusVars['copyrightStatus'])) {
                        case 'under copyright':
                            print 'Adding rights for '.$self->object->title."...\n";
                            $rightsHolderId = false;
                            $rightsHolderNames = explode('|', $self->rowStatusVars['copyrightHolder']);

                            if ($self->rowStatusVars['copyrightExpires']) {
                                $endDates = explode('|', $self->rowStatusVars['copyrightExpires']);
                            }

                            foreach ($rightsHolderNames as $index => $rightsHolderName) {
                                $rightsHolderName = ($rightsHolderName) ? $rightsHolderName : 'Unknown';
                                $rightsHolder = $self->createOrFetchRightsHolder($rightsHolderName);
                                $rightsHolderId = $rightsHolder->id;

                                $rightsHolderName = trim(strtolower($rightsHolderName));
                                if ('city of vancouver' == $rightsHolderName || 0 === strpos($rightsHolderName, 'city of vancouver')) {
                                    $restriction = 1;
                                } else {
                                    $restriction = 0;
                                }

                                $rightAndRelation = [
                                    'restriction' => $restriction,
                                    'basisId' => QubitTerm::RIGHT_BASIS_COPYRIGHT_ID,
                                    'actId' => array_search(
                                        'Replicate',
                                        $self->getStatus('copyrightActTypes')
                                    ),
                                    'copyrightStatusId' => array_search(
                                        'Under copyright',
                                        $self->getStatus('copyrightStatusTypes')
                                    ),
                                ];

                                if (isset($endDates)) {
                                    // If rightsholder/expiry dates and paired, use
                                    // corresponding date, otherwise just use the
                                    // first expiry date
                                    $rightAndRelation['endDate'] = (count($endDates) == count($rightsHolderNames))
                                        ? $endDates[$index]
                                        : $endDates[0];

                                    if (!is_numeric($rightAndRelation['endDate'])) {
                                        throw new sfException(
                                            'Copyright expiry '.$rightAndRelation['endDate'].' is invalid.'
                                        );
                                    }
                                }

                                if ($rightsHolderId) {
                                    $rightAndRelation['rightsHolderId'] = $rightsHolderId;
                                }

                                $self->createRightAndRelation($rightAndRelation);
                            }

                            break;

                        case 'unknown':
                            $rightsHolder = $self->createOrFetchRightsHolder('Unknown');
                            $rightsHolderId = $rightsHolder->id;

                            $rightAndRelation = [
                                'rightsHolderId' => $rightsHolderId,
                                'restriction' => 0,
                                'basisId' => QubitTerm::RIGHT_BASIS_COPYRIGHT_ID,
                                'actId' => array_search(
                                    'Replicate',
                                    $self->getStatus('copyrightActTypes')
                                ),
                                'copyrightStatusId' => array_search(
                                    'Unknown',
                                    $self->getStatus('copyrightStatusTypes')
                                ),
                            ];

                            if ($self->rowStatusVars['copyrightExpires']) {
                                $rightAndRelation['endDate'] = $self->rowStatusVars['copyrightExpires'];
                            }

                            $self->createRightAndRelation($rightAndRelation);

                            break;

                        case 'public domain':
                            $rightAndRelation = [
                                'restriction' => 1,
                                'basisId' => QubitTerm::RIGHT_BASIS_COPYRIGHT_ID,
                                'actId' => array_search(
                                    'Replicate',
                                    $self->getStatus('copyrightActTypes')
                                ),
                                'copyrightStatusId' => array_search(
                                    'Public domain',
                                    $self->getStatus('copyrightStatusTypes')
                                ),
                            ];

                            if ($self->rowStatusVars['copyrightExpires']) {
                                $rightAndRelation['endDate'] = $self->rowStatusVars['copyrightExpires'];
                            }

                            $self->createRightAndRelation($rightAndRelation);

                            break;

                        default:
                            throw new sfException(
                                'Copyright status "'
                                .$self->rowStatusVars['copyrightStatus']
                                .'" not handled: adjust script or import data'
                            );

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
                $this->importDigitalObject($self);

                // Re-index to add translations and related resources
                if (!$self->searchIndexingDisabled) {
                    $node = new arElasticSearchInformationObjectPdo($self->object->id);
                    QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitInformationObject');
                }

                // Reduce memory usage
                Qubit::clearClassCaches();
            },
        ]);

        $import->searchIndexingDisabled = ($options['index']) ? false : true;

        // Disable nested set update per row
        $import->disableNestedSetUpdating = true;

        $import->setUpdateOptions($options);

        // Convert content with | characters to a bulleted list
        $import->contentFilterLogic = function ($text) {
            return (substr_count($text, '|')) ? '* '.str_replace('|', "\n* ", $text) : $text;
        };

        $import->addColumnHandler('levelOfDescription', function ($self, $data) {
            $self->object->setLevelOfDescriptionByName(trim($data));
        });

        // Map value to taxonomy term name and take note of taxonomy term's ID
        $import->addColumnHandler('radGeneralMaterialDesignation', function ($self, $data) {
            if ($data) {
                $data = array_map('trim', explode('|', $data));

                foreach ($data as $value) {
                    $value = trim($value);
                    $materialTypeId = self::arraySearchCaseInsensitive($value, $self->status['materialTypes'][$self->columnValue('culture')]);

                    if (false !== $materialTypeId) {
                        $self->rowStatusVars['radGeneralMaterialDesignation'][] = $materialTypeId;
                    } else {
                        echo "\nTerm {$value} not found in material type taxonomy, creating it...\n";

                        $newTerm = QubitFlatfileImport::createTerm(QubitTaxonomy::MATERIAL_TYPE_ID, $value, $self->columnValue('culture'));
                        $self->status['materialTypes'] = self::refreshTaxonomyTerms(QubitTaxonomy::MATERIAL_TYPE_ID);

                        $self->rowStatusVars['radGeneralMaterialDesignation'][] = $newTerm->id;
                    }
                }
            }
        });

        $import->csv($fh, $skipRows);

        // Rebuild entire nested set for IOs
        if (!$options['skip-nested-set-build']) {
            $this->updateIosNestedSet();
        }
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        parent::configure();

        $this->addOptions([
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
                'Index for search during import.'
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
                'When importing records with --update, skip creating new records if no existing records match.'
            ),
            new sfCommandOption(
                'skip-derivatives',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Skip creation of digital object derivatives.'
            ),
            new sfCommandOption(
                'limit',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Limit --update matching to under a specified top level description or repository via slug.'
            ),
            new sfCommandOption(
                'user-id',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'User ID to run import as',
                null
            ),
            new sfCommandOption(
                'keep-digital-objects',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Skip the deletion of existing digital objects and their derivatives when using --update with "match-and-update".'
            ),
            new sfCommandOption(
                'roundtrip',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Treat legacy IDs as internal IDs.'
            ),
            new sfCommandOption(
                'no-confirmation',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Do not ask for confirmation'
            ),
        ]);
    }

    /**
     * Return default parent id based on various CLI options.
     *
     * @param string $sourceName The source name of this file
     * @param array  $options    CLI options
     *
     * @return mixed The default parent id
     */
    private function getDefaultParentId($sourceName, $options)
    {
        // Allow default parent ID to be overridden by CLI options
        if ($options['default-parent-slug']) {
            $parentId = QubitFlatfileImport::getIdCorrespondingToSlug($options['default-parent-slug']);

            if (!$options['quiet']) {
                $this->log("Parent ID of slug {$options['default-parent-slug']} is {$parentId}");
            }
        } elseif ($options['default-legacy-parent-id']) {
            if (
                false === $keyMapEntry = QubitFlatfileImport::fetchKeymapEntryBySourceAndTargetName(
                    $options['default-legacy-parent-id'],
                    $sourceName,
                    'information_object'
                )
            ) {
                throw new sfException(
                    'Could not find keymap entry for default legacy parent ID '
                    .$options['default-legacy-parent-id']
                );
            }

            $parentId = $keyMapEntry->target_id;
            $this->log("Using default parent ID {$parentId} (legacy parent ID {$options['default-legacy-parent-id']})");
        } else {
            $parentId = QubitInformationObject::ROOT_ID;
        }

        return $parentId;
    }

    private function updateIosNestedSet($retryCount = 0)
    {
        try {
            $nestedSetTask = new propelBuildNestedSetTask($this->dispatcher, $this->formatter);
            $nestedSetTask->setConfiguration($this->configuration);
            $nestedSetTask->run([], ['exclude-tables' => 'term,menu']);
        } catch (PDOException $e) {
            // Repeat on transaction deadlock (MySQL error code 1213)
            if (1213 == $e->errorInfo[1] && $retryCount < 3) {
                $this->updateIosNestedSet(++$retryCount);
            }

            throw $e;
        }
    }
}
