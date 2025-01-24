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
 * Import csv accession data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvAccessionImportTask extends csvImportBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'accession-import';
    protected $briefDescription = 'Import csv acession data';
    protected $detailedDescription = <<<'EOF'
Import CSV accession data
EOF;

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

        $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

        $sourceName = ($options['source-name'])
            ? $options['source-name']
            : basename($arguments['filename']);

        if (false === $fh = fopen($arguments['filename'], 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        $databaseManager = new sfDatabaseManager($this->configuration);
        $conn = $databaseManager->getDatabase('propel')->getConnection();

        // Load taxonomies into variables to avoid use of magic numbers
        $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
            QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID => 'acquisitionTypes',
            QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID => 'resourceTypes',
            QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID => 'processingStatus',
            QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID => 'processingPriority',
            QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID => 'alternativeIdentifierTypes',
            QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID => 'physicalObjectTypes',
            QubitTaxonomy::ACCESSION_EVENT_TYPE_ID => 'accessionEventTypes',
        ]);

        // Define import
        $import = new QubitFlatfileImport([
            // Pass context
            'context' => sfContext::createInstance($this->configuration),

            // How many rows should import until we display an import status update?
            'rowsUntilProgressDisplay' => $options['rows-until-update'],

            // Where to log errors to
            'errorLog' => $options['error-log'],

            // The status array is a place to put data that should be accessible
            // from closure logic using the getStatus method
            'status' => [
                'sourceName' => $sourceName,
                'acquisitionTypes' => $termData['acquisitionTypes'],
                'resourceTypes' => $termData['resourceTypes'],
                'physicalObjectTypes' => $termData['physicalObjectTypes'],
                'processingStatus' => $termData['processingStatus'],
                'processingPriority' => $termData['processingPriority'],
                'alternativeIdentifierTypes' => $termData['alternativeIdentifierTypes'],
                'accessionEventTypes' => $termData['accessionEventTypes'],
                'assignId' => $options['assign-id'],
            ],

            'standardColumns' => [
                'appraisal',
                'archivalHistory',
                'acquisitionDate',
                'locationInformation',
                'processingNotes',
                'receivedExtentUnits',
                'scopeAndContent',
                'sourceOfAcquisition',
                'title',
            ],

            'arrayColumns' => [
                'alternativeIdentifiers' => '|',
                'alternativeIdentifierTypes' => '|',
                'alternativeIdentifierNotes' => '|',

                'eventActors' => '|',
                'eventActorHistories' => '|',
                'eventTypes' => '|',
                'eventPlaces' => '|',
                'eventDates' => '|',
                'eventStartDates' => '|',
                'eventEndDates' => '|',
                'eventDescriptions' => '|',

                'accessionEventTypes' => '|',
                'accessionEventDates' => '|',
                'accessionEventAgents' => '|',
                'accessionEventNotes' => '|',

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
                'creationDatesType' => '|',
            ],

            'columnMap' => [
                'physicalCondition' => 'physicalCharacteristics',
            ],

            // These values get stored to the rowStatusVars array
            'variableColumns' => [
                'accessionNumber',
                'acquisitionType',
                'resourceType',
                'physicalObjectName',
                'physicalObjectLocation',
                'physicalObjectType',
                'donorName',
                'donorStreetAddress',
                'donorCity',
                'donorRegion',
                'donorCountry',
                'donorPostalCode',
                'donorCountry',
                'donorTelephone',
                'donorFax',
                'donorContactPerson',
                'donorEmail',
                'donorNote',
                'qubitParentSlug',
            ],

            // Import logic to load accession
            'rowInitLogic' => function (&$self) {
                $accessionNumber = $self->rowStatusVars['accessionNumber'];

                // Look up Qubit ID of pre-created accession
                $statement = $self->sqlQuery(
                    'SELECT id FROM accession WHERE identifier=?',
                    $params = [$accessionNumber]
                );

                $result = $statement->fetch(PDO::FETCH_OBJ);
                if ($result) {
                    echo $self->logError(sprintf('Found accession ID %d with identifier %s', $result->id, $accessionNumber));
                    $self->object = QubitAccession::getById($result->id);
                } elseif (!empty($accessionNumber)) {
                    echo $self->logError(sprintf('Could not find accession # %s, creating.', $accessionNumber));
                    $self->object = new QubitAccession();
                    $self->object->identifier = $accessionNumber;
                } elseif ($self->getStatus('assignId')) {
                    $identifier = QubitAccession::nextAvailableIdentifier();
                    echo $self->logError(sprintf('No accession number, creating accession with identifier %s', $identifier));
                    $self->object = new QubitAccession();
                    $self->object->identifier = $identifier;
                } else {
                    echo $self->logError('No accession number, skipping');
                }
            },

            // Import logic to save accession
            'saveLogic' => function (&$self) {
                // If row was skipped due to not having an accession number, don't attempt to save
                if (isset($self->object) && $self->object instanceof QubitAccession) {
                    $self->object->save();
                }
            },

            // Create related objects
            'postSaveLogic' => function (&$self) {
                // If row was skipped due to not having an accession number, don't create related objects
                if (isset($self->object) && $self->object instanceof QubitAccession && isset($self->object->id)) {
                    // Add creators
                    if (
                        isset($self->rowStatusVars['creators'])
                        && $self->rowStatusVars['creators']
                    ) {
                        foreach ($self->rowStatusVars['creators'] as $creator) {
                            // Fetch/create actor
                            $actor = $self->createOrFetchActor($creator);

                            // Create relation between accession and creator
                            $self->createRelation($actor->id, $self->object->id, QubitTerm::CREATION_ID);
                        }
                    }

                    // Add alternative identifiers
                    $identifiers = $self->rowStatusVars['alternativeIdentifiers'];
                    $identifierNotes = $self->rowStatusVars['alternativeIdentifierNotes'];

                    if (!empty($identifiers) || !empty($identifierNotes)) {
                        $identifierTypes = $self->rowStatusVars['alternativeIdentifierTypes'];

                        $identifierCount = empty($identifiers) ? 0 : count($identifiers);
                        $identifierNotesCount = empty($identifierNotes) ? 0 : count($identifierNotes);
                        for ($index = 0; $index < max($identifierCount, $identifierNotesCount); ++$index) {
                            $identifier = (empty($identifiers[$index])) ? null : $identifiers[$index];

                            if (!empty($identifier) || !empty($identifierNotes[$index])) {
                                $otherName = new QubitOtherName();
                                $otherName->object = $self->object;
                                $otherName->name = $identifier;

                                // Set type attribute, determine alternative identifier type ID if a type name's specified
                                $otherName->typeId = QubitTerm::ACCESSION_ALTERNATIVE_IDENTIFIER_DEFAULT_TYPE_ID;
                                if (!empty($typeName = $identifierTypes[$index])) {
                                    // Create new accession identifier type term, if necessary
                                    if (empty($typeId = self::arraySearchCaseInsensitive($typeName, $self->status['alternativeIdentifierTypes'][$self->columnValue('culture')]))) {
                                        $term = new QubitTerm();
                                        $term->parentId = QubitTerm::ROOT_ID;
                                        $term->taxonomyId = QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID;
                                        $term->setName($typeName, ['culture' => $self->columnValue('culture')]);
                                        $term->sourceCulture = $self->columnValue('culture');
                                        $term->save();

                                        $self->status['alternativeIdentifierTypes'][$self->columnValue('culture')][$term->id] = $typeName;

                                        $typeId = $term->id;
                                    }

                                    $otherName->typeId = $typeId;
                                }

                                // Set type note, if specified
                                if (!empty($note = $identifierNotes[$index])) {
                                    $otherName->setNote($note, ['culture' => $self->columnValue('culture')]);
                                }

                                $otherName->culture = $self->columnValue('culture');
                                $otherName->save();
                            }
                        }
                    }

                    // Add physical objects
                    csvImportBaseTask::importPhysicalObjects($self);

                    // Add events
                    csvImportBaseTask::importEvents($self);

                    // Add accession events
                    $eventTypes = $self->rowStatusVars['accessionEventTypes'];
                    $eventDates = $self->rowStatusVars['accessionEventDates'];

                    if (!empty($eventTypes) || !empty($eventDates)) {
                        $eventAgents = $self->rowStatusVars['accessionEventAgents'];
                        $eventNotes = $self->rowStatusVars['accessionEventNotes'];

                        for ($index = 0; $index < count($eventTypes); ++$index) {
                            $eventType = (empty($eventTypes[$index])) ? null : $eventTypes[$index];
                            $eventDate = (empty($eventDates[$index])) ? null : $eventDates[$index];

                            if (!empty($eventType) && !empty($eventDate)) {
                                // Create new accession event type term, if necessary
                                if (empty($typeId = self::arraySearchCaseInsensitive($eventType, $self->status['accessionEventTypes'][$self->columnValue('culture')]))) {
                                    $term = new QubitTerm();
                                    $term->parentId = QubitTerm::ROOT_ID;
                                    $term->taxonomyId = QubitTaxonomy::ACCESSION_EVENT_TYPE_ID;
                                    $term->setName($eventType, ['culture' => $self->columnValue('culture')]);
                                    $term->sourceCulture = $self->columnValue('culture');
                                    $term->save();

                                    $self->status['accessionEventTypes'][$self->columnValue('culture')][$term->id] = $eventType;

                                    $typeId = $term->id;
                                }

                                $eventAgent = (empty($eventAgents[$index])) ? null : $eventAgents[$index];
                                $eventNoteText = (empty($eventNotes[$index])) ? null : $eventNotes[$index];

                                $event = new QubitAccessionEvent();
                                $event->accessionId = $self->object->id;
                                $event->typeId = $typeId;
                                $event->date = $eventDate;
                                $event->agent = $eventAgent;
                                $event->save();

                                // Add accession event notes
                                if (!empty($eventNoteText)) {
                                    $note = new QubitNote();
                                    $note->objectId = $event->id;
                                    $note->typeId = QubitTerm::ACCESSION_EVENT_NOTE_ID;
                                    $note->setContent($eventNoteText, ['culture' => $self->columnValue('culture')]);
                                    $note->save();
                                }
                            }
                        }
                    }

                    if (
                        isset($self->rowStatusVars['donorName'])
                        && $self->rowStatusVars['donorName']
                    ) {
                        // Fetch/create donor
                        $donor = $self->createOrFetchDonor($self->rowStatusVars['donorName']);

                        // Map column names to QubitContactInformation properties
                        $columnToProperty = [
                            'donorEmail' => 'email',
                            'donorTelephone' => 'telephone',
                            'donorFax' => 'fax',
                            'donorStreetAddress' => 'streetAddress',
                            'donorCity' => 'city',
                            'donorRegion' => 'region',
                            'donorPostalCode' => 'postalCode',
                            'donorNote' => 'note',
                            'donorContactPerson' => 'contactPerson',
                        ];

                        // Set up creation of contact infomation
                        $contactData = [];
                        foreach ($columnToProperty as $column => $property) {
                            if (isset($self->rowStatusVars[$column])) {
                                $contactData[$property] = $self->rowStatusVars[$column];
                            }
                        }

                        // Attempt to coerce country to country code if value specified (and not already a country code)
                        if (!empty($self->rowStatusVars['donorCountry'])) {
                            $countryCode = QubitFlatfileImport::normalizeCountryAsCountryCode($self->rowStatusVars['donorCountry']);
                            if (null === $countryCode) {
                                echo sprintf("Could not find country or country code matching '%s'\n", $self->rowStatusVars['donorCountry']);
                            } else {
                                $contactData['countryCode'] = $countryCode;
                            }
                        }

                        // Create contact information if none exists
                        $self->createOrFetchContactInformation($donor->id, $contactData);

                        // Create relation between accession and donor
                        $self->createRelation($self->object->id, $donor->id, QubitTerm::DONOR_ID);
                    }

                    // Link accession to existing description
                    if (
                        isset($self->rowStatusVars['qubitParentSlug'])
                        && $self->rowStatusVars['qubitParentSlug']
                    ) {
                        $query = 'SELECT object_id FROM slug WHERE slug=?';
                        $statement = QubitFlatfileImport::sqlQuery($query, [$self->rowStatusVars['qubitParentSlug']]);
                        $result = $statement->fetch(PDO::FETCH_OBJ);
                        if ($result) {
                            $self->createRelation($result->object_id, $self->object->id, QubitTerm::ACCESSION_ID);
                        } else {
                            throw new sfException('Could not find information object matching slug "'.$self->rowStatusVars['qubitParentSlug'].'"');
                        }
                    }
                }

                // Add keymap entry
                if (!empty($self->rowStatusVars['accessionNumber'])) {
                    $self->createKeymapEntry($self->getStatus('sourceName'), $self->rowStatusVars['accessionNumber']);
                }

                // Re-index to add related resources
                if (!$self->searchIndexingDisabled) {
                    QubitSearch::getInstance()->update($self->object);
                }
            },
        ]);

        $import->addColumnHandler('acquisitionDate', function ($self, $data) {
            if ($data) {
                if (isset($self->object) && is_object($self->object)) {
                    $parsedDate = Qubit::parseDate($data);
                    if ($parsedDate) {
                        $self->object->date = $parsedDate;
                    } else {
                        $self->logError('Could not parse date: '.$data);
                    }
                }
            }
        });

        $import->addColumnHandler('resourceType', function ($self, $data) {
            if ($data && isset($self->object) && $self->object instanceof QubitAccession) {
                $self->object->resourceTypeId = $self->createOrFetchTermIdFromName(
                    'resource type',
                    trim($data),
                    $self->columnValue('culture'),
                    $self->status['resourceTypes'],
                    QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID
                );
            }
        });

        $import->addColumnHandler('acquisitionType', function ($self, $data) {
            if ($data && isset($self->object) && $self->object instanceof QubitAccession) {
                $self->object->acquisitionTypeId = $self->createOrFetchTermIdFromName(
                    'acquisition type',
                    trim($data),
                    $self->columnValue('culture'),
                    $self->status['acquisitionTypes'],
                    QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID
                );
            }
        });

        $import->addColumnHandler('processingStatus', function ($self, $data) {
            if ($data && isset($self->object) && $self->object instanceof QubitAccession) {
                $self->object->processingStatusId = $self->createOrFetchTermIdFromName(
                    'processing status',
                    trim($data),
                    $self->columnValue('culture'),
                    $self->status['processingStatus'],
                    QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID
                );
            }
        });

        $import->addColumnHandler('processingPriority', function ($self, $data) {
            if ($data && isset($self->object) && $self->object instanceof QubitAccession) {
                $self->object->processingPriorityId = $self->createOrFetchTermIdFromName(
                    'processing priority',
                    trim($data),
                    $self->columnValue('culture'),
                    $self->status['processingPriority'],
                    QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID
                );
            }
        });

        // Allow search indexing to be enabled via a CLI option
        $import->searchIndexingDisabled = ($options['index']) ? false : true;

        $import->csv($fh, $skipRows);
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
                'index',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Index for search during import.'
            ),
            new sfCommandOption(
                'assign-id',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Assign identifier, based on mask and counter, if no accession number specified in row.'
            ),
        ]);
    }
}
