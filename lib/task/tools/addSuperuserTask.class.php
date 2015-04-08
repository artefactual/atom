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
 * Add Qubit superuser
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class addSuperuserTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::OPTIONAL, 'The username to create.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('email', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired user email address'),
      new sfCommandOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired user password'),
      new sfCommandOption('demo', null, sfCommandOption::PARAMETER_NONE, 'Use default demo values')
    ));

    $this->namespace = 'tools';
    $this->name = 'add-superuser';
    $this->briefDescription = 'Add new superuser.';

    $this->detailedDescription = <<<EOF
Add new superuser.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    if ($options['demo'])
    {
      $this->setDemoOptions($arguments, $options);
    }

    $needsData = !$arguments['username'] || !$options['email'] || !$options['password'];
    if ($needsData && !function_exists('readline'))
    {
      throw new Exception('One of the following properties have not been '.
        'assigned: username, email and password. Please, use the '.
        'corresponding command line options.');
    }

    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    self::addSuperUser($arguments['username'], $options);
  }

  /**
   * Set the user to have default demo values,
   * i.e. admin user is demo@example.com / demo.
   */
  private function setDemoOptions(&$arguments, &$options)
  {
    $arguments['username'] = 'demo';
    $options['email'] = 'demo@example.com';
    $options['password'] = 'demo';
  }

  public static function addSuperUser($username, $options)
  {
    // Ask for admin user information
    if (!$username)
    {
      $defaultUser = 'admin';
      $usernamePrompt = 'Admin username';
      $usernamePrompt .= ($defaultUser) ? ' ['. $defaultUser .']' : '';
      $usernamePrompt .= ': ';
      $username = readline($usernamePrompt);
      $username = ($username) ? $username : $defaultUser;
    }

    $email = ($options['email']) ? $options['email'] : '';
    if (!$email)
    {
      $defaultEmail = 'admin@example.com';
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

    // Create user
    $user = new QubitUser();
    $user->username = $username;
    $user->email = $email;
    $user->setPassword($password);
    $user->active = true;
    $user->save();

    // Give user admin capability
    $group = new QubitAclUserGroup();
    $group->userId = $user->id;
    $group->groupId = QubitAclGroup::ADMIN_ID;
    $group->save();
  }
}
