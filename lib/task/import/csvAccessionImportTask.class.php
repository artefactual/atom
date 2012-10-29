<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Import csv accession data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvAccessionImportTask extends csvImportBaseTask
{
  protected $namespace           = 'csv';
  protected $name                = 'accession-import';
  protected $briefDescription    = 'Import csv acession data';
  protected $detailedDescription = <<<EOF
Import CSV data
EOF;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption(
        'source-name',
        null,
        sfCommandOption::PARAMETER_OPTIONAL, 'Source name to use when inserting keymap entries.'
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

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Load taxonomies into variables to avoid use of magic numbers
    $termData = QubitFlatfileImport::loadTermsFromTaxonomies(array(
      QubitTaxonomy::ACCESSION_ACQUISITION_TYPE_ID    => 'acquisitionTypes',
      QubitTaxonomy::ACCESSION_RESOURCE_TYPE_ID       => 'resourceTypes',
      QubitTaxonomy::ACCESSION_PROCESSING_STATUS_ID   => 'processingStatus',
      QubitTaxonomy::ACCESSION_PROCESSING_PRIORITY_ID => 'processingPriority'
    ));

    // Define import
    $import = new QubitFlatfileImport(array(
      /* How many rows should import until we display an import status update? */
      'rowsUntilProgressDisplay' => $options['rows-until-update'],

      /* Where to log errors to */
      'errorLog' => $options['error-log'],

      /* the status array is a place to put data that should be accessible
         from closure logic using the getStatus method */
      'status' => array(
        'sourceName'         => $sourceName,
        'acquisitionTypes'   => $termData['acquisitionTypes'],
        'resourceTypes'      => $termData['resourceTypes'],
        'processingStatus'   => $termData['processingStatus'],
        'processingPriority' => $termData['processingPriority']
      ),

      'standardColumns' => array(
        'appraisal',
        'archivalHistory',
        'acquisitionDate',
        'locationInformation',
        'processingNotes',
        'receivedExtentUnits',
        'scopeAndContent',
        'sourceOfAcquisition',
        'title'
      ),

      /* import columns that should be redirected to QubitInformationObject
         properties (and optionally transformed)
      
         Example:
         'columnMap' => array(
           'Archival History' => 'archivalHistory',
           'Revision history' => array(
             'column' => 'revision',
             'transformationLogic' => function(&$self, $text)
             {
               return $self->appendWithLineBreakIfNeeded(
                 $self->object->revision,
                 $text
               );
             }
           )
         ),
      */
      'columnMap' => array(
        'physicalCondition' => 'physicalCharacteristics'
      ),

      /* these values get stored to the rowStatusVars array */
      'variableColumns' => array(
        'accessionNumber',
        'acquisitionType',
        'resourceType',
        'donorName',
        'donorStreetAddress',
        'donorCity',
        'donorRegion',
        'donorCountry',
        'donorPostalCode',
        'donorTelephone',
        'donorEmail',
        'creators'
      ),

      /* import logic to load accession */
      'rowInitLogic' => function(&$self)
      {
        $accessionNumber =  $self->rowStatusVars['accessionNumber'];

        // look up Qubit ID of pre-created accession
        $statement = $self->sqlQuery(
          "SELECT id FROM accession WHERE identifier=?",
          $params = array($accessionNumber)
        );

        $result = $statement->fetch(PDO::FETCH_OBJ);
        if ($result)
        {
          print 'Found '. $result->id ."\n";
          $self->object = QubitAccession::getById($result->id);
        } else {
          $self->object = false;
          $error = "Couldn't find accession # ". $accessionNumber .'... creating.';
          print $error ."\n";
          $self->object = new QubitAccession();
          $self->object->identifier = $accessionNumber;
        }
      },

      /* import logic to save accession */
      'saveLogic' => function(&$self)
      {
        if(isset($self->object) && is_object($self->object))
        {
          $self->object->save();
        }
      },

      /* create related objects */
      'postSaveLogic' => function(&$self)
      {
        if(isset($self->object) && is_object($self->object))
        {
          if (
            isset($self->rowStatusVars['creators'])
            && $self->rowStatusVars['creators']
          )
          {
            $creators = explode('|', $self->rowStatusVars['creators']);
            foreach($creators as $creator)
            {
              // fetch/create actor
              $actor = $self->createOrFetchActor($creator);

              // create relation between accession and creator
              $self->createRelation($actor->id, $self->object->id, QubitTerm::CREATION_ID);
            }
          }

          if (
            isset($self->rowStatusVars['donorName'])
            && $self->rowStatusVars['donorName']
          )
          {
            // fetch/create actor
            $actor = $self->createOrFetchActor($self->rowStatusVars['donorName']);

            // map column names to QubitContactInformation properties
            $columnToProperty = array(
              'donorEmail'         => 'email',
              'donorTelephone'     => 'telephone',
              'donorStreetAddress' => 'streetAddress',
              'donorCity'          => 'city',
              'donorRegion'        => 'region',
              'donorPostalCode'    => 'postalCode'
            );

            // set up creation of contact infomation
            $contactData = array();
            foreach($columnToProperty as $column => $property)
            {
              if (isset($self->rowStatusVars[$column]))
              {
                $contactData[$property] = $self->rowStatusVars[$column];
              }
            }

            // create contact information if none exists
            $self->createOrFetchContactInformation($actor->id, $contactData);

            // create relation between accession and donor
            $self->createRelation($self->object->id, $actor->id, QubitTerm::DONOR_ID);
          }
        }
      }
    ));

    $import->addColumnHandler('acquisitionDate', function(&$self, $data)
    {
      if ($data)
      {
        if (isset($self->object) && is_object($self->object))
        { 
          $parsedDate = $self->parseDateLoggingErrors($data);
          if ($parsedDate) {
            $self->object->date = $parsedDate;
          }
        }
      }
    });

    $import->addColumnHandler('resourceType', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'resourceTypeId',
        'resource type',
        $data,
        $self->getStatus('resourceTypes')
      );
    });

    $import->addColumnHandler('acquisitionType', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'acquisitionTypeId',
        'acquisition type',
        $data,
        $self->getStatus('acquisitionTypes')
      );
    });

    $import->addColumnHandler('processingStatus', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'processingStatusId',
        'processing status',
        $data,
        $self->getStatus('processingStatus')
      );
    });

    $import->addColumnHandler('processingPriority', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'processingPriorityId',
        'processing priority',
        $data,
        $self->getStatus('processingPriority')
      );
    });

    $import->csv($fh, $skipRows);
  }
}

function setObjectPropertyToTermIdLookedUpFromTermNameArray(&$self, $property, $propertyDescription, $termName, $termNameArray)
{
  if ($termName)
  {
    if (isset($self->object) && is_object($self->object))
    {
      $self->object->$property = $self->translateNameToTermId(
        $propertyDescription,
        $termName,
        array(),
        $termNameArray
      );
    }
  }
}
