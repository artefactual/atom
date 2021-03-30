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
 * Import authority record relations using CSV.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvAuthorityRecordRelationImportTask extends csvImportBaseTask
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

        $this->log('Importing relations...');

        $this->import($arguments['filename'], $options['index'], $options['update']);

        $this->log('Done.');
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'Output filename'),
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
        $this->name = 'authority-relation-import';
        $this->briefDescription = 'Import authority record relations using CSV data.';
        $this->detailedDescription = <<<'EOF'
Import authority record relations using CSV data
EOF;
    }

    private function import($filepath, $indexDuringImport = false, $updateMode = false)
    {
        if (false === $fh = fopen($filepath, 'rb')) {
            throw new sfException('You must specify a valid filename');
        }

        // Load taxonomies into variables to avoid use of magic numbers
        $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
            QubitTaxonomy::ACTOR_RELATION_TYPE_ID => 'actorRelationTypes',
        ]);

        $this->import = new QubitFlatfileImport([
            'context' => sfContext::createInstance($this->configuration),

            'status' => [
                'updateMode' => $updateMode,
                'actorRelationTypes' => $termData['actorRelationTypes'],
                'actorIds' => [],
            ],

            'variableColumns' => [
                'objectAuthorizedFormOfName',
                'subjectAuthorizedFormOfName',
                'relationType',
                'description',
                'date',
                'startDate',
                'endDate',
            ],

            'saveLogic' => function ($self) {
                // Figure out ID of the two actors
                $sourceActor = QubitActor::getByAuthorizedFormOfName(
                    $self->columnValue('objectAuthorizedFormOfName'),
                    ['culture' => $self->columnValue('culture')]
                );
                $targetActor = QubitActor::getByAuthorizedFormOfName(
                    $self->columnValue('subjectAuthorizedFormOfName'),
                    ['culture' => $self->columnValue('culture')]
                );

                // Determine type ID of relationship type
                $relationTypeId = self::arraySearchCaseInsensitive(
                    $self->columnValue('relationType'),
                    $self->status['actorRelationTypes'][$self->columnValue('culture')]
                );

                // Skip invalid relationships
                if (!$relationTypeId) {
                    $error = sprintf('Unknown relationship type "%s"... skipping row.', $self->columnValue('relationType'));
                    echo $self->logError($error);
                } else {
                    // Attempt to add relationship
                    if (empty($sourceActor) || empty($targetActor)) {
                        // Warn if actor is missing
                        $badActor = (empty($sourceActor))
                            ? $self->columnValue('objectAuthorizedFormOfName')
                            : $self->columnValue('subjectAuthorizedFormOfName');

                        $error = sprintf('Actor "%s" does not exist... skipping row.', $badActor);
                        echo $self->logError($error);
                    } else {
                        $this->importRow($sourceActor->id, $targetActor->id, $relationTypeId);
                    }
                }
            },
        ]);

        // Allow search indexing to be enabled via a CLI option
        $this->import->searchIndexingDisabled = !$indexDuringImport;

        $this->import->csv($fh);

        // Update actor relationships in Elasticsearch
        if ($indexDuringImport) {
            $this->log('Updating Elasticsearch actor relation data...');

            foreach ($this->import->status['actorIds'] as $actorId) {
                $actor = QubitActor::getById($actorId);
                arUpdateEsActorRelationsJob::updateActorRelationships($actor);
                Qubit::clearClassCaches();
            }
        }
    }

    private function importRow($sourceActorId, $targetActorId, $relationTypeId)
    {
        $updateMode = !empty($this->import->status['updateMode'])
            ? $this->import->status['updateMode']
            : false;

        if ($updateMode) {
            if ('delete-and-replace' == $updateMode) {
                // Handle delete-and-replace updating by deleting all existing relations between source
                // and target then adding new relation
                $relations = $this->getRelations($targetActorId, $sourceActorId);
                $relationsAlternate = $this->getRelations($sourceActorId, $targetActorId);

                foreach (array_unique(array_merge($relations, $relationsAlternate)) as $relationId) {
                    // Don't delete relations that have been added during this import
                    if (!in_array($relationId, $this->newlyAdded)) {
                        $relation = QubitRelation::getById($relationId);
                        $relation->delete();
                    }
                }
            } elseif ($relationId = $this->getRelationByType($sourceActorId, $targetActorId, $relationTypeId)) {
                // Handle match-and-update when a relation already exists
                $this->updateRelation($relationId, $sourceActorId, $targetActorId, $relationTypeId);

                return;
            }
        } elseif (!empty($this->getRelationByType($sourceActorId, $targetActorId, $relationTypeId))) {
            // If not updating, but relation already exists, then don't create new relation
            echo $this->import->logError('Skipping row as relationship already exists');

            return;
        }

        $relation = $this->addRelation($sourceActorId, $targetActorId, $relationTypeId);

        if ('delete-and-replace' == $updateMode) {
            $this->newlyAdded[] = $relation->id;
        }
    }

    /**
     * Use SQL to fetch the ID of a relation, of a certain type, if it exists.
     *
     * @param int $sourceActorId  ID of an actor that possibly relates to others
     * @param int $targetActorId  ID of an actor that possibly relates to others
     * @param int $relationTypeId ID of relation type
     *
     * @return mixed integer ID of the relation or boolean false if there's no result
     */
    private function getRelationByType($sourceActorId, $targetActorId, $relationTypeId)
    {
        $sql = 'SELECT id FROM relation
            WHERE subject_id = :subject_id
            AND object_id = :object_id
            AND type_id = :type_id
            LIMIT 1';

        $params = [
            ':subject_id' => $sourceActorId,
            ':object_id' => $targetActorId,
            ':type_id' => $relationTypeId,
        ];

        $paramsVariant = [
            ':subject_id' => $targetActorId,
            ':object_id' => $sourceActorId,
            ':type_id' => $relationTypeId,
        ];

        if ($relationId = QubitPdo::fetchColumn($sql, $params)) {
            return $relationId;
        }

        return QubitPdo::fetchColumn($sql, $paramsVariant);
    }

    /**
     * Use SQL to fetch relations.
     *
     * @param int $sourceActorId ID of an actor that possibly relates to others
     * @param int $targetActorId ID of an actor that possibly relates to others
     *
     * @return array array of relation IDs
     */
    private function getRelations($sourceActorId, $targetActorId)
    {
        $sql = 'SELECT id FROM relation
            WHERE subject_id = :subject_id
            AND object_id = :object_id';

        $results = QubitPdo::fetchAll(
            $sql,
            [':subject_id' => $sourceActorId, ':object_id' => $targetActorId],
            ['fetchMode' => PDO::FETCH_ASSOC]
        );

        return array_column($results, 'id');
    }

    /**
     * Add relation between two actors.
     *
     * @param int $sourceActorId  ID of an actor
     * @param int $targetActorId  ID of an actor
     * @param int $relationTypeId ID of relation type
     */
    private function addRelation($sourceActorId, $targetActorId, $relationTypeId)
    {
        $relation = new QubitRelation();
        $this->setRelationFields($relation, $sourceActorId, $targetActorId, $relationTypeId);
        $relation->save();

        $this->addUpdatedActorIds([$sourceActorId, $targetActorId]);

        return $relation;
    }

    /**
     * Update relation using CSV row data.
     *
     * @param int $relationId     ID of relation to update
     * @param int $sourceActorId  ID of an actor
     * @param int $targetActorId  ID of an actor
     * @param int $relationTypeId ID of relation type
     */
    private function updateRelation($relationId, $sourceActorId, $targetActorId, $relationTypeId)
    {
        $relation = QubitRelation::getById($relationId);
        $this->setRelationFields($relation, $sourceActorId, $targetActorId, $relationTypeId);
        $relation->save();

        $this->addUpdatedActorIds([$sourceActorId, $targetActorId]);
    }

    /**
     * Set relation properties using CSV row data.
     *
     * @param QubitRelation $relation       Relation to update
     * @param int           $sourceActorId  ID of an actor
     * @param int           $targetActorId  ID of an actor
     * @param int           $relationTypeId ID of relation type
     */
    private function setRelationFields(&$relation, $sourceActorId, $targetActorId, $relationTypeId)
    {
        // Note that the column names don't map to the internal representation
        $relation->objectId = $targetActorId;
        $relation->subjectId = $sourceActorId;
        $relation->typeId = $relationTypeId;

        // Set relationship properties from column values
        foreach (['date', 'startDate', 'endDate', 'description'] as $property) {
            if (!empty($this->import->columnValue($property))) {
                $relation->{$property} = $this->import->columnValue($property);
            }
        }
    }

    /**
     * Note actor IDs whose relationship data has possibly changed.
     *
     * @param array $actorIds IDs of actors
     */
    private function addUpdatedActorIds($actorIds)
    {
        // Keep track of actor IDs so actor relationships in Elasticsearch can be updated
        foreach ($actorIds as $id) {
            if (!in_array($id, $this->import->status['actorIds'])) {
                $this->import->status['actorIds'][] = $id;
            }
        }
    }
}
