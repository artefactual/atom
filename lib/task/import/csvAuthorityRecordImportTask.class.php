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
 * Import csv authoriy record data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvAuthorityRecordImportTask extends csvImportBaseTask
{
  protected $namespace           = 'csv';
  protected $name                = 'authority-import';
  protected $briefDescription    = 'Import csv authority record data';
  protected $detailedDescription = <<<EOF
Import CSV data
EOF;

  /**
   * @see csvImportBaseTask
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
        'index',
        null,
        sfCommandOption::PARAMETER_NONE,
        "Index for search during import."
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
        "When importing records with --update, skip creating new records if no existing records match."
      ),
      new sfCommandOption(
        'skip-derivatives',
        null,
        sfCommandOption::PARAMETER_NONE,
        "Skip creation of digital object derivatives."
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
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $this->validateOptions($options);

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    $sourceName = ($options['source-name'])
      ? $options['source-name']
      : basename($arguments['filename']);

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Load taxonomies into variables to avoid use of magic numbers
    $termData = QubitFlatfileImport::loadTermsFromTaxonomies(array(
      QubitTaxonomy::NOTE_TYPE_ID                => 'noteTypes',
      QubitTaxonomy::ACTOR_ENTITY_TYPE_ID        => 'actorTypes',
      QubitTaxonomy::ACTOR_RELATION_TYPE_ID      => 'actorRelationTypes',
      QubitTaxonomy::DESCRIPTION_STATUS_ID       => 'descriptionStatusTypes',
      QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID => 'detailLevelTypes'
    ));

    // Define import
    $import = new QubitFlatfileImport(array(
      // Pass context
      'context' => sfContext::createInstance($this->configuration),

      // What type of object are we importing?
      'className' => 'QubitActor',

      // How many rows should import until we display an import status update?
      'rowsUntilProgressDisplay' => $options['rows-until-update'],

      /* Where to log errors to */
      'errorLog' => $options['error-log'],

      // The status array is a place to put data that should be accessible
      // from closure logic using the getStatus method
      'status' => array(
        'sourceName'             => $sourceName,
        'actorTypes'             => $termData['actorTypes'],
        'descriptionStatusTypes' => $termData['descriptionStatusTypes'],
        'detailLevelTypes'       => $termData['detailLevelTypes'],
        'aliases'                => $aliases,
        'actorNames'             => array()
      ),

      // Import columns that map directory to QubitActor properties
      'standardColumns' => array(
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
        'sources'
      ),

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

      'columnMap' => array(
        'institutionIdentifier' => 'institutionResponsibleIdentifier'
      ),

      // Import columns that can be added as QubitNote objects
      'noteMap' => array(
        'maintenanceNotes' => array(
          'typeId' => array_search('Maintenance note', $termData['noteTypes']['en'])
        )
      ),

      // These values get stored to the rowStatusVars array
      'variableColumns' => array(
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
        'digitalObjectChecksum'
      ),

      // These values get exploded and stored to the rowStatusVars array
      'arrayColumns' => array(
        'parallelFormsOfName' => '|',
        'standardizedFormsOfName' => '|',
        'otherFormsOfName' => '|',
        'script' => '|'
      ),

      'updatePreparationLogic' => function(&$self)
      {
        $this->deleteDigitalObjectIfUpdatingAndNotKeeping($self);
      },

      // Import logic to execute before saving actor
      'preSaveLogic' => function(&$self)
      {
        if ($self->object)
        {
          // Warn if identifier's already been used
          if (!empty($identifier = $self->columnValue('descriptionIdentifier'))
              && QubitValidatorActorDescriptionIdentifier::identifierUsedByAnotherActor($identifier, $self->object))
          {
            print $self->logError(sprintf('Authority record identifier "%s" not unique.', $identifier));
          }

          if (
            isset($self->rowStatusVars['typeOfEntity'])
            && $self->rowStatusVars['typeOfEntity']
          )
          {
            $self->object->entityTypeId = $self->translateNameToTermId(
              'type of entity',
              $self->rowStatusVars['typeOfEntity'],
              array(),
              $self->status['actorTypes'][$self->columnValue('culture')]
            );
          }

          if (
            isset($self->rowStatusVars['status'])
            && $self->rowStatusVars['status']
          )
          {
            $self->object->descriptionStatusId = $self->translateNameToTermId(
              'status',
              $self->rowStatusVars['status'],
              array(),
              $self->status['descriptionStatusTypes'][$self->columnValue('culture')]
            );
          }

          if (
            isset($self->rowStatusVars['levelOfDetail'])
            && $self->rowStatusVars['levelOfDetail']
          )
          {
            $self->object->descriptionDetailId = $self->translateNameToTermId(
              'level of detail',
              $self->rowStatusVars['levelOfDetail'],
              array(),
              $self->status['detailLevelTypes'][$self->columnValue('culture')]
            );
          }
        }
      },

      // Import logic to execute after saving actor
      'postSaveLogic' => function(&$self)
      {
        if ($self->object)
        {
          // Note actor name for optional relationship import phase
          $self->status['actorNames'][$self->object->id] = $self->object->authorizedFormOfName;

          csvImportBaseTask::importAlternateFormsOfName($self);

          // Add contact information, if applicable
          $contactVariables = array(
            'email',
            'notes',
            'countryCode',
            'fax',
            'telephone',
            'postalCode',
            'streetAddress',
            'region'
          );

          $hasContactInfo = false;
          foreach(array_keys($self->rowStatusVars) as $name)
          {
            if (in_array($name, $contactVariables))
            {
              $hasContactInfo = true;
            }
          }

          if ($hasContactInfo)
          {
            // Add contact information
            $info = new QubitContactInformation();
            $info->actorId = $self->object->id;

            foreach($contactVariables as $property)
            {
              if ($self->rowStatusVars[$property])
              {
                $info->$property = $self->rowStatusVars[$property];
              }
            }

            $info->save();
          }

          // Add placeAccessPoints
          if (!empty($self->rowStatusVars['placeAccessPoints']))
          {
            $places = explode('|', $self->rowStatusVars['placeAccessPoints']);
            for ($i = 0; $i < count($places); $i++)
            {
              if (empty($places[$i]))
              {
                continue;
              }

              if (null !== $relation = QubitActor::setTermRelationByName($places[$i], $options = array('taxonomyId' => QubitTaxonomy::PLACE_ID, 'culture' => $self->columnValue('culture'))))
              {
                $relation->object = $self->object;
                $relation->save();
              }
            }
          }

          // Add subjectAccessPoints
          if (!empty($self->rowStatusVars['subjectAccessPoints']))
          {
            $subjects = explode('|', $self->rowStatusVars['subjectAccessPoints']);
            for ($i = 0; $i < count($subjects); $i++)
            {
              if (empty($subjects[$i]))
              {
                continue;
              }

              if (null !== $relation = QubitActor::setTermRelationByName($subjects[$i], $options = array('taxonomyId' => QubitTaxonomy::SUBJECT_ID, 'culture' => $self->columnValue('culture'))))
              {
                $relation->object = $self->object;
                $relation->save();
              }
            }
          }

          // Add occupations
          if (!empty($self->rowStatusVars['actorOccupations']))
          {
            $occupations = explode('|', $self->rowStatusVars['actorOccupations']);
            $occupationNotes = array();

            if (!empty($self->rowStatusVars['actorOccupationNotes']))
            {
              $occupationNotes = explode('|', $self->rowStatusVars['actorOccupationNotes']);
            }

            for ($i = 0; $i < count($occupations); $i++)
            {
              if (empty($occupations[$i]))
              {
                continue;
              }

              if (null !== $relation = QubitActor::setTermRelationByName($occupations[$i], $options = array('taxonomyId' => QubitTaxonomy::ACTOR_OCCUPATION_ID, 'culture' => $self->columnValue('culture'))))
              {
                $relation->object = $self->object;
                $relation->save();

                if (!empty($occupationNotes[$i]) && $occupationNotes[$i] !== 'NULL')
                {
                  $note = new QubitNote;
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
          if (!$self->searchIndexingDisabled)
          {
            QubitSearch::getInstance()->update($self->object);
          }
        }
      }
    ));

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = ($options['index']) ? false : true;

    // Set update, limit and skip options
    $import->setUpdateOptions($options);

    $import->csv($fh, $skipRows);
  }
}
