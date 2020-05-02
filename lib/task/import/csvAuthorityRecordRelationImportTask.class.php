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
class csvAuthorityRecordRelationImportTask extends arBaseTask
{
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

    $this->log('Importing relations...');

    $sourceName = $options['source-name'] ?: basename($arguments['filename']);
    $this->import($arguments['filename'], $sourceName, $options['index']);

    $this->log('Done.');
  }

  private function import($filepath, $sourceName, $indexDuringImport = false)
  {
    if (false === $fh = fopen($filepath, 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    // Load taxonomies into variables to avoid use of magic numbers
    $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
      QubitTaxonomy::ACTOR_RELATION_TYPE_ID => 'actorRelationTypes',
    ]);

    $import = new QubitFlatfileImport([
      'context' => sfContext::createInstance($this->configuration),

      'status' => [
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
            // Add relationship if it doesn't yet exist
            if (!$this->relationshipExists($sourceActor->id, $targetActor->id, $relationTypeId))
            {
              $relation = new QubitRelation;
              $relation->objectId  = $sourceActor->id;
              $relation->subjectId = $targetActor->id;
              $relation->typeId    = $relationTypeId;

              // Set relationship properties from column values
              foreach (['date', 'startDate', 'endDate', 'description'] as $property)
              {
                if (!empty($self->columnValue($property)))
                {
                  $relation->$property = $self->columnValue($property);
                }
              }

              $relation->save();

              // Add keymap entry
              if (!empty($self->columnValue('legacyId')))
              {
                $self->createKeymapEntry($self->getStatus('sourceName'), $self->columnValue('legacyId'), $relation);
              }
            }
          }
        }
      }
    ]);

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = !$indexDuringImport;

    $import->csv($fh);
  }

  private function relationshipExists($sourceActorId, $targetActorId, $relationTypeId)
  {
    $sql = "SELECT id FROM relation \r
      WHERE subject_id = :subject_id \r
      AND object_id = :object_id \r
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
}
