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
      new sfCommandOption('use-gitconfig', null, sfCommandOption::PARAMETER_NONE, 'Get username and email from $HOME/.gitconfig'),
      new sfCommandOption('title', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site title'),
      new sfCommandOption('description', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site description'),
      new sfCommandOption('username', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin username'),
      new sfCommandOption('email', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin email address'),
      new sfCommandOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin password')
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
      if ($options['use-gitconfig'])
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
      }

      $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
      $sf_context = sfContext::createInstance($configuration);
      sfInstall::loadData();

      // set, or prompt for, site title configuration information
      $siteTitle = ($options['title']) ? $options['title'] : '';
      if (!$siteTitle)
      {
        $siteTitle       = readline("Site title [Qubit]: ");
        $siteTitle       = ($siteTitle) ? $siteTitle : 'Qubit';
      }

      // set, or prompt for, site description information
      $siteDescription = ($options['description']) ? $options['description'] : '';
      if (!$siteDescription)
      {
        $siteDescription = readline("Site description [Test site]: ");
        $siteDescription = ($sitedescription) ? $siteDescription : 'Test site';
      }

      $this->createSetting('siteTitle', $siteTitle);
      $this->createSetting('siteDescription', $siteDescription);

      print "\n";

      // ask for admin user information
      $username = ($options['username']) ? $options['username'] : '';
      if (!$username)
      {
        $usernamePrompt = 'Admin username';
        $usernamePrompt .= ($defaultUser) ? ' ['. $defaultUser .']' : '';
        $usernamePrompt .= ': ';
        $username = readline($usernamePrompt);
        $username = ($username) ? $username : $defaultUser;
      }

      $email = ($options['email']) ? $options['email'] : '';
      if (!$email)
      {
        $emailPrompt = 'Admin email';
        $emailPrompt .= ($defaultEmail) ? ' ['. $defaultEmail .']' : '';
        $emailPrompt .= ': ';
        $email    = readline($emailPrompt);
        $email = ($email) ? $email : $defaultEmail;
      }

      $password = ($options['password']) ? $options['password'] : '';
      if (!$password)
      {
        $password = trim(readline("Admin password: "));
      }

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

  /*
   * Helper to create a system setting
   *
   * @param string $name  Name of setting
   * @param string $value  Value of setting
   */
  protected function createSetting($name, $value)
  {
    $setting = new QubitSetting();
    $setting->name = $name;
    $setting->value = $value;
    $setting->save();
  }
}
