<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
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

    QubitSearch::getInstance()->disabled = true;

    $this->logSection("Load digital objects from {$arguments['filename']}...");

    // Get header (first) row
    $header = fgetcsv($fh, 1000);

    if (!in_array('information_object_id', $header) || !in_array('filename', $header))
    {
      throw new sfException('Import file must contain an \'information_object_id\' and \'filename\' column');
    }

    $idKey = array_search('information_object_id', $header);
    $fileKey = array_search('filename', $header);
    $identifierKey = array_search('identifier', $header);

    // Build hash on information_object.id, with array value if information
    // object has multiple digital objects attached
    while ($item = fgetcsv($fh, 1000))
    {
      $id = $item[$idKey];
      $identifier = $item[$identifierKey];
      $filename = $item[$fileKey];

      // No information_object_id specified, try looking up id via identifier
      if (strlen($id) < 1 && strlen($identifier) > 0)
      {
        if (null !== $ret = $this->getIdFromIdentifier($identifier))
        {
          $id = $ret;
        }
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

    // Loop through $digitalObject hash and add digital objects to db
    foreach ($digitalObjects as $key => $item)
    {
      if (null === $informationObject = QubitInformationObject::getById($key))
      {
        $this->curObjNum++;
        $this->log("Invalid information_object id $key");

        continue;
      }

      if (!is_array($item))
      {
        self::addDigitalObject($informationObject, $item, $options);
      }
      else
      {
        // If more than one digital object linked to this information object
        for ($i=0; $i < count($item); $i++)
        {
          $informationObject->title = basename($item[$i]);
          // Create new information objects, to maintain one-to-one
          // relationship with digital objects
          $informationObject = new QubitInformationObject;
          $informationObject->parent = QubitInformationObject::getById($key);
          $informationObject->title = basename($item[$i]);
          $informationObject->save($options['conn']);

          self::addDigitalObject($informationObject, $item[$i], $options);
        }
      }
    }

    $this->logSection('Successfully Loaded '.self::$count.' digital objects.');
  }

  protected function getIdFromIdentifier($identifier)
  {
    $sql = 'SELECT id from information_object where identifier = ?';
    $id = QubitPdo::fetchColumn($sql, array($identifier));

    if ($id)
    {
      return $id;
    }
  }

  protected function addDigitalObject($informationObject, $path, $options = array())
  {
    $this->curObjNum++;

    if (isset($options['path']))
    {
      $path = $options['path'].$path;
    }

    $sql = 'SELECT id FROM digital_object WHERE information_object_id = ?';

    if (QubitPdo::fetchColumn($sql, array($informationObject->id)))
    {
      $this->log(sprintf('Information object id:%s (%s) already has a digital object. Skipping.', $informationObject->id, $informationObject->identifier));

      return;
    }

    // read file contents
    if (false === $content = file_get_contents($path))
    {
      $this->log("Couldn't read file '$path'");

      return;
    }

    $filename = basename($path);
    $this->log("(" . strftime("%h %d, %r") . ") Loading '$filename' " . "({$this->curObjNum} of {$this->totalObjCount})");

    // Create digital object
    $do = new QubitDigitalObject;
    $do->informationObject = $informationObject;
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
