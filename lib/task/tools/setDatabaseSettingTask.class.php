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
 * Update AtoM'ss settings stored in the database
 *
 * @package    symfony
 * @subpackage task
 */
class setDatabaseSettingTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Setting name'),
      new sfCommandArgument('value', sfCommandArgument::REQUIRED, 'Setting value')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('action', null, sfCommandOption::PARAMETER_REQUIRED, 'Desired action')
    ));

    $this->namespace = 'tools';
    $this->name = 'set-db-setting';
    $this->briefDescription = 'Update AtoM\'s settings stored in the database';

    $this->detailedDescription = <<<EOF
FIXME
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    // Retrieve QubitSetting object
    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, $arguments['name']);
    if (null === $setting = QubitSetting::getOne($criteria))
    {
      throw new sfException('No setting with the given name was found in the database.');
    }

    $setting->setValue($arguments['value'], array('sourceCulture' => true));
    $setting->save();
  }
}
