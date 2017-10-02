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
 * Load a csv list of digital objects
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class digitalObjectLoadTask extends sfBaseTask
{
  protected static
    $count = 0;

  private $curObjNum = 0;
  private $totalObjCount = 0;
  private $skippedCount = 0;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'The input file (csv format).')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('path', 'p', sfCommandOption::PARAMETER_OPTIONAL, 'Path prefix for digital objects', null),
      new sfCommandOption('limit', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Limit number of digital objects imported to n', null),
      new sfCommandOption('index', 'i', sfCommandOption::PARAMETER_NONE, 'Update search index (defaults to false)', null),
    ));

    $this->namespace = 'digitalobject';
    $this->name = 'load';
    $this->briefDescription = 'Load a csv list of digital objects';

    $this->detailedDescription = <<<EOF
Load a csv list of digital objects
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $options['conn'] = $databaseManager->getDatabase('propel')->getConnection();

    sfConfig::set('app_upload_dir', self::getUploadDir($options));

    if (false === $fh = fopen($arguments['filename'], 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    if (isset($options['limit']) && !is_numeric($options['limit']))
    {
      throw new sfException('Limit must be a number');
    }

    if ($options['index'])
    {
      QubitSearch::enable();
    }
    else
    {
      QubitSearch::disable();
    }

    $this->logSection('digital-object', "Load digital objects from {$arguments['filename']}...");

    // Get header (first) row
    $header = fgetcsv($fh, 1000);

    if ((!in_array('information_object_id', $header) && !in_array('identifier', $header)) || !in_array('filename', $header))
    {
      throw new sfException('Import file must contain an \'information_object_id\' or an \'identifier\' column, and a \'filename\' column');
    }

    $fileKey = array_search('filename', $header);

    // If information_object_id column is available, use it for id
    $idKey = array_search('information_object_id', $header);

    // If no id, then lookup by identifier
    if ($idKey === false)
    {
      $idKey = array_search('identifier', $header);
      $idType = 'identifier';
    }
    else
    {
      $idType = 'id';
    }

    // Build hash on information_object.id, with array value if information
    // object has multiple digital objects attached
    while ($item = fgetcsv($fh, 1000))
    {
      $id = $item[$idKey];
      $filename = $item[$fileKey];

      if (0 == strlen($id) || 0 == strlen($filename))
      {
        $this->log("Row $totalObjCount: missing $idType");

        continue;
      }

      if (0 == strlen($id) || 0 == strlen($filename))
      {
        $this->log("Row $totalObjCount: missing filename");

        continue;
      }

      if (!isset($digitalObjects[$id]))
      {
        $digitalObjects[$id] = $filename;
      }
      else if (!is_array($digitalObjects[$id]))
      {
        $digitalObjects[$id] = array($digitalObjects[$id], $filename);
      }
      else
      {
        $digitalObjects[$id][] = $filename;
      }

      $this->totalObjCount++;
    }

    $this->curObjNum = 0;

    // Set up prepared query based on identifier type
    $sql = 'SELECT io.id, do.id FROM '.QubitInformationObject::TABLE_NAME.' io
      LEFT JOIN '.QubitDigitalObject::TABLE_NAME.' do ON io.id = do.information_object_id';

    if ($idType == 'id')
    {
      $sql .= ' WHERE io.id = ?';
    }
    else
    {
      $sql .= ' WHERE identifier = ?';
    }

    $ioQuery = QubitPdo::prepare($sql);
    $importedCount = 0;

    // Loop through $digitalObject hash and add digital objects to db
    foreach ($digitalObjects as $key => $item)
    {
      // Stop importing if we've reached the limit
      if (isset($options['limit']) && ($importedCount >= $options['limit']))
      {
        break;
      }

      // No information_object_id specified, try looking up id via identifier
      if (!$ioQuery->execute(array($key)))
      {
        $this->log("Couldn't find information object with $idType: $key");

        continue;
      }

      // Fetch results
      $results = $ioQuery->fetch();

      if (!is_array($item))
      {
        // Skip if this information object already has a digital object attached
        if ($results[1] !== null)
        {
          $this->log(sprintf('Information object $idType: %s already has a digital object. Skipping.', $key));
          $this->skippedCount++;

          continue;
        }

        self::addDigitalObject($results[0], $item, $options);
      }
      else
      {
        // If more than one digital object linked to this information object
        for ($i=0; $i < count($item); $i++)
        {
          // Create new information objects, to maintain one-to-one
          // relationship with digital objects
          $informationObject = new QubitInformationObject;
          $informationObject->parent = QubitInformationObject::getById($results[0]);
          $informationObject->title = basename($item[$i]);
          $informationObject->save($options['conn']);

          self::addDigitalObject($informationObject->id, $item[$i], $options);
        }
      }

      $importedCount++;
    }

    $this->logSection('digital-object', 'Successfully Loaded '.self::$count.' digital objects.');

    // Warn user to manually update search index
    if (!$options['index'])
    {
      $this->logSection('digital-object', 'Please update the search index manually to reflect any changes');
    }
  }

  protected function addDigitalObject($ioId, $path, $options = array())
  {
    $this->curObjNum++;

    if (isset($options['path']))
    {
      $path = $options['path'].$path;
    }

    // read file contents
    if (false === $content = file_get_contents($path))
    {
      $this->log("Couldn't read file '$path'");

      return;
    }

    $filename = basename($path);

    $remainingImportCount = $this->totalObjCount - $this->skippedCount - $importedCount;
    $message = "Loading '$filename' " . "({$this->curObjNum} of {$remainingImportCount} remaining";

    if (isset($options['limit']))
    {
      $message .= ": limited to {$options['limit']} imports";
    }
    $message .= ")";

    $this->log("(" . strftime("%h %d, %r") . ") ". $message);

    // Create digital object
    $do = new QubitDigitalObject;
    $do->informationObjectId = $ioId;
    $do->usageId = QubitTerm::MASTER_ID;
    $do->assets[] = new QubitAsset($filename, $content);
    $do->save($options['conn']);

    self::$count++;
  }

  protected function getUploadDir($options = array())
  {
    $uploadDir = 'uploads'; // Default value

    $sql = 'SELECT i18n.value
      FROM setting stg JOIN setting_i18n i18n ON stg.id = i18n.id
      WHERE stg.source_culture = i18n.culture
       AND stg.name = \'upload_dir\';';

    if ($sth = $options['conn']->query($sql))
    {
      list($uploadDir) = $sth->fetch();
    }

    return $uploadDir;
  }
}
