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
 * Import authority record relations using CSV
 *
 * @package    AccessToMemory
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvAuthorityRecordRelationImportTask extends csvImportBaseTask
{
  private $import;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments([
      new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'Output filename')
    ]);

    $this->addOptions([
      new sfCommandOption('application', null,
        sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null,
        sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null,
        sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),

      new sfCommandOption('source-name', null,
        sfCommandOption::PARAMETER_OPTIONAL, 'Source name to use when inserting keymap entries.'),
      new sfCommandOption('index', null,
        sfCommandOption::PARAMETER_NONE, 'Index for search during import.'),
      new sfCommandOption('update', null,
        sfCommandOption::PARAMETER_REQUIRED, 'Attempt to update if relation has already been imported. Valid option values are "match-and-update" & "delete-and-replace".'
      ),
    ]);

    $this->namespace = 'csv';
    $this->name = 'authority-relation-import';
    $this->briefDescription = 'Import authority record relations using CSV data.';
    $this->detailedDescription = <<<EOF
      Import authority record relations using CSV data
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = [], $options = [])
  {
    parent::execute($arguments, $options);

    $this->validateUpdateOptions($options);

    $this->log('Importing relations...');

    $sourceName = $options['source-name'] ?: basename($arguments['filename']);
    $this->import($arguments['filename'], $sourceName, $options['index'], $options['update']);

    $this->log('Done.');
  }

  private function import($filepath, $sourceName, $indexDuringImport = false, $updateMode = false)
  {
    if (false === $fh = fopen($filepath, 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    // Load taxonomies into variables to avoid use of magic numbers
    $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
      QubitTaxonomy::ACTOR_RELATION_TYPE_ID => 'actorRelationTypes',
    ]);

    $this->import = new QubitFlatfileImport([
      'context' => sfContext::createInstance($this->configuration),

      'status' => [
        'updateMode'         => $updateMode,
        'sourceName'         => $sourceName,
        'actorRelationTypes' => $termData['actorRelationTypes'],
        'actorIds'           => [],
      ],

      'variableColumns' => [
        'legacyId',
        'objectAuthorizedFormOfName',
        'subjectAuthorizedFormOfName',
        'relationType',
        'description',
        'date',
        'startDate',
        'endDate'
      ],

      'saveLogic' => function($self)
      {
        // Figure out ID of the two actors
        $sourceActor = QubitActor::getByAuthorizedFormOfName(
          $self->columnValue('objectAuthorizedFormOfName'), ['culture' => $self->columnValue('culture')]);
        $targetActor = QubitActor::getByAuthorizedFormOfName(
          $self->columnValue('subjectAuthorizedFormOfName'), ['culture' => $self->columnValue('culture')]);

        // Determine type ID of relationship type
        $relationTypeId = array_search_case_insensitive(
          $self->columnValue('relationType'),
          $self->status['actorRelationTypes'][$self->columnValue('culture')]
        );

        // Skip invalid relationships
        if (!$relationTypeId)
        {
          $error = sprintf('Unknown relationship type "%s"... skipping row.', $self->columnValue('relationType'));
          print $self->logError($error);
        }
        else
        {
          // Attempt to add relationship
          if (empty($sourceActor) || empty($targetActor))
          {
            // Warn if actor is missing
            $badActor = (empty($sourceActor))
              ? $self->columnValue('objectAuthorizedFormOfName')
              : $self->columnValue('subjectAuthorizedFormOfName');

            $error = sprintf('Actor "%s" does not exist... skipping row.', $badActor);
            print $self->logError($error);
          }
          else
          {
            if ($self->status['updateMode'] == 'match-and-update')
            {
              // If a relationship between the two, of any type, exists then update it rather than add it
              if ($relation = $this->getRelation($sourceActor->id, $targetActor->id))
              {
                $this->updateRelation($relation->id, $sourceActor, $targetActor, $relationTypeId);
              }
              else
              {
                $this->addRelation($sourceActor, $targetActor, $relationTypeId);
              }
            }
            else if ($self->status['updateMode'] == 'delete-and-replace')
            {
              // Delete existing relationships between the two actors
              foreach ($this->getRelations($sourceActor->id, $targetActor->id) as $row)
              {
                $relation = QubitRelation::getById($row->id);
                $relation->delete();
              }

              // Add relationship
              $this->addRelation($sourceActor, $targetActor, $relationTypeId);
            }
            else
            {
              // Add relationship if one of the same type doesn't yet exist
              if (!$this->relationshipAndTypeExists($sourceActor->id, $targetActor->id, $relationTypeId))
              {
                $this->addRelation($sourceActor, $targetActor, $relationTypeId);
              }
            }
          }
        }
      }
    ]);

    // Allow search indexing to be enabled via a CLI option
    $this->import->searchIndexingDisabled = !$indexDuringImport;

    $this->import->csv($fh);

    // Update actor relationships in Elasticsearch
    if ($indexDuringImport)
    {
      $this->log('Updating Elasticsearch actor relation data...');

      foreach ($this->import->status['actorIds'] as $actorId)
      {
        $actor = QubitActor::getById($actorId);
        arUpdateEsActorRelationsJob::updateActorRelationships($actor);
        Qubit::clearClassCaches();
      }
    }
  }

  private function getRelation($sourceActorId, $targetActorId)
  {
    return $this->getRelations($sourceActorId, $targetActorId, 'fetchOne');
  }

  private function getRelations($sourceActorId, $targetActorId, $fetchMethod = 'fetchAll')
  {
    $sql = "SELECT id FROM relation
      WHERE subject_id = :subject_id
      AND object_id = :object_id";

    $params = [
      ':subject_id' => $sourceActorId,
      ':object_id' => $targetActorId
    ];

    $paramsVariant = [
      ':subject_id' => $targetActorId,
      ':object_id' => $sourceActorId
    ];

    if ($results = QubitPdo::$fetchMethod($sql, $params))
    {
      return $results;
    }

    return QubitPdo::$fetchMethod($sql, $paramsVariant);
  }

  private function relationshipAndTypeExists($sourceActorId, $targetActorId, $relationTypeId)
  {
    $sql = "SELECT id FROM relation
      WHERE subject_id = :subject_id
      AND object_id = :object_id
      AND type_id = :type_id";

    $params = [
      ':subject_id' => $sourceActorId,
      ':object_id' => $targetActorId,
      ':type_id' => $relationTypeId
    ];

    $paramsVariant = [
      ':subject_id' => $targetActorId,
      ':object_id' => $sourceActorId,
      ':type_id' => $relationTypeId
    ];

    return QubitPdo::fetchOne($sql, $params) !== false
      || QubitPdo::fetchOne($sql, $paramsVariant) !== false;
  }

  private function addRelation($sourceActor, $targetActor, $relationTypeId)
  {
    $relation = new QubitRelation;
    $this->setRelationFields($relation, $sourceActor->id, $targetActor->id, $relationTypeId);
    $relation->save();

    $this->noteActorIds($sourceActor->id, $targetActor->id);

    // Add keymap entry
    if (!empty($this->import->columnValue('legacyId')))
    {
      $this->import->createKeymapEntry($this->import->getStatus('sourceName'), $this->import->columnValue('legacyId'), $relation);
    }
  }

  private function updateRelation($relationId, $sourceActor, $targetActor, $relationTypeId)
  {
    $relation = QubitRelation::getById($relationId);
    $this->setRelationFields($relation, $sourceActor->id, $targetActor->id, $relationTypeId);
    $relation->save();

    $this->noteActorIds($sourceActor->id, $targetActor->id);
  }

  private function setRelationFields(&$relation, $sourceActorId, $targetActorId, $relationTypeId)
  {
    $relation->objectId  = $sourceActorId;
    $relation->subjectId = $targetActorId;
    $relation->typeId    = $relationTypeId;

    // Set relationship properties from column values
    foreach (['date', 'startDate', 'endDate', 'description'] as $property)
    {
      if (!empty($this->import->columnValue($property)))
      {
        $relation->$property = $this->import->columnValue($property);
      }
    }
  }

  private function noteActorIds($sourceActorId, $targetActorId)
  {
    // Keep track of actor IDs so actor relationships in Elasticsearch can be updated
    if (!in_array($sourceActorId, $this->import->status['actorIds']))
    {
      $this->import->status['actorIds'][] = $sourceActorId;
    }

    if (!in_array($targetActorId, $this->import->status['actorIds']))
    {
      $this->import->status['actorIds'][] = $targetActorId;
    }
  }
}
