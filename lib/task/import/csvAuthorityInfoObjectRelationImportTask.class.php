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
 * Import authority record to information object relations using CSV.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class csvAuthorityInfoObjectRelationImportTask extends csvImportBaseTask
{
    private $import;
    private $newlyAdded = [];

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $this->validateUpdateOptions($options);

        $this->log('Importing authority to information object relations...');

        $this->import($arguments['filename'], $options['index'], $options['update']);

        $this->log('Done.');
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'CSV filename'),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                true
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
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
                'Attempt to update if relation has already been imported. Valid option values are "match-and-update" & "delete-and-replace".'
            ),
        ]);

        $this->namespace = 'csv';
        $this->name = 'authority-info-object-relation-import';
        $this->briefDescription = 'Import authority record to information object relations using CSV data.';
        $this->detailedDescription = <<<'EOF'
Import authority record to information object relations using CSV data.
This task creates name access point relationships between existing authority records (actors) and information objects.

The relationships are created as QubitRelation objects with configurable relation types from the Event Type taxonomy.

The CSV should contain columns for:
- authorityAuthorizedFormOfName: The authorized form of name of the authority record
- informationObjectIdentifier: The identifier of the information object
- relationType: The type of relationship from the Event Type taxonomy (e.g., "Member", "Creator", "Contributor")
- relationTypeId: Optional relation type ID (overrides relationType if provided)
- culture: Optional culture code for multilingual lookups
EOF;
    }

    private function import($filepath, $indexDuringImport = false, $updateMode = false)
    {
        if (false === $fh = fopen($filepath, 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        // Load taxonomies into variables to avoid use of magic numbers
        $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
            QubitTaxonomy::EVENT_TYPE_ID => 'eventTypes',
        ]);

        $this->import = new QubitFlatfileImport([
            'context' => sfContext::createInstance($this->configuration),

            'status' => [
                'updateMode' => $updateMode,
                'eventTypes' => $termData['eventTypes'],
                'actorIds' => [],
                'ioIds' => [],
            ],

            'variableColumns' => [
                'authorityAuthorizedFormOfName',
                'informationObjectIdentifier',
                'relationType',
                'relationTypeId',
                'culture',
            ],

            'saveLogic' => function ($self) {
                // Get the authority record (actor)
                $actor = QubitActor::getByAuthorizedFormOfName(
                    $self->columnValue('authorityAuthorizedFormOfName'),
                    ['culture' => $self->columnValue('culture')]
                );

                if (!$actor) {
                    $error = sprintf('Authority record "%s" not found... skipping row.',
                        $self->columnValue('authorityAuthorizedFormOfName'));
                    echo $self->logError($error);

                    return;
                }

                // Get the information object
                $informationObject = null;
                $identifier = $self->columnValue('informationObjectIdentifier');

                if (!empty($identifier)) {
                    // First try to find by identifier
                    $query = 'SELECT id FROM information_object WHERE identifier = ?';
                    $statement = QubitFlatfileImport::sqlQuery($query, [$identifier]);
                    $result = $statement->fetch(PDO::FETCH_OBJ);

                    if ($result) {
                        $informationObject = QubitInformationObject::getById($result->id);
                    } else {
                        // Try to find by slug
                        $query = 'SELECT object_id FROM slug WHERE slug = ?';
                        $statement = QubitFlatfileImport::sqlQuery($query, [$identifier]);
                        $result = $statement->fetch(PDO::FETCH_OBJ);

                        if ($result) {
                            $informationObject = QubitInformationObject::getById($result->object_id);
                        }
                    }
                }

                if (!$informationObject) {
                    $error = sprintf('Information object with identifier "%s" not found... skipping row.',
                        $identifier);
                    echo $self->logError($error);

                    return;
                }

                // Determine relation type ID
                $relationTypeId = null;

                // First check if a specific relation type ID was provided
                if (!empty($self->columnValue('relationTypeId'))) {
                    $relationTypeId = $self->columnValue('relationTypeId');
                } elseif (!empty($self->columnValue('relationType'))) {
                    // Look up the term in the event types taxonomy
                    $culture = $self->columnValue('culture') ?: 'en';
                    $relationTypeId = self::arraySearchCaseInsensitive(
                        $self->columnValue('relationType'),
                        $self->status['eventTypes'][$culture]
                    );

                    if (!$relationTypeId) {
                        // If not found, create a new event type term
                        $term = new QubitTerm();
                        $term->parentId = QubitTerm::ROOT_ID;
                        $term->taxonomyId = QubitTaxonomy::EVENT_TYPE_ID;
                        $term->setName($self->columnValue('relationType'), ['culture' => $culture]);
                        $term->sourceCulture = $culture;
                        $term->save();

                        $relationTypeId = $term->id;
                        $self->status['eventTypes'][$culture][$term->id] = $self->columnValue('relationType');

                        $self->log(sprintf('Created new event type "%s"', $self->columnValue('relationType')));
                    }
                }

                if (!$relationTypeId) {
                    $error = 'No relation type specified... skipping row.';
                    echo $self->logError($error);

                    return;
                }

                // Import the relation
                $this->importRow($informationObject->id, $actor->id, $relationTypeId);
            },
        ]);

        // Allow search indexing to be enabled via a CLI option
        $this->import->searchIndexingDisabled = !$indexDuringImport;

        $this->import->csv($fh);

        // Update search index if needed
        if ($indexDuringImport) {
            $this->log('Updating search index...');

            // Update affected information objects
            foreach ($this->import->status['ioIds'] as $ioId) {
                $io = QubitInformationObject::getById($ioId);
                if ($io) {
                    QubitSearch::getInstance()->update($io);
                }
                Qubit::clearClassCaches();
            }

            // Update affected actors
            foreach ($this->import->status['actorIds'] as $actorId) {
                $actor = QubitActor::getById($actorId);
                if ($actor) {
                    QubitSearch::getInstance()->update($actor);
                }
                Qubit::clearClassCaches();
            }
        }
    }

    private function importRow($informationObjectId, $actorId, $relationTypeId)
    {
        $updateMode = !empty($this->import->status['updateMode'])
            ? $this->import->status['updateMode']
            : false;

        if ($updateMode) {
            if ('delete-and-replace' == $updateMode) {
                // Delete existing relations of this type between the information object and actor
                $relations = $this->getRelations($informationObjectId, $actorId, $relationTypeId);

                foreach ($relations as $relationId) {
                    // Don't delete relations that have been added during this import
                    if (!in_array($relationId, $this->newlyAdded)) {
                        $relation = QubitRelation::getById($relationId);
                        if ($relation) {
                            $relation->delete();
                        }
                    }
                }
            } elseif ($relationId = $this->getRelationByType($informationObjectId, $actorId, $relationTypeId)) {
                // Relation already exists in match-and-update mode
                $this->import->log('Relation already exists, skipping...');

                return;
            }
        } elseif ($this->getRelationByType($informationObjectId, $actorId, $relationTypeId)) {
            // If not updating, but relation already exists, skip
            echo $this->import->logError('Skipping row as relationship already exists');

            return;
        }

        // Create the relation
        $relation = $this->import->createRelation($informationObjectId, $actorId, $relationTypeId);

        if ($relation && 'delete-and-replace' == $updateMode) {
            $this->newlyAdded[] = $relation->id;
        }

        // Track updated objects for indexing
        $this->addUpdatedIds($informationObjectId, $actorId);
    }

    /**
     * Use SQL to fetch the ID of a relation, of a certain type, if it exists.
     *
     * @param int $informationObjectId ID of information object
     * @param int $actorId             ID of actor
     * @param int $relationTypeId      ID of relation type
     *
     * @return mixed integer ID of the relation or boolean false if there's no result
     */
    private function getRelationByType($informationObjectId, $actorId, $relationTypeId)
    {
        $sql = 'SELECT id FROM relation
            WHERE subject_id = :subject_id
            AND object_id = :object_id
            AND type_id = :type_id
            LIMIT 1';

        $params = [
            ':subject_id' => $informationObjectId,
            ':object_id' => $actorId,
            ':type_id' => $relationTypeId,
        ];

        return QubitPdo::fetchColumn($sql, $params);
    }

    /**
     * Use SQL to fetch relations of a specific type.
     *
     * @param int $informationObjectId ID of information object
     * @param int $actorId             ID of actor
     * @param int $relationTypeId      ID of relation type
     *
     * @return array array of relation IDs
     */
    private function getRelations($informationObjectId, $actorId, $relationTypeId)
    {
        $sql = 'SELECT id FROM relation
            WHERE subject_id = :subject_id
            AND object_id = :object_id
            AND type_id = :type_id';

        $results = QubitPdo::fetchAll(
            $sql,
            [
                ':subject_id' => $informationObjectId,
                ':object_id' => $actorId,
                ':type_id' => $relationTypeId,
            ],
            ['fetchMode' => PDO::FETCH_ASSOC]
        );

        return array_column($results, 'id');
    }

    /**
     * Track IDs of updated objects for search indexing.
     *
     * @param int $informationObjectId ID of information object
     * @param int $actorId             ID of actor
     */
    private function addUpdatedIds($informationObjectId, $actorId)
    {
        // Keep track of information object IDs
        if (!in_array($informationObjectId, $this->import->status['ioIds'])) {
            $this->import->status['ioIds'][] = $informationObjectId;
        }

        // Keep track of actor IDs
        if (!in_array($actorId, $this->import->status['actorIds'])) {
            $this->import->status['actorIds'][] = $actorId;
        }
    }
}
