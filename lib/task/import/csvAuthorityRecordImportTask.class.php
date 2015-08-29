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
        'alias-file',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        'CSV file containing aliases.'
      ),
      new sfCommandOption(
        'relation-file',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        'CSV file containing relationships.'
      ),
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

    $sourceName = ($options['source-name'])
      ? $options['source-name']
      : basename($arguments['filename']);

    // If alias file option set, load aliases from CSV
    $aliases = array();

    if ($options['alias-file'])
    {
      // Open alias CSV file
      if (false === $fh = fopen($options['alias-file'], 'rb'))
      {
        throw new sfException('You must specify a valid filename');
      }
      else
      {
        print "Reading aliases\n";

        // Import name aliases, if specified
        $import = new QubitFlatfileImport(array(
          // Pass context
          'context' => sfContext::createInstance($this->configuration),

          'status' => array(
            'aliases' => array()
          ),
          'variableColumns' => array(
            'parentAuthorizedFormOfName',
            'alternateForm',
            'formType'
          ),
          'saveLogic' => function(&$self)
          {
            if (trim($self->rowStatusVars['alternateForm']))
            {
              $aliases = $self->getStatus('aliases');

              $aliases[] = array(
                'parentAuthorizedFormOfName' => $self->rowStatusVars['parentAuthorizedFormOfName'],
                'alternateForm'              => $self->rowStatusVars['alternateForm'],
                'formType'                   => $self->rowStatusVars['formType']
              );

              $self->setStatus('aliases', $aliases);
            }
          }
        ));
      }
      $import->csv($fh);
      $aliases = $import->getStatus('aliases');
    }

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
          'typeId' => array_search('Maintenance note', $termData['noteTypes'])
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
        'region'
      ),

      // Import logic to execute before saving actor
      'preSaveLogic' => function(&$self)
      {
        if ($self->object)
        {
          if (
            isset($self->rowStatusVars['typeOfEntity'])
            && $self->rowStatusVars['typeOfEntity']
          )
          {
            $self->object->entityTypeId = $self->translateNameToTermId(
              'type of entity',
              $self->rowStatusVars['typeOfEntity'],
              array(),
              $self->getStatus('actorTypes')
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
              $self->getStatus('descriptionStatusTypes')
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
              $self->getStatus('detailLevelTypes')
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

          // Cycle through aliases looking for other names
          $otherNames = array();

          $aliases = $self->getStatus('aliases');

          foreach($aliases as $alias)
          {
            if ($self->object->authorizedFormOfName == $alias['parentAuthorizedFormOfName'])
            {
              $typeIds = array(
                'parallel'     => QubitTerm::PARALLEL_FORM_OF_NAME_ID,
                'standardized' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID,
                'other'        => QubitTerm::OTHER_FORM_OF_NAME_ID
              );

              $normalizedType = strtolower($alias['formType']);

              if ($typeIds[$normalizedType])
              {
                $typeId = $typeIds[$normalizedType];
              } else {
                throw new sfException('Invalid alias type"'. $alias['formType'] .'".');
              }

              // Add other name
              $otherName = new QubitOtherName;
              $otherName->objectId = $self->object->id;
              $otherName->name     = $alias['alternateForm'];
              $otherName->typeId   = $typeId;
              $otherName->save();
            }
          }

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
        }
      }
    ));

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = ($options['index']) ? false : true;

    $import->csv($fh, $skipRows);
    $actorNames = $import->getStatus('actorNames');

    // Optional relationship import
    if ($options['relation-file'])
    {
      // Open relationship CSV file
      if (false === $fh = fopen($options['relation-file'], 'rb'))
      {
        throw new sfException('You must specify a valid filename');
      }
      else
      {
        print "Importing relationships\n";

        $import = new QubitFlatfileImport(array(
          // Pass context
          'context' => sfContext::createInstance($this->configuration),

          'status' => array(
            'actorNames'         => $actorNames,
            'actorRelationTypes' => $termData['actorRelationTypes']
          ),

          'variableColumns' => array(
            'sourceAuthorizedFormOfName',
            'targetAuthorizedFormOfName',
            'category',
            'description',
            'date',
            'startDate',
            'endDate'
          ),

          'saveLogic' => function(&$self)
          {
            // Figure out ID of the two actors
            $sourceActorId = array_search($self->rowStatusVars['sourceAuthorizedFormOfName'], $self->status['actorNames']);
            $targetActorId = array_search($self->rowStatusVars['targetAuthorizedFormOfName'], $self->status['actorNames']);

            // Determine type ID of relationship type
            $relationTypeId = array_search(
              $self->rowStatusVars['category'],
              $self->status['actorRelationTypes']
            );

            if (!$relationTypeId)
            {
              throw new sfException('Unknown relationship type :'. $self->rowStatusVars['category']);
            }
            else
            {
              // Determine type ID of relationship type
              // add relationship, with date/startdate/enddate/description
              if (!$sourceActorId || !$targetActorId)
              {
                $badActor = (!$sourceActorId)
                  ? $self->rowStatusVars['sourceAuthorizedFormOfName']
                  : $self->rowStatusVars['targetAuthorizedFormOfName'];

                $error = 'Actor "'. $badActor .'" does not exist';
                print $self->logError($error);
              }
              else
              {
                $relation = new QubitRelation;
                $relation->subjectId = $sourceActorId;
                $relation->objectId  = $targetActorId;
                $relation->typeId    = $relationTypeId;

                if ($self->rowStatusVars['date'])
                {
                  $relation->date = $self->rowStatusVars['date'];
                }
                if ($self->rowStatusVars['startDate'])
                {
                  $relation->startDate = $self->rowStatusVars['startDate'];
                }
                if ($self->rowStatusVars['endDate'])
                {
                  $relation->endDate = $self->rowStatusVars['endDate'];
                }
                if ($self->rowStatusVars['description'])
                {
                  $relation->description = $self->rowStatusVars['description'];
                }

                $relation->save();
              }
            }
          }
        ));

        $import->csv($fh);
      }
    }
  }
}
