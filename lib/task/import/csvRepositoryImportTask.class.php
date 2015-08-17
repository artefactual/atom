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
 * Import csv repository data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <MikeG@Artefactual.com>
 * @author     Mike Cantelon <mike@artefactual.com>
 */

class csvRepositoryImportTask extends csvImportBaseTask
{
  protected $namespace           = 'csv';
  protected $name                = 'repository-import';
  protected $briefDescription    = 'Import csv repository data';
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
        'Attempt to update if repository has already been imported. Valid option values are "match-and-update" & "delete-and-replace".'
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
        'upload-limit',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        "Set the upload limit for repositories getting imported (default: disable uploads)")
      )
    );
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    $this->validateOptions($options);

    $this->logSection("Importing repository objects from CSV to AtoM");

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Define import
    $import = new QubitFlatfileImport(array(
      // Pass context
      'context' => sfContext::createInstance($this->configuration),

      // What type of object are we importing?
      'className' => 'QubitRepository',

      // How many rows should import until we display an import status update?
      'rowsUntilProgressDisplay' => $options['rows-until-update'],

      // Where to log errors to
      'errorLog' => $options['error-log'],

      // the status array is a place to put data that should be accessible
      // from closure logic using the getStatus method
      'status' => array(
        'options' => $options
      ),

      // Import columns that map directory to QubitRepository properties
      'standardColumns' => array(
        'identifier',
        'uploadLimit',
        'authorizedFormOfName',
        'geoculturalContext',
        'holdings',
        'findingAids',
        'openingTimes',
        'history',
        'mandates',
        'internalStructures',
        'collectingPolicies',
        'buildings',
        'accessConditions',
        'disabledAccess',
        'researchServices',
        'reproductionServices',
        'publicFacilities',
        'descIdentifier',
        'descInstitutionIdentifier',
        'descRules',
        'descRevisionHistory',
        'descSources',
        'culture'
      ),

      'columnMap' => array(
      ),

      // Import columns that can be added as QubitNote objects
      'noteMap' => array(
      ),

      // These values get stored to the rowStatusVars array
      'variableColumns' => array(
        'contactPerson',
        'streetAddress',
        'telephone',
        'email',
        'fax',
        'website',
        'notes',
        # TODO: Parse the below fields
        'legacyId',
        'parallelFormsOfName',
        'otherFormsOfName',
        'types',
        'languages',
        'scripts',
        'thematicAreas',
        'geographicSubregions',
        'descStatus',
        'descDetail'
      ),

      // Import logic to execute before saving QubitRepository
      'preSaveLogic' => function(&$self)
      {
        $opts = $self->getStatus('options');
        if (isset($opts['upload-limit']) && !isset($self->object->uploadLimit))
        {
          $self->object->uploadLimit = $opts['upload-limit'];
        }
      },

      // Import logic to execute after saving QubitRepository
      'postSaveLogic' => function(&$self)
      {
        // Check if any contact information data exists
        $addContactInfo = false;
        $contactInfoFields = array('contactPerson', 'streetAddress', 'telephone', 'email', 'fax', 'website');
        foreach ($contactInfoFields as $field)
        {
          if (!empty($self->rowStatusVars[$field]))
          {
            $addContactInfo = true;

            break;
          }
        }

        if ($addContactInfo)
        {
          // Try to get existing contanct information
          $criteria = new Criteria;
          $criteria->add(QubitContactInformation::ACTOR_ID, $self->object->id);
          $contactInfo = QubitContactInformation::getOne($criteria);

          if (!isset($contactInfo))
          {
            $contactInfo = new QubitContactInformation;
            $contactInfo->actorId = $self->object->id;
          }

          foreach ($contactInfoFields as $field)
          {
            // Don't overwrite/add blank fields
            if (!empty($self->rowStatusVars[$field]))
            {
              $contactInfo->$field = $self->rowStatusVars[$field];
            }
          }

          $contactInfo->save();
        }

        // Add note
        if (!empty($self->rowStatusVars['maintenanceNote']))
        {
          $criteria = new Criteria;
          $criteria->add(QubitNote::OBJECT_ID, $self->object->id);
          $criteria->add(QubitNote::TYPE_ID, QubitTerm::MAINTENANCE_NOTE_ID);
          $note = QubitNote::getOne($criteria);

          if (!isset($note))
          {
            $note = new QubitNote;
            $note->typeId = QubitTerm::MAINTENANCE_NOTE_ID;
            $note->objectId = $self->object->id;
          }

          $note->content = $self->rowStatusVars['maintenanceNote'];
          $note->save();
        }
      }
    ));

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = ($options['index']) ? false : true;

    // Set update, limit and skip options
    $import->setUpdateOptions($options);

    $import->csv($fh, $skipRows);
    $this->logSection("Imported repositories successfully!");
  }
}
