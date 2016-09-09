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
 * Parse and add to the database the metadata from the METS
 * files copied to the uploads folder in DIP uploads
 *
 * @package     AccesstoMemory
 * @subpackage  tools
 */
class arMetsToDatabaseTask extends arBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli')));

    $this->namespace = 'tools';
    $this->name = 'mets2db';

    $this->briefDescription = 'Parse and add METS metadata to the database';
    $this->detailedDescription = <<<EOF
The [tools:mets2db|INFO] task parses and adds to the database the metadata from the METS files copied to the uploads folder in DIP uploads.
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    // Bootstrap
    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', false);
    $sf_context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Start transaction
    $conn->beginTransaction();

    try
    {
      $this->run();

      $conn->commit();
    }
    catch (Exception $e)
    {
      $conn->rollback();

      throw $e;
    }
  }

  public function run($arguments = array(), $options = array())
  {
    $timer = new QubitTimer;

    // Disable search index
    QubitSearch::getInstance()->disable();

    $this->log('Adding METS metadata to database:');

    // Go over AIPs to open each METS file only once
    $sql = 'SELECT filename, uuid FROM ' . QubitAIP::TABLE_NAME;
    $aips = QubitPdo::fetchAll($sql);
    $aipsTotal = count($aips);
    $aipsCount = 0;
    $totalDosCount = 0;

    foreach ($aips as $aip)
    {
      $this->log(vsprintf('[AIP] %s (UUID: %s) (%s s) (%s/%s)', array($aip->filename, $aip->uuid, $timer->elapsed(), ++$aipsCount, $aipsTotal)));

      // Get METS filepath
      $metsFilepath = sfConfig::get('sf_web_dir').
        DIRECTORY_SEPARATOR.'uploads'.
        DIRECTORY_SEPARATOR.'aips'.
        DIRECTORY_SEPARATOR.$aip->uuid.
        DIRECTORY_SEPARATOR.'METS.xml';

      if (!file_exists($metsFilepath))
      {
        $this->log('    Could not find METS file');

        continue;
      }

      // Load document
      $document = new SimpleXMLElement(@file_get_contents($metsFilepath));
      if (!isset($document))
      {
        $this->log('    Could not open METS file');

        continue;
      }

      // Initialice METS parser
      $metsParser = new QubitMetsParser($document);

      // Go over related digital objects parsing the METS file
      $sql  = 'SELECT
                  prop.object_id';
      $sql .= ' FROM '.QubitProperty::TABLE_NAME.' prop';
      $sql .= ' JOIN '.QubitPropertyI18n::TABLE_NAME.' prop_i18n
                  ON prop.id = prop_i18n.id';
      $sql .= ' WHERE prop_i18n.value = ?
                  AND prop.name = ?';

      $dos = QubitPdo::fetchAll($sql, array($aip->uuid, 'aipUUID'));
      $dosTotal = count($dos);
      $dosCount = 0;

      foreach ($dos as $item)
      {
        ++$totalDosCount;
        ++$dosCount;

        $do = QubitInformationobject::getById($item->object_id);
        if (!isset($do))
        {
          $this->log(vsprintf('    [DO] Could not find DO with id: %s (%s s) (%s/%s)', array($item->object_id, $timer->elapsed(), $dosCount, $dosTotal)));

          continue;
        }

        // Only DO with object UUID
        $objectUuid = (string)$do->getPropertyByName('objectUuid');
        if (empty($objectUuid))
        {
          $this->log(vsprintf('    [DO] Could not find UUID for DO with id: %s (%s s) (%s/%s)', array($item->object_id, $timer->elapsed(), $dosCount, $dosTotal)));

          continue;
        }

        // Parse METS file to get DO metadata
        $error = $metsParser->addMetsDataToInformationObject($do, $objectUuid);
        if (isset($error))
        {
          $this->log(vsprintf('    [DO] %s (%s s) (%s/%s)', array($error, $timer->elapsed(), $dosCount, $dosTotal)));

          continue;
        }

        $do->save();

        $this->log(vsprintf('    [DO] %s (UUID: %s) (%s s) (%s/%s)', array($do->title, $objectUuid, $timer->elapsed(), $dosCount, $dosTotal)));
      }
    }

    // Enable search index
    QubitSearch::getInstance()->enable();

    $this->log(vsprintf('Metadata from METS files added to the database for %s AIPs and %s DOs in %s seconds.', array($aipsTotal, $totalDosCount, $timer->elapsed())));
    $this->log('Note: you will need to rebuild your search index to add the METS metadata from the database.');
  }
}
