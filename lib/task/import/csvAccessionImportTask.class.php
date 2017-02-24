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
      // Pass context
      'context' => sfContext::createInstance($this->configuration),

      // How many rows should import until we display an import status update?
      'rowsUntilProgressDisplay' => $options['rows-until-update'],

      // Where to log errors to
      'errorLog' => $options['error-log'],

      // The status array is a place to put data that should be accessible
      // from closure logic using the getStatus method
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

      'arrayColumns' => array(
        'eventActors'          => '|',
        'eventActorHistories'  => '|',
        'eventTypes'           => '|',
        'eventPlaces'          => '|',
        'eventDates'           => '|',
        'eventStartDates'      => '|',
        'eventEndDates'        => '|',
        'eventDescriptions'    => '|',

        // These columns are for backwards compatibility
        'creators'           => '|',
        'creatorHistories'   => '|',
        'creatorDates'       => '|',
        'creatorDatesStart'  => '|',
        'creatorDatesEnd'    => '|',
        'creatorDateNotes'   => '|',
        'creationDates'      => '|',
        'creationDatesStart' => '|',
        'creationDatesEnd'   => '|',
        'creationDateNotes'  => '|',
        'creationDatesType'  => '|'
      ),

      // Import columns that should be redirected to QubitAccession
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
        'physicalCondition' => 'physicalCharacteristics'
      ),

      // These values get stored to the rowStatusVars array
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
        'donorCountry',
        'donorTelephone',
        'donorEmail',
        'qubitParentSlug'
      ),

      // Import logic to load accession
      'rowInitLogic' => function(&$self)
      {
        $accessionNumber =  $self->rowStatusVars['accessionNumber'];

        // Look up Qubit ID of pre-created accession
        $statement = $self->sqlQuery(
          "SELECT id FROM accession WHERE identifier=?",
          $params = array($accessionNumber)
        );

        $result = $statement->fetch(PDO::FETCH_OBJ);
        if ($result)
        {
          print 'Found '. $result->id ."\n";
          $self->object = QubitAccession::getById($result->id);
        }
        else
        {
          $self->object = false;
          $error = "Couldn't find accession # ". $accessionNumber .'... creating.';
          print $error ."\n";
          $self->object = new QubitAccession();
          $self->object->identifier = $accessionNumber;
        }
      },

      // Import logic to save accession
      'saveLogic' => function(&$self)
      {
        if(isset($self->object) && is_object($self->object))
        {
          $self->object->save();
        }
      },

      // Create related objects
      'postSaveLogic' => function(&$self)
      {
        if(isset($self->object) && is_object($self->object))
        {
          // Add creators
          if (isset($self->rowStatusVars['creators'])
            && $self->rowStatusVars['creators'])
          {
            foreach($self->rowStatusVars['creators'] as $creator)
            {
              // Fetch/create actor
              $actor = $self->createOrFetchActor($creator);

              // Create relation between accession and creator
              $self->createRelation($actor->id, $self->object->id, QubitTerm::CREATION_ID);
            }
          }

          // Add events
          csvImportBaseTask::importEvents($self);

          if (isset($self->rowStatusVars['donorName'])
            && $self->rowStatusVars['donorName'])
          {
            // Fetch/create donor
            $donor = $self->createOrFetchDonor($self->rowStatusVars['donorName']);

            // Map column names to QubitContactInformation properties
            $columnToProperty = array(
              'donorEmail'         => 'email',
              'donorTelephone'     => 'telephone',
              'donorStreetAddress' => 'streetAddress',
              'donorCity'          => 'city',
              'donorRegion'        => 'region',
              'donorPostalCode'    => 'postalCode'
            );

            // Set up creation of contact infomation
            $contactData = array();
            foreach($columnToProperty as $column => $property)
            {
              if (isset($self->rowStatusVars[$column]))
              {
                $contactData[$property] = $self->rowStatusVars[$column];
              }
            }

            // Attempt to coerce country to country code if value specified (and not already a country code)
            if (!empty($self->rowStatusVars['donorCountry']))
            {
              $contactData['countryCode'] = $this->parseCountryOrCountryCodeOrFail($self->rowStatusVars['donorCountry']);
            }

            // Create contact information if none exists
            $self->createOrFetchContactInformation($donor->id, $contactData);

            // Create relation between accession and donor
            $self->createRelation($self->object->id, $donor->id, QubitTerm::DONOR_ID);
          }

          // Link accession to existing description
          if (isset($self->rowStatusVars['qubitParentSlug'])
            && $self->rowStatusVars['qubitParentSlug'])
          {
            $query = "SELECT object_id FROM slug WHERE slug=?";
            $statement = QubitFlatfileImport::sqlQuery($query, array($self->rowStatusVars['qubitParentSlug']));
            $result = $statement->fetch(PDO::FETCH_OBJ);
            if ($result)
            {
              $self->createRelation($result->object_id, $self->object->id, QubitTerm::ACCESSION_ID);
            }
            else
            {
              throw new sfException('Could not find information object matching slug "'. $self->rowStatusVars['qubitParentSlug'] .'"');
            }
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
          if ($parsedDate)
          {
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
        $self->status['resourceTypes'][$self->columnValue('culture')]
      );
    });

    $import->addColumnHandler('acquisitionType', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'acquisitionTypeId',
        'acquisition type',
        $data,
        $self->status['acquisitionTypes'][$self->columnValue('culture')]
      );
    });

    $import->addColumnHandler('processingStatus', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'processingStatusId',
        'processing status',
        $data,
        $self->status['processingStatus'][$self->columnValue('culture')]
      );
    });

    $import->addColumnHandler('processingPriority', function(&$self, $data)
    {
      setObjectPropertyToTermIdLookedUpFromTermNameArray(
        $self,
        'processingPriorityId',
        'processing priority',
        $data,
        $self->status['processingPriority'][$self->columnValue('culture')]
      );
    });

    // Allow search indexing to be enabled via a CLI option
    $import->searchIndexingDisabled = ($options['index']) ? false : true;

    $import->csv($fh, $skipRows);
  }

  private function parseCountryOrCountryCodeOrFail($value)
  {
    $countries = sfCultureInfo::getInstance()->getCountries();

    if (isset($countries[strtoupper($value)]))
    {
      return $value; // Value was a country code
    }
    else if ($countryCode = array_search($value, $countries))
    {
      return $countryCode; // Value was a country name
    }
    else
    {
      throw new sfException(sprintf('Could not find country or country code matching "%s"', $value));
    }
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
