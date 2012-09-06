<?php

class QubitCsvTransformFactory {

  public $cliOptions;
  public $machineName;
  public $addColumns;
  public $renameColumns;
  public $parentKeyLogic;
  public $rowParentKeyLookupLogic;
  public $transformLogic;

  public function __construct($options = array())
  {
    $allowedProperties = array(
      'cliOptions',
      'machineName',
      'addColumns',
      'renameColumns',
      'parentKeyLogic',
      'rowParentKeyLookupLogic',
      'transformLogic'
    );

    QubitFlatfileImport::setPropertiesFromArray(
      $this,
      $options,
      $allowedProperties
    );

    if (!$this->machineName)
    {
      throw new sfException('The machineName property is required.');
    }
  }

  public function make()
  {
    $tempCsvFile = sys_get_temp_dir() .'/'. $this->machineName .'_stage1.csv';

    return new QubitCsvTransform(array(

      'options' => $this->cliOptions,

      'status' => array(
        'parentKeys'              => array(),
        'noIdentifierCount'       => 0,
        'tempFile'                => $tempCsvFile,
        'outFh'                   => fopen($tempCsvFile, 'w'),
        'parentKeyLogic'          => $this->parentKeyLogic,
        'rowParentKeyLookupLogic' => $this->rowParentKeyLookupLogic
      ),

      'transformLogic' => $this->transformLogic,

      'addColumns' => $this->addColumns,

      'renameColumns' => $this->renameColumns,

      'saveLogic' => function(&$self)
      {
        $self->writeHeadersOnFirstPass();

        if (isset($self->status['parentKeyLogic']))
        {
          $parentKey = trim($self->status['parentKeyLogic']($self));
          if ($parentKey)
          {
            print "Stored parent key...\n";
            $self->status['parentKeys'][$parentKey] = $self->columnValue('legacyId');
          }
        }

        if (isset($self->transformLogic))
        {
          $self->executeClosurePropertyIfSet('transformLogic');
        }

        fputcsv($self->status['outFh'], $self->status['row']);
      },

      'completeLogic' => function(&$self)
      {
        print "Step 1 complete.\n";

        $fhIn = fopen($self->status['tempFile'], 'r');

        if (!$fhIn) throw new sfException('Error reading '. $self->status['tempFile'] .'.');

        $self->initializeMySQLtemp();

        $stage2 = new QubitCsvTransform(array(

          'skipOptionsAndEnvironmentCheck' => true,

          'status' => array(
            'finalOutputFile'  => $self->status['finalOutputFile'],
            'parentKeys'       => $self->status['parentKeys'],
            'badParents'       => 0,
            'tempFile'         => $self->status['tempFile'],
            'badLevelOfDescription' => 0,
            'rowParentKeyLookupLogic' => $self->status['rowParentKeyLookupLogic'],
            'ignoreBadLod' => $self->status['ignoreBadLod']
          ),

          'errorLog' => $self->errorLog,

          'saveLogic' => function(&$self)
          {
            if (isset($self->status['rowParentKeyLookupLogic']))
            {
              $keyOfRowParent = trim($self->status['rowParentKeyLookupLogic']($self));
              if ($keyOfRowParent && isset($self->status['parentKeys'][$keyOfRowParent])) {
                $parentId = $self->status['parentKeys'][$keyOfRowParent];
                print "Found parent ID ". $parentId ."\n";
                $self->columnValue('parentId', $parentId);
              } else {
                $self->status['badParents']++;
              }
            }

            $levelOfDescriptionAvailable = is_numeric(array_search('levelOfDescription', $self->columnNames));

            if ($levelOfDescriptionAvailable)
            {
              print "Found a level of description...\n";

              $sortorder = $self->levelOfDescriptionToSortorder($self->columnValue('levelOfDescription'));

              if (is_numeric($sortorder))
              {
                print "Description sort order is ". $sortorder .".\n";
                $self->addRowToMySQL($sortorder);
              }
              else if (isset($self->status['ignoreBadLod']) && $self->status['ignoreBadLod'])
              {
                $sortorder = count($self->levelsOfDescription);
                print "Description sort order is ". $sortorder .".\n";
                $self->addRowToMySQL($sortorder);
              } else {
                $self->status['badLevelOfDescription']++;
                print "Ignoring data with bad level of description: '". $self->columnValue('levelOfDescription') . "'.\n";
              }
            } else {
              $self->addRowToMySQL(0);
            }
          },

          'completeLogic' => function(&$self)
          {
            $self->writeMySQLRowsToCsvFilePath($self->status['finalOutputFile']);

            print "Step 2 complete.\n";
            print "Bad parents found: ". $self->status['badParents'] .".\n";
            print "Bad level of description found: ". $self->status['badLevelOfDescription'] .".\n";
          }
        ));

        $stage2->csv($fhIn);
      }
    ));
  }
}
