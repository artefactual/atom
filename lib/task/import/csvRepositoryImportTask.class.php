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
        'authorizedFormOfName',
        'identifier',
        'openingTimes',
        'geoculturalContext',
        'holdings',
        'findingAids',
        'internalStructures',
        'uploadLimit'
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
        'phone',
        'email',
        'fax',
        'website',
        'notes'
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
        // Add contact information
        $info = new QubitContactInformation();
        $info->actorId = $self->object->id;

        $info->contactPerson = $self->rowStatusVars['contactPerson'];
        $info->streetAddress = $self->rowStatusVars['streetAddress'];
        $info->telephone = $self->rowStatusVars['phone'];
        $info->email = $self->rowStatusVars['email'];
        $info->fax = $self->rowStatusVars['fax'];
        $info->website = $self->rowStatusVars['website'];

        $info->save();

        // Add note
        $note = new QubitNote();
        $note->content = $self->rowStatusVars['notes'];
        $note->objectId = $self->object->id;

        $note->save();
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
