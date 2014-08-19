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

class arUpdateCollectionTmsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('log-path', null, sfCommandOption::PARAMETER_OPTIONAL, 'Path to log file'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_OPTIONAL, 'If set to \'true\' forces the update of all Artworks', false),
    ));

    $this->namespace = 'tms';
    $this->name  = 'update';
    $this->briefDescription = 'Updates TMS data for all Artworks in the collection.';
    $this->detailedDescription = <<<EOF
Updates the TMS data for all the Artworks in the collection
wich LastModifiedCheckDate has changed. If the force
option is set it will update all the Artworks without checking
if LastModifiedCheckDate has changed.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);

    // Overall timer
    $timer = new QubitTimer;

    // Initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    // Determine log type and create/open file if needed
    if (empty($options['log-path']))
    {
      $this->logTo = 'console';
    }
    else if (false === $this->logFile = @fopen($options['log-path'], 'a'))
    {
      $this->logTo = 'console';
      $this->writeLog(sprintf('Couldn\'t create/open log file in: %s', $options['log-path']));
    }
    else
    {
      $this->logTo = 'file';
    }

    $this->writeLog(sprintf('Collection TMS data update started at %s', date('Y-m-d H:i:s')));

    // Get artworks
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, sfConfig::get('app_drmc_lod_artwork_record_id'));
    $artworks = QubitInformationObject::get($criteria);

    $total = count($artworks);
    $count = 0;

    foreach ($artworks as $artwork)
    {
      $fetchTms = new arFetchTms;

      // Determine if the artwork needs to be updated
      $needsUpdate = true;
      if (!$options['force'])
      {
        // Get and check last modified date from TMS and database
        $tmsDate = $fetchTms->getLastModifiedCheckDate($artwork->identifier);
        $atomDate = $artwork->getPropertyByName('LastModifiedCheckDate')->value;

        if (isset($tmsDate) && isset($atomDate) && $tmsDate === $atomDate)
        {
          $needsUpdate = false;
        }
      }

      if ($needsUpdate)
      {
        // Update artwork
        $fetchTms->updateArtwork($artwork);

        $this->writeLog(sprintf(' - Artwork: \'%s\' has been updated (%ss) (%s/%s)', $artwork->title, $timer->elapsed(), ++$count, $total));
      }
      else
      {
        $this->writeLog(sprintf(' - Artwork: \'%s\' is already updated (%ss) (%s/%s)', $artwork->title, $timer->elapsed(), ++$count, $total));
      }
    }

    $this->writeLog(sprintf('Collection TMS data updated for %s artworks in %s seconds.', $total, $timer->elapsed()));

    // Close log file if openned
    if (false !== $this->logFile)
    {
      fclose($this->logFile);
    }
  }

  protected function writeLog($message)
  {
    switch ($this->logTo)
    {
      case 'console':
        $this->logSection('tms-update:', $message);

        break;

      case 'file':
        fwrite($this->logFile, $message."\n");

        break;
    }
  }
}
