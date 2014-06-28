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
 * arElasticSearchPlugin main class
 *
 * @package     AccesstoMemory
 * @subpackage  search
 */
class arDrmcDeleteArtworkRecordTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'If passed, progress is displayed for each object indexed')));

    $this->addArguments(array(
      new sfCommandArgument('id', null, sfCommandArgument::REQUIRED, 'Internal ID of the artwork record')
    ));

    $this->namespace = 'drmc';
    $this->name = 'delete-artwork-record';

    $this->briefDescription = 'Delete artwork record and its descendants';
    $this->detailedDescription = <<<EOF
The [drmc:delete-artwork-record|INFO] task delets and arwork record and its descendants
given the internal record ID.
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
      $this->run($arguments, $options);

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
    if (null === $artwork = QubitInformationObject::getById($arguments['id']))
    {
      throw new sfException('Resource not found');
    }

    if (QubitInformationObject::ROOT_ID === $artwork->id)
    {
      throw new sfException('Sorry, you can\'t do that');
    }

    $this->logSection('INFO', 'Starting process...');

    // Delete AIPs for this specific artwork
    $criteria = new Criteria;
    $criteria->add(QubitAip::PART_OF, $artwork->id);
    foreach (QubitAip::get($criteria) as $aip)
    {
      $this->logSection('END', 'Removing AIP '.$aip->id);

      $aip->delete();
    }

    foreach ($artwork->descendants->andSelf()->orderBy('rgt') as $item)
    {
      // Delete related digitalObjects
      foreach ($item->digitalObjects as $digitalObject)
      {
        $digitalObject->informationObjectId = null;
        $this->logSection('INFO', 'Removing digital object '.$digitalObject->id);

        $digitalObject->delete();
      }

      $term = QubitTerm::getById($item->levelOfDescriptionId);

      if ($item->id === $artwork->id)
      {
        $this->logSection('END', 'Removing artwork record '.$item->id.' ('.$term->name.')');
      }
      else
      {
        $this->logSection('INFO', 'Removing descendant '.$item->id.' ('.$term->name.')');
      }

      $item->delete();
    }
  }
}
