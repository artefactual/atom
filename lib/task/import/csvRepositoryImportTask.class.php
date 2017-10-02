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

    $this->logSection('repository-import', 'Importing repository objects from CSV to AtoM');

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    // Source name can be specified so, if importing from multiple
    // sources, you can accommodate legacy ID collisions in files
    // you import from different places
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
      QubitTaxonomy::DESCRIPTION_STATUS_ID       => 'descriptionStatusTypes',
      QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID => 'levelOfDetailTypes'
    ));

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
        'options'                => $options,
        'sourceName'             => $sourceName,
        'descriptionStatusTypes' => $termData['descriptionStatusTypes'],
        'levelOfDetailTypes'     => $termData['levelOfDetailTypes']
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
        'culture'
      ),

      'columnMap' => array(
        'descriptionIdentifier'      => 'descIdentifier',
        'institutionIdentifier'      => 'descInstitutionIdentifier',
        'descriptionRules'           => 'descRules',
        'descriptionRevisionHistory' => 'descRevisionHistory',
        'descriptionSources'         => 'descSources'
      ),

      // Import columns that map to taxonomy terms
      'termRelations' => array(
        'geographicSubregions' => QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID,
        'thematicAreas'        => QubitTaxonomy::THEMATIC_AREA_ID,
        'types'                => QubitTaxonomy::REPOSITORY_TYPE_ID
      ),

      // Import columns that can be added as QubitNote objects
      'noteMap' => array(
        'maintenanceNote' => array('typeId' => QubitTerm::MAINTENANCE_NOTE_ID)
      ),

      // Import columns with values that should be serialized/added as a language property
      'languageMap' => array(
        'language' => 'language'
      ),

      // Import columns with values that should be serialized/added as a script property
      'scriptMap' => array(
        'script' => 'script'
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
        'descriptionStatus',
        'levelOfDetail',
        'legacyId'
      ),

      // These values get exploded and stored to the rowStatusVars array
      'arrayColumns' => array(
        'parallelFormsOfName' => '|',
        'otherFormsOfName' => '|',
        'script' => '|'
      ),

      // Import logic to execute before saving QubitRepository
      'preSaveLogic' => function(&$self)
      {
        $opts = $self->getStatus('options');
        if (isset($opts['upload-limit']) && !isset($self->object->uploadLimit))
        {
          $self->object->uploadLimit = $opts['upload-limit'];
        }

        // Handle description status
        $self->object->descStatusId = $self->translateNameToTermId(
          'description status',
          $self->rowStatusVars['descriptionStatus'],
          array(),
          $self->status['descriptionStatusTypes'][$self->columnValue('culture')]
        );

        // Handle description detail
        $self->object->descDetailId = $self->translateNameToTermId(
          'description detail',
          $self->rowStatusVars['levelOfDetail'],
          array(),
          $self->status['levelOfDetailTypes'][$self->columnValue('culture')]
        );
      },

      // Import logic to execute after saving QubitRepository
      'postSaveLogic' => function(&$self)
      {
        // Handle alternate forms of name
        $typeIds = array(
          'parallel'     => QubitTerm::PARALLEL_FORM_OF_NAME_ID,
          'other'        => QubitTerm::OTHER_FORM_OF_NAME_ID
        );

        foreach ($typeIds as $typeName => $typeId)
        {
          $columnName = $typeName .'FormsOfName';
          $aliases = $self->rowStatusVars[$columnName];

          foreach($aliases as $alias)
          {
            // Add other name
            $otherName = new QubitOtherName;
            $otherName->objectId = $self->object->id;
            $otherName->name     = $alias;
            $otherName->typeId   = $typeId;
            $otherName->culture  = $self->columnValue('culture');
            $otherName->save();
          }
        }

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

          $contactInfo->culture = $self->columnValue('culture');
          $contactInfo->save();
        }

        // Add keymap entry
        if (!empty($self->rowStatusVars['legacyId']))
        {
          $self->createKeymapEntry($self->getStatus('sourceName'), $self->rowStatusVars['legacyId']);
        }
      }
    ));

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = ($options['index']) ? false : true;

    // Set update, limit and skip options
    $import->setUpdateOptions($options);

    $import->csv($fh, $skipRows);
    $this->logSection('repository-import', 'Imported repositories successfully!');
  }
}
