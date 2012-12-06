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
 * Regenerate nested set column values
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class propelBuildNestedSetTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'propel';
    $this->name = 'purge';
    $this->briefDescription = 'Purge all data.';

    $this->detailedDescription = <<<EOF
Purge all data.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $insertSql = new sfPropelInsertSqlTask($this->dispatcher, $this->formatter);
    $insertSql->setCommandApplication($this->commandApplication);
    $insertSql->setConfiguration($this->configuration);
    $insertSql->run();

    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
    $sf_context = sfContext::createInstance($configuration);
    sfInstall::loadData();

    $username = readline("Admin username: ");
    $email    = readline("Admin email: ");
    $password = trim(readline("Admin password: "));

    $user = new QubitUser();
    $user->username = $username;
    $user->email = $email;
    $user->setPassword($password);
    $user->active = true;
    $user->save();

print 'U:'. $user->id ."\n";
$group = new QubitAclUserGroup();
$group->userId = $user->id;
$group->groupId = 100;
$group->save();


    $this->logSection('propel', 'Done!');
  }
}
