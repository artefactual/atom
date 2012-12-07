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
 * Purge Qubit data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class propelPurgeTask extends sfBaseTask
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
    $stopExecution = $insertSql->run();

    if (!$stopExecution)
    {
      // attempt to provide default user admin name and email
      if ($_SERVER['HOME'])
      {
        $gitConfigFile = $_SERVER['HOME'] .'/.gitconfig';
        if (file_exists($gitConfigFile))
        {
          $gitConfig = parse_ini_file($gitConfigFile);

          $defaultUser = strtolower(strtok($gitConfig['name'], ' '));
          $defaultEmail = $gitConfig['email'];
        }
      }

      $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
      $sf_context = sfContext::createInstance($configuration);
      sfInstall::loadData();

      // ask for basic site configuration information
      $siteTitle       = readline("Site title [Qubit]: ");
      $siteTitle       = ($siteTitle) ? $siteTitle : 'Qubit';
      $siteDescription = readline("Site description [Test site]: ");
      $siteDescription = ($sitedescription) ? $siteDescription : 'Test site';

      // set site title
      $setting = new QubitSetting();
      $setting->name = 'siteTitle';
      $setting->value = $siteTitle;
      $setting->save();

      // set site description
      $setting = new QubitSetting();
      $setting->name = 'siteDescription';
      $setting->value = $siteDescription;
      $setting->save();

      print "\n";

      // ask for admin user information
      $usernamePrompt = 'Admin username';
      $usernamePrompt .= ($defaultUser) ? ' ['. $defaultUser .']' : '';
      $usernamePrompt .= ': ';
      $username = readline($usernamePrompt);
      $username = ($username) ? $username : $defaultUser;

      $emailPrompt = 'Admin email';
      $emailPrompt .= ($defaultEmail) ? ' ['. $defaultEmail .']' : '';
      $emailPrompt .= ': ';
      $email    = readline($emailPrompt);
      $email = ($email) ? $email : $defaultEmail;

      $password = trim(readline("Admin password: "));

      // create user
      $user = new QubitUser();
      $user->username = $username;
      $user->email = $email;
      $user->setPassword($password);
      $user->active = true;
      $user->save();

      // give user admin capability
      $group = new QubitAclUserGroup();
      $group->userId = $user->id;
      $group->groupId = 100;
      $group->save();

      $this->logSection('propel', 'Purge complete!');
    }
  }
}
