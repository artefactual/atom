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
 * Process a CSV file to import digital objects from an Archivematica DIP and
 * associate them with existing information objects in AtoM
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class importDipObjectsTask extends arBaseTask
{
  protected
    $conn,                    // Database connection
    $dipDir,                  // Path to DIP
    $columnNames = array(),   // Sequence of column names
    $columnIndexes = array(), // Index in CSV row for each column
    $uniqueValueColumnName;   // Column name of unique identifier ("identifier" or "slug")

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('dip', sfCommandArgument::REQUIRED, 'The DIP directory.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('undo-log-dir', null, sfCommandOption::PARAMETER_OPTIONAL, 'Directory to write undo logs to', false),
      new sfCommandOption('audit', null, sfCommandOption::PARAMETER_NONE, 'Audit mode')
    ));

    $this->namespace = 'import';
    $this->name = 'dip-objects';
    $this->briefDescription = 'Import digital objects from Archivematica DIP using CSV file';

    $this->detailedDescription = <<<EOF
Process a CSV file to import digital objects from an Archivematica DIP to
existing information objects in AtoM.

The CSV file can be named anything, but must have the extension "csv"
(lower-case).

The CSV file must start with a header row specifying column order. A "filename"
column must be included. Additionally, either an "identifier" or a "slug"
column must be included (not both: one of these will be used to specify
information objects to import to).

The import script expects that Archivematica will have modified the filename
so:

a) The original object file has been converted to a derivative with a
   correspondiing extension ("jpg" for example)

b) A UUID has been pre-pended to the filename (for example:
   "815da5cf-f49f-41f5-aa5d-c40d9d4dec3c-MARBLES.jpg")

Note: Filenames must be unique (without UUID) to avoid colliding on import.

The undo-log-dir option can be used to log which information objects have
digital objects added to them. This log can be used, in event of an incomplete
import, to either establish where the import stopped or to manually remove the
imported digital objects. Undo logs contain two columns: the ID of the
information object to which objects have beem imported and the DIP directory
the objects were imported from.

The audit option can be used to verify that all objects specified in a DIP's
CSV file were imported. If any are found to be missing the object filename
will be output.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->checkArgumentsAndOptions($arguments, $options);

    sfContext::createInstance($this->configuration);

    QubitSearch::getInstance()->enable();

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('propel')->getConnection();

    // Set dip directory
    $this->dipDir = $arguments['dip'];

    // Set undo log filename, if undo log directory is specified
    $undoLog = null;
    if (isset($options['undo-log-dir']))
    {
      $undoLog = rtrim($options['undo-log-dir'], '/') .'/'. date('Y-m-d') .'-'. basename($this->dipDir) .'.log';
    }

    // Set path to DIP objects
    $objectsPath = rtrim($this->dipDir, '/') .'/objects';
    $this->logSection('dip-import', sprintf('Looking for objects in: %s', $this->dipDir));

    // Parse CSV file and import/audit objects
    $digitalObjects = $this->parseCsvData($this->openFirstCsvFile($objectsPath), $objectsPath);
    $count = $this->importDigitalObjects($digitalObjects, $options['audit'], $undoLog);

    $verb = ($options['audit']) ? 'audited' : 'processed';
    $this->logSection('dip-import', sprintf('Successfully %s %d digital objects.', $verb, $count));
  }

  /**
   * Make sure argument and option values are valid
   *
   * @param array $arguments  array of sfCommandArgument instances
   * @param array $options  array of sfCommandOption instances
   *
   * @return void
   */
  protected function checkArgumentsAndOptions($arguments, $options)
  {
    // Make sure DIP directory exists
    if (!is_dir($arguments['dip']))
    {
      throw new sfException('You must specify a DIP directory');
    }

    // If undo log directory specified, make sure it's a valid directory 
    if (!empty($options['undo-log-dir']) && !is_dir($options['undo-log-dir']))
    {
      throw new sfException('Undo log directory does not exist.');
    }
  }

  /**
   * Return file pointer resource for the first CSV file found within the DIP's
   * objects directory. CSV file's extension must be lower-case.
   *
   * @param string $objectsPathh  Path to DIP object files
   *
   * @return resource  File pointer resource for CSV file
   */
  protected function openFirstCsvFile($objectsPath)
  {
    // Find all CSV files in the objects directory
    $csvFiles = sfFinder::type('file')->name('*.csv')->in($objectsPath);

    // Attempt to open the first CSV file found
    if (0 == count($csvFiles) || false === $fh = fopen($csvFiles[0], 'rb'))
    {
      throw new sfException('You must specify a DIP that contains a CSV file');
    }

    return $fh;
  }

  /**
   * Determine CSV column order and which information object attribute (slug or
   * identifier) will be used as unique value to specify information objects to
   * timport digital objects to. Read CSV data, creating an array of information
   * object unique values and the original filenames of digital objects that
   * should be imported and associated with these information objects.
   *
   * @param resource $fh  File pointer resource for CSV file
   * @param string $objectsPath  Path to DIP object files
   *
   * @return array  Hash with hash key being unique value and hash value(s)
   *                being original object filenames
   */
  protected function parseCsvData($fh, $objectsPath)
  {
    // Create a lookup table to match original file basename (UUID and
    // extension removed) with original object filename (example:
    // 979c4458-21f3-11e1-a4bd-001d09282b9d-foo.tif)
    $filenames = $this->createFilenameLookup($objectsPath);

    // Determine column order and what type of unique value will be used to find
    // information objects (slug or identifier)
    $this->processCsvHeaderRow($fh);

    $digitalObjects = array();

    // Build hash on information_object key, with value being an array if information
    // object has multiple digital objects attached
    while ($row = fgetcsv($fh, 1000))
    {
      $filename = $this->getRowColumnValue('filename', $row);
      $filepath = $objectsPath .'/'. $filename;

      // Check if filename has been changed by Archivematica
      if (!file_exists($filepath))
      {
        $key = null;

        // Substitute original file extension (e.g. TIFF) with new DIP extension
        // (e.g. JPEG)
        if (preg_match('/(.+)\.(\w{3})$/', $filename, $matches))
        {
          $key = strtolower($matches[1]);
        }

        if (isset($key) && isset($filenames[$key]))
        {
          $filepath = str_replace($filename, $filenames[$key], $filepath);
        }
        else
        {
          throw new Exception("Error: Couldn't find file $filepath");
        }
      }

      $uniqueValue = $this->getRowColumnValue($this->uniqueValueColumnName, $row);

      if (!isset($digitalObjects[$uniqueValue]))
      {
        $digitalObjects[$uniqueValue] = $filepath;
      }
      else if (!is_array($digitalObjects[$uniqueValue]))
      {
        $digitalObjects[$uniqueValue] = array($digitalObjects[$uniqueValue], $filepath);
      }
      else
      {
        $digitalObjects[$uniqueValue][] = $filepath;
      }
    }

    return $digitalObjects;
  }

  /**
   * Determine CSV column order and which information object attribute will be
   * used as a unique value to specify information objects to import digital
   * objects to.
   *
   * @param resource $fh  File pointer resource for CSV file
   *
   * @return void
   */
  protected function processCsvHeaderRow($fh)
  {
    // Storage order of columns in CSV header row
    $this->columnNames = fgetcsv($fh, 1000);

    // Make sure there isn't both an "identifier" and "slug" column in the CSV header row
    $identifierExists = in_array('identifier', $this->columnNames);

    if ($identifierExists && in_array('slug', $this->columnNames))
    {
      throw new sfException('Error: CSV header row includes both an "identifier" column and a "slug" column. Please use only one of the two.');
    }

    // Make sure there is either an "identifier" or a "slug" column  in the CSV header row
    if (!$identifierExists && !in_array('slug', $this->columnNames))
    {
      throw new sfException('Error: CSV header row must include either an "identifier" column or a "slug" column.');
    }

    // Determine which column name should be used to specific individual information objects
    $this->uniqueValueColumnName = (in_array('identifier', $this->columnNames)) ? 'identifier' : 'slug';
  }

  /**
   * Get value of column, using column name, from array representing row of CSV data
   *
   * @return void
   */
  function getRowColumnValue($column, $row)
  {
    // Return cached column index, if present
    if (isset($this->columnIndexes[$column]))
    {
      return $row[($this->columnIndexes[$column])];
    }

    // Determine column index and cache
    if (is_numeric($columnIndex = array_search($column, $this->columnNames)))
    {
      $this->columnIndexes[$column] = $columnIndex;
      return $row[$columnIndex];
    }

    throw new sfException('Missing column "'. $column .'".');
  }

  /**
   * Import digital objects
   *
   * @param array $digitalObjects  Hash with keys of unique value for
   *                               specifying information objects, values:
   *                               digital object filenames
   * @param boolean $auditMode  Whether to audit, rather than import, objects
   * @param string $undoLog  Path to undo log
   *
   * @return void
   */
  protected function importDigitalObjects($digitalObjects, $auditMode = false, $undoLog = null)
  {
    $count = 0;

    // Loop through $digitalObject hash and add digital objects to db
    foreach ($digitalObjects as $key => $item)
    {
      $opDescription = ($auditMode) ? 'Auditing' : 'Importing to';
      $this->logSection('dip-import', sprintf("%s '$key'...", $opDescription));

      if ($auditMode)
      {
        if (!is_array($item))
        {
          $this->auditDigitalObject($item);
          $count++;
        }
        else
        {
          // If more than one digital object linked to this information object
          foreach ($item as $filepath)
          {
            $this->auditDigitalObject($filepath);
            $count++;
          }
        }
      }
      else
      {
        $informationObject = $this->getInformationObjectUsingUniqueId($key);

        if (!is_array($item))
        {
          $this->addDigitalObject($informationObject, $item, $undoLog);
          $count++;
        }
        else
        {
          // If more than one digital object linked to this information object
          foreach ($item as $filepath)
          {
            // Create new information objects, to maintain one-to-one
            // relationship with digital objects
            $childInformationObject = new QubitInformationObject;
            $childInformationObject->parent = $informationObject;
            $childInformationObject->title = basename($filepath);

            $childInformationObject->save($this->conn);

            $this->addDigitalObject($childInformationObject, $filepath, $undoLog, true);
            $count++;
          }
        }
      }
    }

    return $count;
  }

  /**
   * Get information object using unique identifier
   *
   * @param string $uniqueValue  Unique value for specifying information object
   *
   * @return object QubitInformationObject  Information object
   */
  protected function getInformationObjectUsingUniqueId($uniqueValue)
  {
    $criteria = new Criteria;

    if ($this->uniqueValueColumnName == 'identifier')
    {
      $criteria->add(QubitInformationObject::IDENTIFIER, $uniqueValue);

      if (null === $informationObject = QubitInformationObject::getOne($criteria))
      {
        throw new Exception("Invalid information object identifier '$uniqueValue'");
      }
    }
    else
    {
      $criteria->add(QubitSlug::SLUG, $uniqueValue);

      if (null === $slug = QubitSlug::getOne($criteria))
      {
        throw new Exception("Invalid information object slug '$uniqueValue'");
      }

      if (null === $informationObject = QubitInformationObject::getById($slug->objectId))
      {
        throw new Exception("Missing information object for slug '$uniqueValue'");
      }
    }

    return $informationObject;
  }

  /**
   * Check to see if an object has already been imported and, if not, print
   * text to notify.
   *
   * @param string $filepath  Path, within DIP, to file
   *
   * @return void
   */
  protected function auditDigitalObject($filepath)
  {
    $filename = basename($filepath);

    $query = "SELECT id FROM digital_object WHERE name=?";

    $statement = QubitFlatfileImport::sqlQuery($query, array($filename));

    if (!$statement->fetchColumn())
    {
      $this->log("Missing ". $filename);
    }
  }

  /**
   * Import object, associate with information object, and, optionally, log
   *
   * @param object QubitInformationObject $informationObject  Information object
   * @param string $filepath  Path, within DIP, to file
   * @param boolean $undoLog  Optional undo log location
   * @param boolean $container  Whether information object contains others
   *
   * @return void
   */
  protected function addDigitalObject($informationObject, $filepath, $undoLog = null, $container = false)
  {
    // Abort if a digital object already exists for this information object
    if (null !== $informationObject->getDigitalObject())
    {
      $this->log("A digital object is already attached to $informationObject->identifier (slug: $informationObject->slug). Skipping.");

      return;
    }

    // Make sure file exists
    if (!file_exists($filepath))
    {
      throw new Exception("Couldn't find file '$filepath'");
    }

    // Create digital object
    $this->log("Importing '$filepath'");

    $do = new QubitDigitalObject;
    $do->usageId = QubitTerm::MASTER_ID;
    $do->assets[] = new QubitAsset($filepath);

    // Add digital object to information object
    $informationObject->digitalObjects[] = $do;

    // Add DIP UUID as aipUUID information object property
    if (null !== $dipUUID = $this->getUUID(basename($this->dipDir)))
    {
      $this->log('Creating property: dip UUID '. $dipUUID);
      $informationObject->addProperty('aipUUID', $dipUUID);
    }

    // Add object UUID as objectUUID information object property
    if (null !== $objectUUID = $this->getUUID(basename($filepath)))
    {
      $this->log('Creating property: object UUID '. $objectUUID);
      $informationObject->addProperty('objectUUID', $objectUUID);
    }

    // Save and, optionally, log
    $informationObject->save($this->conn);

    if (isset($undoLog))
    {
      $logLine = $informationObject->id ."\t". basename($this->dipDir) ."\t". $container ."\n";
      file_put_contents($undoLog, $logLine, FILE_APPEND);
    }
  }

  /**
   * Create a lookup table that will be used to match original filename
   * (example: foo.TIF) with its DIP object filename (example:
   * 979c4458-21f3-11e1-a4bd-001d09282b9d-foo.jpg)
   *
   * The lookup table stores files by their basename in this format:
   *
   * Array
   * (
   *   [somefile] => ad0acf68-34b4-47ea-aef7-e5e29c5b7388-somefile.tif
   * )
   *
   * @param string $objectsPath  Path to DIP objects
   *
   * @return array  Hash (see function description)
   */
  protected function createFilenameLookup($objectsPath)
  {
    foreach (scandir($objectsPath) as $file)
    {
      if (is_dir($file))
      {
        continue;
      }

      // Format should be UUID (37 hex chars or hyphen) + filename + '.jpg'
      $pattern = '/^[0-9a-f-]{37}(.+)\.(\w{3})$/';

      if (!preg_match($pattern, strtolower($file), $matches))
      {
        continue;
      }

      // Skip CSV manifest file
      if ('csv' == $matches[2])
      {
        continue;
      }

      // Key is original filename without extension
      $filenames[$matches[1]] = $file;
    }

    return $filenames;
  }

  /*
   * Parse UUID from string
   *
   * For example: "ad0acf68-34b4-47ea-aef7-e5e29c5b7388-bob.tif" would parse to
   * "ad0acf68-34b4-47ea-aef7-e5e29c5b7388". If multiple UUIDs exist in a
   * string the first found is returned. Null is returned if no UUID
   * is found.
   *
   * @param string @subject  String that possibly contains UUID
   *
   * @return mixed UUID  UUID string or, if none found, null
   */
  protected function getUUID($subject)
  {
    preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $subject, $matches);

    // Return null if no UUIDs found
    if (empty($matches[0]))
    {
      return null;
    }

    // If UUIDs found, inform user and return first UUID found
    $this->logSection('dip-import', 'UUID found: '. $matches[0][0] ." in ". $subject);

    return $matches[0][0];
  }
}
