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
 * Import csv authoriy record data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvAuthorityRecordImportTask extends csvImportBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'authority-import';
    protected $briefDescription = 'Import csv authority record data';
    protected $detailedDescription = <<<'EOF'
Import CSV data
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
            QubitTaxonomy::NOTE_TYPE_ID => 'noteTypes',
            QubitTaxonomy::ACTOR_ENTITY_TYPE_ID => 'actorTypes',
            QubitTaxonomy::ACTOR_RELATION_TYPE_ID => 'actorRelationTypes',
            QubitTaxonomy::DESCRIPTION_STATUS_ID => 'descriptionStatusTypes',
            QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID => 'detailLevelTypes',
        ]);

        // Define helper function used in post-save logic
        $relateTermFunction = function ($import, $column, $taxonomyId) {
            if (empty($import->rowStatusVars[$column])) {
                return;
            }

            $terms = explode('|', $import->rowStatusVars[$column]);

            for ($i = 0; $i < count($terms); ++$i) {
                if (empty($terms[$i])) {
                    continue;
                }

                // Attempt to create relation to term object
                $relation = QubitActor::setTermRelationByName(
                    $terms[$i],
                    [
                        'taxonomyId' => $taxonomyId,
                        'culture' => $import->columnValue('culture'),
                    ]
                );

                if (null === $relation) {
                    continue;
                }

                // If a relation to term object could be created, associate it with this actor and save
                $relationAlreadyExists = QubitFlatfileImport::objectTermRelationExists($import->object->id, $relation->termId);

                if (!$relationAlreadyExists) {
                    $relation->object = $import->object;
                    $relation->save();
                }
            }
        };

        // Define import
        $import = new QubitFlatfileImport([
            // Pass context
            'context' => sfContext::createInstance($this->configuration),

            // What type of object are we importing?
            'className' => 'QubitActor',

            // How many rows should import until we display an import status update?
            'rowsUntilProgressDisplay' => $options['rows-until-update'],

            // Where to log errors to
            'errorLog' => $options['error-log'],

            // The status array is a place to put data that should be accessible
            // from closure logic using the getStatus method
            'status' => [
                'sourceName' => $sourceName,
                'actorTypes' => $termData['actorTypes'],
                'descriptionStatusTypes' => $termData['descriptionStatusTypes'],
                'detailLevelTypes' => $termData['detailLevelTypes'],
                'aliases' => $aliases,
                'actorNames' => [],
                'relateTermFunction' => $relateTermFunction,
            ],

            // Import columns that map directory to QubitActor properties
            'standardColumns' => [
                'authorizedFormOfName',
                'corporateBodyIdentifiers',
                'datesOfExistence',
                'history',
                'places',
                'legalStatus',
                'functions',
                'mandates',
                'internalStructures',
                'generalContext',
                'descriptionIdentifier',
                'rules',
                'revisionHistory',
                'sources',
            ],

            // Import columns that should be redirected to QubitActor
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
                'institutionIdentifier' => 'institutionResponsibleIdentifier',
            ],

            // Import columns that can be added as QubitNote objects
            'noteMap' => [
                'maintenanceNotes' => [
                    'typeId' => array_search('Maintenance note', $termData['noteTypes']['en']),
                ],
            ],

            // These values get stored to the rowStatusVars array
            'variableColumns' => [
                'typeOfEntity',
                'status',
                'levelOfDetail',
                'email',
                'notes',
                'countryCode',
                'fax',
                'telephone',
                'postalCode',
                'streetAddress',
                'region',
                'actorOccupations',
                'actorOccupationNotes',
                'placeAccessPoints',
                'subjectAccessPoints',
                'digitalObjectPath',
                'digitalObjectURI',
                'digitalObjectChecksum',
            ],

            // These values get exploded and stored to the rowStatusVars array
            'arrayColumns' => [
                'parallelFormsOfName' => '|',
                'standardizedFormsOfName' => '|',
                'otherFormsOfName' => '|',
                'script' => '|',
            ],

            'updatePreparationLogic' => function (&$self) {
                $this->deleteDigitalObjectIfUpdatingAndNotKeeping($self);
            },

            // Import logic to execute before saving actor
            'preSaveLogic' => function (&$self) {
                if ($self->object) {
                    // Warn or abort if identifier's already been used
                    if (
                        $self->columnExists('descriptionIdentifier')
                        && !empty($identifier = $self->columnValue('descriptionIdentifier'))
                        && QubitValidatorActorDescriptionIdentifier::identifierUsedByAnotherActor($identifier, $self->object)
                    ) {
                        $error = sfContext::getInstance()
                            ->i18n
                            ->__(
                                '%1% identifier "%2%" not unique.',
                                [
                                    '%1%' => sfConfig::get('app_ui_label_actor'),
                                    '%2%' => $identifier,
                                ]
                            );

                        if (sfConfig::get('app_prevent_duplicate_actor_identifiers', false)) {
                            $error .= sfContext::getInstance()->i18n->__(' Import aborted.');

                            throw new sfException($error);
                        }

                        echo $self->logError($error);
                    }

                    // Handle actor types.
                    $self->object->entityTypeId = $self->createOrFetchTermIdFromName(
                        'actor entity type',
                        $self->rowStatusVars['typeOfEntity'],
                        $self->columnValue('culture'),
                        $self->status['actorTypes'],
                        QubitTaxonomy::ACTOR_ENTITY_TYPE_ID
                    );

                    // Handle description status.
                    $self->object->descriptionStatusId = $self->createOrFetchTermIdFromName(
                        'description status',
                        $self->rowStatusVars['status'],
                        $self->columnValue('culture'),
                        $self->status['descriptionStatusTypes'],
                        QubitTaxonomy::DESCRIPTION_STATUS_ID
                    );

                    // Handle level of detail.
                    $self->object->descriptionDetailId = $self->createOrFetchTermIdFromName(
                        'description detail levels',
                        $self->rowStatusVars['levelOfDetail'],
                        $self->columnValue('culture'),
                        $self->status['detailLevelTypes'],
                        QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID
                    );
                }
            },

            // Import logic to execute after saving actor
            'postSaveLogic' => function (&$self) {
                if ($self->object) {
                    // Note actor name for optional relationship import phase
                    $self->status['actorNames'][$self->object->id] = $self->object->authorizedFormOfName;

                    csvImportBaseTask::importAlternateFormsOfName($self);

                    // Add contact information, if applicable
                    $contactVariables = [
                        'email',
                        'notes',
                        'countryCode',
                        'fax',
                        'telephone',
                        'postalCode',
                        'streetAddress',
                        'region',
                    ];

                    $hasContactInfo = false;
                    foreach (array_keys($self->rowStatusVars) as $name) {
                        if (in_array($name, $contactVariables)) {
                            $hasContactInfo = true;
                        }
                    }

                    if ($hasContactInfo) {
                        // Add contact information
                        $info = new QubitContactInformation();
                        $info->actorId = $self->object->id;

                        foreach ($contactVariables as $property) {
                            if ($self->rowStatusVars[$property]) {
                                $info->{$property} = $self->rowStatusVars[$property];
                            }
                        }

                        $info->save();
                    }

                    // Add placeAccessPoints and subjectAccessPoints
                    $self->status['relateTermFunction']($self, 'placeAccessPoints', QubitTaxonomy::PLACE_ID);
                    $self->status['relateTermFunction']($self, 'subjectAccessPoints', QubitTaxonomy::SUBJECT_ID);

                    // Add occupations
                    if (!empty($self->rowStatusVars['actorOccupations'])) {
                        $occupations = explode('|', $self->rowStatusVars['actorOccupations']);
                        $occupationNotes = [];

                        if (!empty($self->rowStatusVars['actorOccupationNotes'])) {
                            $occupationNotes = explode('|', $self->rowStatusVars['actorOccupationNotes']);
                        }

                        for ($i = 0; $i < count($occupations); ++$i) {
                            if (empty($occupations[$i])) {
                                continue;
                            }

                            if (null !== $relation = QubitActor::setTermRelationByName($occupations[$i], $options = ['taxonomyId' => QubitTaxonomy::ACTOR_OCCUPATION_ID, 'culture' => $self->columnValue('culture')])) {
                                $relation->object = $self->object;
                                $relation->save();

                                if (!empty($occupationNotes[$i]) && 'NULL' !== $occupationNotes[$i]) {
                                    $note = new QubitNote();
                                    $note->typeId = QubitTerm::ACTOR_OCCUPATION_NOTE_ID;
                                    $note->content = $occupationNotes[$i];
                                    $note->object = $relation;
                                    $note->save();
                                }
                            }
                        }
                    }

                    // Add digital object
                    $this->importDigitalObject($self);

                    // Re-index to add related resources
                    if (!$self->searchIndexingDisabled) {
                        QubitSearch::getInstance()->update($self->object);
                    }

                    Qubit::clearClassCaches();
                }
            },
        ]);

        // Allow search indexing to be enabled via a CLI option
        $import->searchIndexingDisabled = ($options['index']) ? false : true;

        // Set update, limit and skip options
        $import->setUpdateOptions($options);

        $import->csv($fh, $skipRows);
    }

    /**
     * @see csvImportBaseTask
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
                'update',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Attempt to update if an actor already exists. Valid option values are "match-and-update" and "delete-and-replace".'
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
                'keep-digital-objects',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Skip the deletion of existing digital objects and their derivatives when using --update with "match-and-update".'
            ),
            new sfCommandOption(
                'limit',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Limit --update matching to under a specified maintaining repository via slug.'
            ),
        ]);
    }
}
